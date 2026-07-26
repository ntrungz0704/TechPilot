#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

# Fail closed — no SKIP for missing tools
if ! command -v jq >/dev/null 2>&1; then echo "FAIL: test_28 requires jq"; exit 1; fi
if ! command -v node >/dev/null 2>&1; then echo "FAIL: test_28 requires node"; exit 1; fi

NODE_PATH="$REPO_ROOT/node_modules"
export NODE_PATH

if ! node -e "require('ajv'); require('ajv-formats'); require('js-yaml')" >/dev/null 2>&1; then
  echo "FAIL: test_28 node dependencies not resolved"
  exit 1
fi

CLEANUP_DIRS=()
OUTPUT_FILES=()
cleanup_all() {
  for d in "${CLEANUP_DIRS[@]}"; do [ -d "$d" ] && rm -rf "$d" 2>/dev/null || true; done
  for f in "${OUTPUT_FILES[@]}"; do [ -f "$f" ] && rm -f "$f" 2>/dev/null || true; done
}
trap cleanup_all EXIT

# ── Temp repos ────────────────────────────────────────────────────
TEMP_REPO=$(mktemp -d)
MARKER_DIR=$(mktemp -d)
REMOTE_DIR=$(mktemp -d)
CLEANUP_DIRS+=("$TEMP_REPO" "$MARKER_DIR" "$REMOTE_DIR")
cd "$TEMP_REPO"
git init -q
git config user.email "test@test.com"
git config user.name "Test"

mkdir -p checkpoints docs/workflow/schemas scripts/workflow/lib

cat > checkpoints/STATE.json << 'EOF'
{"schema_version":"1","checkpoint_id":"CHECKPOINT_3","title":"Test","lifecycle_status":"ROADMAP_DEFINED","branch":"feature/hieu-news","candidate_sha":null,"reviewed_sha":null,"last_updated":"2026-01-01T00:00:00Z","state_history_ref":"checkpoints/STATE_HISTORY.jsonl"}
EOF
touch checkpoints/STATE_HISTORY.jsonl

git add -A && git commit -q -m "baseline" > /dev/null 2>&1

for f in transition-state verify-remediation-marker commit-remediation check-changed-files; do
  cp "$REPO_ROOT/scripts/workflow/$f" scripts/workflow/
done
cp "$REPO_ROOT/scripts/workflow/lib/"*.js scripts/workflow/lib/
cp -R "$REPO_ROOT/docs/workflow/schemas/"* docs/workflow/schemas/
chmod +x scripts/workflow/transition-state scripts/workflow/verify-remediation-marker scripts/workflow/commit-remediation scripts/workflow/check-changed-files

test -x scripts/workflow/transition-state || { echo "FAIL: transition-state not executable"; exit 1; }
test -x scripts/workflow/verify-remediation-marker || { echo "FAIL: verify-remediation-marker not executable"; exit 1; }
test -x scripts/workflow/commit-remediation || { echo "FAIL: commit-remediation not executable"; exit 1; }
test -x scripts/workflow/check-changed-files || { echo "FAIL: check-changed-files not executable"; exit 1; }

git add -A && git commit -q -m "fixture with workflow scripts" > /dev/null 2>&1
BASE_SHA=$(git rev-parse HEAD)

git checkout -q -b fix/workflow-qa-v3-post-merge

git init -q --bare "$REMOTE_DIR"
git remote add origin "$REMOTE_DIR"
git push -q origin fix/workflow-qa-v3-post-merge > /dev/null 2>&1
git branch feature/hieu-news HEAD
git push -q origin feature/hieu-news > /dev/null 2>&1

MERGE_BASE=$(git merge-base HEAD origin/feature/hieu-news 2>/dev/null || echo "$BASE_SHA")

# ── Helpers ────────────────────────────────────────────────────────
SCENARIOS=0; PASS=0; FAIL_COUNT=0

restore_fixture() {
  git reset --hard "$BASE_SHA" > /dev/null 2>&1
  git clean -fdx > /dev/null 2>&1
  if ! git diff --quiet 2>/dev/null || ! git diff --cached --quiet 2>/dev/null; then
    echo "FAIL: temporary fixture could not be restored"
    exit 1
  fi
  if [ -n "$(git ls-files --others --exclude-standard 2>/dev/null)" ]; then
    echo "FAIL: temporary fixture still has untracked files"
    exit 1
  fi
}

assert_exit() {
  local desc="$1" expected="$2"
  shift 2
  SCENARIOS=$((SCENARIOS+1))
  local outfile
  outfile=$(mktemp)
  OUTPUT_FILES+=("$outfile")
  set +e
  "$@" > "$outfile" 2>&1
  local EC=$?
  set -e
  if [ "$EC" -eq 126 ] || [ "$EC" -eq 127 ]; then
    FAIL_COUNT=$((FAIL_COUNT+1))
    echo "  FAIL: $desc (ec=$EC command not found or permission denied)"
    head -5 "$outfile"
  elif [ "$EC" -eq "$expected" ]; then
    PASS=$((PASS+1))
    echo "  PASS: $desc (ec=$EC)"
  else
    FAIL_COUNT=$((FAIL_COUNT+1))
    echo "  FAIL: $desc (ec=$EC, expected=$expected)"
    head -5 "$outfile"
  fi
}

assert_exit_contains() {
  local desc="$1" expected_exit="$2" expected_text="$3"
  shift 3
  local outfile
  outfile=$(mktemp)
  OUTPUT_FILES+=("$outfile")
  set +e
  "$@" > "$outfile" 2>&1
  local EC=$?
  set -e
  local OUT=$(cat "$outfile")
  SCENARIOS=$((SCENARIOS+1))
  local allpass=true
  if [ "$EC" -eq 126 ] || [ "$EC" -eq 127 ]; then allpass=false; echo "  FAIL: $desc (ec=$EC command not found)"; fi
  if [ "$EC" -ne "$expected_exit" ]; then allpass=false; echo "  FAIL: $desc (ec=$EC, expected=$expected_exit)"; fi
  if ! echo "$OUT" | grep -qF "No such file or directory" 2>/dev/null; then :; else allpass=false; echo "  FAIL: $desc contains 'No such file or directory'"; fi
  if ! echo "$OUT" | grep -qF "command not found" 2>/dev/null; then :; else allpass=false; echo "  FAIL: $desc contains 'command not found'"; fi
  if ! echo "$OUT" | grep -qF "unbound variable" 2>/dev/null; then :; else allpass=false; echo "  FAIL: $desc contains 'unbound variable'"; fi
  if ! echo "$OUT" | grep -qF "$expected_text" 2>/dev/null; then allpass=false; echo "  FAIL: $desc missing '$expected_text'"; fi
  if $allpass; then PASS=$((PASS+1)); echo "  PASS: $desc (ec=$EC)"; else FAIL_COUNT=$((FAIL_COUNT+1)); head -5 "$outfile"; fi
}

create_marker() {
  local output="$1" base_sha="$2" branch="$3" allow_commit="$4" allow_push="$5" approved_paths="$6" purpose="$7"
  local ts
  ts=$(date -u +%Y-%m-%dT%H:%M:%SZ 2>/dev/null || date -u +%Y-%m-%dT%H:%M:%S"Z")
  jq -n \
    --arg purpose "$purpose" \
    --arg approved_by "human:test" \
    --arg approval_ref "https://example.com/test" \
    --arg base_sha "$base_sha" \
    --arg branch "$branch" \
    --argjson allow_commit "$allow_commit" \
    --argjson allow_push "$allow_push" \
    --argjson allow_force_push false \
    --argjson allow_merge false \
    --argjson allow_mark_ready false \
    --argjson approved_paths "$approved_paths" \
    --arg created_at "$ts" \
    '{purpose:$purpose, approved_by:$approved_by, approval_ref:$approval_ref, exact_base_sha:$base_sha, exact_target_branch:$branch, allow_commit:$allow_commit, allow_push:$allow_push, allow_force_push:$allow_force_push, allow_merge:$allow_merge, allow_mark_ready:$allow_mark_ready, approved_paths:$approved_paths, created_at:$created_at}' > "$output"
}

reset_state() {
  local status="$1"
  jq --arg s "$status" '.lifecycle_status = $s | .last_updated = "2026-01-01T00:00:00Z"' checkpoints/STATE.json > checkpoints/STATE.json.tmp \
    && mv checkpoints/STATE.json.tmp checkpoints/STATE.json
}

# ═══════════════════════════════════════════════════════════════════
# Scenarios 1-5: Approval transition tests
# ═══════════════════════════════════════════════════════════════════
echo "=== Scenarios 1-5: Approval transition tests ==="

reset_state "CONTRACT_DRAFTED"
assert_exit "1. missing approved_by" 1 scripts/workflow/transition-state CONTRACT_DRAFTED CONTRACT_APPROVED workflow-test "test missing approved_by" "" ""

reset_state "CONTRACT_DRAFTED"
assert_exit "2. missing approval_ref" 1 scripts/workflow/transition-state CONTRACT_DRAFTED CONTRACT_APPROVED workflow-test "test missing approval_ref" "human:test" ""

reset_state "CONTRACT_DRAFTED"
assert_exit "3. malformed approved_by" 1 scripts/workflow/transition-state CONTRACT_DRAFTED CONTRACT_APPROVED workflow-test "test bad approved_by" "bot" "https://example.com"

reset_state "CONTRACT_DRAFTED"
assert_exit "4. malformed approval_ref" 1 scripts/workflow/transition-state CONTRACT_DRAFTED CONTRACT_APPROVED workflow-test "test bad ref" "human:test" "not-a-url"

reset_state "CONTRACT_DRAFTED"
assert_exit "5. valid approval" 0 scripts/workflow/transition-state CONTRACT_DRAFTED CONTRACT_APPROVED workflow-test "valid approval" "human:test" "https://github.com/ntrungz0704/TechPilot/issues/42"

HISTORY_LINE=$(tail -1 checkpoints/STATE_HISTORY.jsonl 2>/dev/null || echo "")
if [ -n "$HISTORY_LINE" ]; then
  echo "$HISTORY_LINE" | jq -e '.approved_by == "human:test"' > /dev/null 2>&1 \
    || { echo "  FAIL: 5 history missing approved_by"; FAIL_COUNT=$((FAIL_COUNT+1)); }
  echo "$HISTORY_LINE" | jq -e '.approval_ref' > /dev/null 2>&1 \
    || { echo "  FAIL: 5 history missing approval_ref"; FAIL_COUNT=$((FAIL_COUNT+1)); }
fi

echo "--- Approval matrix: $PASS/$SCENARIOS pass, $FAIL_COUNT fail ---"

# Restore to clean fixture before marker scenarios
restore_fixture

# ═══════════════════════════════════════════════════════════════════
# Scenarios 6-8: Marker rejection tests
# ═══════════════════════════════════════════════════════════════════
echo "=== Scenarios 6-8: Marker tests ==="

restore_fixture
create_marker "$MARKER_DIR/m6.json" "$MERGE_BASE" "fix/workflow-qa-v3-post-merge" true false '["app/**"]' "production path test"
assert_exit_contains "6. MARKER_PRODUCTION_PATH" 1 "not subset of canonical remediation allowlist" \
  scripts/workflow/verify-remediation-marker --marker "$MARKER_DIR/m6.json" commit

restore_fixture
create_marker "$MARKER_DIR/m7.json" "0000000000000000000000000000000000000000" "fix/workflow-qa-v3-post-merge" true false '["scripts/workflow/**"]' "old base test"
assert_exit_contains "7. MARKER_OLD_BASE_SHA" 1 "not a valid commit" \
  scripts/workflow/verify-remediation-marker --marker "$MARKER_DIR/m7.json" commit

restore_fixture
create_marker "$MARKER_DIR/m8.json" "$MERGE_BASE" "fix/workflow-qa-v3-post-merge" true false '["scripts/workflow/**"]' "prestage test"
echo "# dummy" >> scripts/workflow/check-changed-files
git add scripts/workflow/check-changed-files
assert_exit_contains "8. MARKER_PRESTAGED_FILE" 1 "Pre-staged files are not allowed" \
  scripts/workflow/commit-remediation --marker "$MARKER_DIR/m8.json"
git restore --staged scripts/workflow/check-changed-files 2>/dev/null || true
git checkout -- scripts/workflow/check-changed-files 2>/dev/null || true

# ═══════════════════════════════════════════════════════════════════
# Scenario 9: COMMIT_WRAPPER_STAGES_EXACT_SET
# ═══════════════════════════════════════════════════════════════════
echo "=== Scenario 9: COMMIT_WRAPPER_STAGES_EXACT_SET ==="
restore_fixture

if ! git status --short 2>/dev/null | grep -q .; then :; else echo "FAIL: 9 pre-check not clean"; exit 1; fi

echo "approved content" > scripts/workflow/approved_file.txt
HEAD_BEFORE=$(git rev-parse HEAD)
create_marker "$MARKER_DIR/m9.json" "$MERGE_BASE" "fix/workflow-qa-v3-post-merge" true false '["scripts/workflow/approved_file.txt"]' "stage exact set test"

out9=$(mktemp)
OUTPUT_FILES+=("$out9")
SCENARIOS=$((SCENARIOS+1))
set +e
scripts/workflow/commit-remediation --marker "$MARKER_DIR/m9.json" > "$out9" 2>&1
EC=$?
set -e
OUT9=$(cat "$out9")

allpass=true
if [ "$EC" -ne 0 ]; then allpass=false; echo "  FAIL: 9 exit=$EC expected 0"; fi
echo "$OUT9" | grep -qF "PASS: commit-remediation" || { allpass=false; echo "  FAIL: 9 no PASS: commit-remediation"; }
echo "$OUT9" | grep -qF "Marker verification failed" && { allpass=false; echo "  FAIL: 9 marker verification failed"; }
echo "$OUT9" | grep -qF "File outside approved paths" && { allpass=false; echo "  FAIL: 9 file outside approved paths"; }
echo "$OUT9" | grep -qF "No such file or directory" && { allpass=false; echo "  FAIL: 9 file not found"; }
echo "$OUT9" | grep -qF "command not found" && { allpass=false; echo "  FAIL: 9 command not found"; }
echo "$OUT9" | grep -qF "unbound variable" && { allpass=false; echo "  FAIL: 9 unbound variable"; }

HEAD_AFTER=$(git rev-parse HEAD)
if [ "$HEAD_AFTER" = "$HEAD_BEFORE" ]; then allpass=false; echo "  FAIL: 9 HEAD unchanged (no commit)"; fi

COMMITTED=$(git diff --name-only HEAD~1..HEAD 2>/dev/null || echo "")
if [ "$COMMITTED" != "scripts/workflow/approved_file.txt" ]; then allpass=false; echo "  FAIL: 9 committed files: '$COMMITTED' (expected exactly scripts/workflow/approved_file.txt)"; fi

if ! git diff --cached --quiet 2>/dev/null; then allpass=false; echo "  FAIL: 9 index not clean after commit"; fi

if $allpass; then PASS=$((PASS+1)); echo "  PASS: 9 COMMIT_WRAPPER_STAGES_EXACT_SET (ec=0, exact 1 file committed)"; else FAIL_COUNT=$((FAIL_COUNT+1)); head -10 "$out9"; fi

restore_fixture

# ═══════════════════════════════════════════════════════════════════
# Scenario 10: COMMIT_FAILURE_ROLLBACK
# ═══════════════════════════════════════════════════════════════════
echo "=== Scenario 10: COMMIT_FAILURE_ROLLBACK ==="
restore_fixture

if ! git status --short 2>/dev/null | grep -q .; then :; else echo "FAIL: 10 pre-check not clean"; exit 1; fi
if [ -f scripts/workflow/approved_file.txt ]; then echo "FAIL: 10 stray approved_file.txt"; exit 1; fi
if ! git diff --cached --quiet 2>/dev/null; then echo "FAIL: 10 pre-check unclean index"; exit 1; fi

HEAD_BEFORE=$(git rev-parse HEAD)
INDEX_BEFORE=$(git diff --cached 2>/dev/null || true)

echo "rollback content" > scripts/workflow/rollback_file.txt

create_marker "$MARKER_DIR/m10.json" "$MERGE_BASE" "fix/workflow-qa-v3-post-merge" true false '["scripts/workflow/rollback_file.txt"]' "commit failure rollback test"

cat > .git/hooks/pre-commit << 'HOOK'
#!/bin/bash
exit 1
HOOK
chmod +x .git/hooks/pre-commit

out10=$(mktemp)
OUTPUT_FILES+=("$out10")
SCENARIOS=$((SCENARIOS+1))
set +e
scripts/workflow/commit-remediation --marker "$MARKER_DIR/m10.json" > "$out10" 2>&1
EC=$?
set -e
OUT10=$(cat "$out10")

allpass=true

if [ "$EC" -ne 1 ]; then allpass=false; echo "  FAIL: 10 exit=$EC expected 1"; fi
if [ "$EC" -eq 126 ] || [ "$EC" -eq 127 ]; then allpass=false; echo "  FAIL: 10 command not found (ec=$EC)"; fi

echo "$OUT10" | grep -qF "=== Step 5: Committing ===" || { allpass=false; echo "  FAIL: 10 never reached git commit (no Step 5)"; }
echo "$OUT10" | grep -qF "FAIL: Commit failed" || { allpass=false; echo "  FAIL: 10 no 'Commit failed'"; }
echo "$OUT10" | grep -qF "Marker verification failed" && { allpass=false; echo "  FAIL: 10 marker verification failed before commit"; }
echo "$OUT10" | grep -qF "File outside approved paths" && { allpass=false; echo "  FAIL: 10 file outside approved paths"; }
echo "$OUT10" | grep -qF "No such file or directory" && { allpass=false; echo "  FAIL: 10 file not found"; }
echo "$OUT10" | grep -qF "command not found" && { allpass=false; echo "  FAIL: 10 command not found"; }
echo "$OUT10" | grep -qF "unbound variable" && { allpass=false; echo "  FAIL: 10 unbound variable"; }

INDEX_AFTER=$(git diff --cached 2>/dev/null || true)
if [ "$INDEX_BEFORE" != "$INDEX_AFTER" ]; then allpass=false; echo "  FAIL: 10 index changed"; fi

if [ ! -f scripts/workflow/rollback_file.txt ] || ! grep -q "rollback content" scripts/workflow/rollback_file.txt; then
  allpass=false; echo "  FAIL: 10 rollback_file.txt missing or altered"
fi

HEAD_AFTER=$(git rev-parse HEAD)
if [ "$HEAD_AFTER" != "$HEAD_BEFORE" ]; then allpass=false; echo "  FAIL: 10 HEAD changed (new commit)"; fi

if [ -f scripts/workflow/approved_file.txt ]; then allpass=false; echo "  FAIL: 10 stray approved_file.txt"; fi

case "$MARKER_DIR" in
  "$TEMP_REPO"/*|"$TEMP_REPO") allpass=false; echo "  FAIL: 10 marker inside repo" ;;
esac

if $allpass; then
  PASS=$((PASS+1))
  echo "  PASS: 10 COMMIT_FAILURE_ROLLBACK (ec=1, reached git commit, index clean, no commit, worktree intact)"
else
  FAIL_COUNT=$((FAIL_COUNT+1))
  head -15 "$out10"
fi

rm -f .git/hooks/pre-commit scripts/workflow/rollback_file.txt 2>/dev/null || true
restore_fixture

# ═══════════════════════════════════════════════════════════════════
# Scenario 11: WAL_MISSING_MANIFEST
# ═══════════════════════════════════════════════════════════════════
echo "=== Scenario 11: WAL_MISSING_MANIFEST ==="
restore_fixture
reset_state "CONTRACT_DRAFTED"

WAL_TXID="stale-missing-man-txid"
mkdir -p "checkpoints/.wal-${WAL_TXID}"
echo "{}" > "checkpoints/.wal-${WAL_TXID}/state.json.orig"
echo "{}" > "checkpoints/.wal-${WAL_TXID}/history.jsonl.orig"
mkdir -p checkpoints/.state-transition.lock
echo "99999" > checkpoints/.state-transition.lock/pid
echo "$(hostname)" > checkpoints/.state-transition.lock/hostname
echo "2000-01-01T00:00:00Z" > checkpoints/.state-transition.lock/timestamp
echo "$WAL_TXID" > checkpoints/.state-transition.lock/txid

out11=$(mktemp)
OUTPUT_FILES+=("$out11")
SCENARIOS=$((SCENARIOS+1))
set +e
WORKFLOW_TEST_MODE=1 scripts/workflow/transition-state CONTRACT_DRAFTED CONTRACT_APPROVED workflow-test "wal missing manifest" > "$out11" 2>&1
EC=$?
set -e
OUT11=$(cat "$out11")

allpass=true
if [ "$EC" -ne 1 ]; then allpass=false; echo "  FAIL: 11 exit=$EC expected 1"; fi
echo "$OUT11" | grep -qF "missing manifest.json" || { allpass=false; echo "  FAIL: 11 no 'missing manifest.json'"; }
echo "$OUT11" | grep -qF "FAIL CRITICAL" || { allpass=false; echo "  FAIL: 11 no 'FAIL CRITICAL'"; }
echo "$OUT11" | grep -qF "command not found" && { allpass=false; echo "  FAIL: 11 command not found"; }
echo "$OUT11" | grep -qF "No such file or directory" && { allpass=false; echo "  FAIL: 11 file not found"; }
echo "$OUT11" | grep -qF "unbound variable" && { allpass=false; echo "  FAIL: 11 unbound variable"; }

[ -d checkpoints/.state-transition.acquire ] && { allpass=false; echo "  FAIL: 11 acquire lock not cleaned"; }
[ -d checkpoints/.state-transition.lock ] && { allpass=false; echo "  FAIL: 11 transition lock not cleaned"; }

if $allpass; then PASS=$((PASS+1)); echo "  PASS: 11 WAL_MISSING_MANIFEST (ec=1, exact error, locks cleaned)"
else FAIL_COUNT=$((FAIL_COUNT+1)); head -10 "$out11"; fi

rm -rf "checkpoints/.wal-${WAL_TXID}" checkpoints/.state-transition.lock.stale.* 2>/dev/null || true

# ═══════════════════════════════════════════════════════════════════
# Scenario 12: WAL_TXID_MISMATCH
# ═══════════════════════════════════════════════════════════════════
echo "=== Scenario 12: WAL_TXID_MISMATCH ==="
restore_fixture
reset_state "CONTRACT_DRAFTED"

WAL_TXID2="wrong-txid-mismatch"
mkdir -p "checkpoints/.wal-${WAL_TXID2}"
cat > "checkpoints/.wal-${WAL_TXID2}/manifest.json" << 'MANIFEST'
{"txid":"different-txid","phase":"PREPARED","state_sha256_orig":"0000","history_sha256_orig":"0000"}
MANIFEST
echo "{}" > "checkpoints/.wal-${WAL_TXID2}/state.json.orig"
echo "{}" > "checkpoints/.wal-${WAL_TXID2}/history.jsonl.orig"
mkdir -p checkpoints/.state-transition.lock
echo "99999" > checkpoints/.state-transition.lock/pid
echo "$(hostname)" > checkpoints/.state-transition.lock/hostname
echo "2000-01-01T00:00:00Z" > checkpoints/.state-transition.lock/timestamp
echo "$WAL_TXID2" > checkpoints/.state-transition.lock/txid

out12=$(mktemp)
OUTPUT_FILES+=("$out12")
SCENARIOS=$((SCENARIOS+1))
set +e
WORKFLOW_TEST_MODE=1 scripts/workflow/transition-state CONTRACT_DRAFTED CONTRACT_APPROVED workflow-test "wal txid mismatch" > "$out12" 2>&1
EC=$?
set -e
OUT12=$(cat "$out12")

allpass=true
if [ "$EC" -ne 1 ]; then allpass=false; echo "  FAIL: 12 exit=$EC expected 1"; fi
echo "$OUT12" | grep -qF "WAL txid" || { allpass=false; echo "  FAIL: 12 no 'WAL txid'"; }
echo "$OUT12" | grep -qF "expected txid" || { allpass=false; echo "  FAIL: 12 no 'expected txid'"; }
echo "$OUT12" | grep -qF "FAIL CRITICAL" || { allpass=false; echo "  FAIL: 12 no 'FAIL CRITICAL'"; }
echo "$OUT12" | grep -qF "command not found" && { allpass=false; echo "  FAIL: 12 command not found"; }
echo "$OUT12" | grep -qF "No such file or directory" && { allpass=false; echo "  FAIL: 12 file not found"; }
echo "$OUT12" | grep -qF "unbound variable" && { allpass=false; echo "  FAIL: 12 unbound variable"; }

[ -d checkpoints/.state-transition.acquire ] && { allpass=false; echo "  FAIL: 12 acquire lock not cleaned"; }
[ -d checkpoints/.state-transition.lock ] && { allpass=false; echo "  FAIL: 12 transition lock not cleaned"; }

if $allpass; then PASS=$((PASS+1)); echo "  PASS: 12 WAL_TXID_MISMATCH (ec=1, exact errors, locks cleaned)"
else FAIL_COUNT=$((FAIL_COUNT+1)); head -10 "$out12"; fi

rm -rf "checkpoints/.wal-${WAL_TXID2}" checkpoints/.state-transition.lock.stale.* 2>/dev/null || true

# ═══════════════════════════════════════════════════════════════════
# Scenario 13: ACQUISITION_MUTEX_STALE_TAKEOVER
# ═══════════════════════════════════════════════════════════════════
echo "=== Scenario 13: ACQUISITION_MUTEX_STALE_TAKEOVER ==="
restore_fixture
reset_state "ROADMAP_DEFINED"

mkdir -p checkpoints/.state-transition.acquire
echo "99999" > checkpoints/.state-transition.acquire/pid
echo "$(hostname)" > checkpoints/.state-transition.acquire/hostname
echo "2000-01-01T00:00:00Z" > checkpoints/.state-transition.acquire/timestamp
echo "stale-acq-txid" > checkpoints/.state-transition.acquire/txid

out13=$(mktemp)
OUTPUT_FILES+=("$out13")
SCENARIOS=$((SCENARIOS+1))
set +e
WORKFLOW_TEST_MODE=1 scripts/workflow/transition-state FAKE_STATE CONTRACT_DRAFTED workflow-test "stale mutex test" > "$out13" 2>&1
EC=$?
set -e

LOCK_CLEAN=0
[ ! -d checkpoints/.state-transition.acquire ] && LOCK_CLEAN=1
STALE_REMNANTS=$(find checkpoints -maxdepth 1 -name ".state-transition.acquire.stale.*" 2>/dev/null | head -1 || echo "")

if [ "$EC" -eq 1 ] && [ "$LOCK_CLEAN" -eq 1 ] && [ -z "$STALE_REMNANTS" ]; then
  PASS=$((PASS+1))
  echo "  PASS: 13 ACQUISITION_MUTEX_STALE_TAKEOVER (ec=1, lock released, no remnants)"
else
  FAIL_COUNT=$((FAIL_COUNT+1))
  echo "  FAIL: 13 ACQUISITION_MUTEX_STALE_TAKEOVER (ec=$EC, lock_clean=$LOCK_CLEAN, remnants=${STALE_REMNANTS:-none})"
  head -5 "$out13"
fi

rm -rf checkpoints/.state-transition.acquire checkpoints/.state-transition.acquire.stale.* 2>/dev/null || true

# ═══════════════════════════════════════════════════════════════════
# Scenario 14: PRODUCTION_FAULT_ENV
# ═══════════════════════════════════════════════════════════════════
echo "=== Scenario 14: PRODUCTION_FAULT_ENV ==="
restore_fixture

out14=$(mktemp)
OUTPUT_FILES+=("$out14")
SCENARIOS=$((SCENARIOS+1))
set +e
WORKFLOW_TEST_FAULT=before_history_replace scripts/workflow/transition-state ROADMAP_DEFINED CONTRACT_DRAFTED workflow-test "fault without mode" > "$out14" 2>&1
EC=$?
set -e
OUT14=$(cat "$out14")

if [ "$EC" -eq 1 ] && echo "$OUT14" | grep -qF "WORKFLOW_TEST_FAULT requires WORKFLOW_TEST_MODE"; then
  PASS=$((PASS+1))
  echo "  PASS: 14 PRODUCTION_FAULT_ENV (ec=1, exact error)"
else
  FAIL_COUNT=$((FAIL_COUNT+1))
  echo "  FAIL: 14 PRODUCTION_FAULT_ENV (ec=$EC)"
  head -5 "$out14"
fi

# ═══════════════════════════════════════════════════════════════════
# Summary
# ═══════════════════════════════════════════════════════════════════
echo "TEST_28_SCENARIOS_TOTAL=$SCENARIOS"
echo "TEST_28_SCENARIOS_PASSED=$PASS"
echo "TEST_28_SCENARIOS_FAILED=$FAIL_COUNT"
echo "--- Summary: $PASS/$SCENARIOS pass, $FAIL_COUNT fail ---"
if [ "$FAIL_COUNT" -gt 0 ]; then
  echo "FAIL: test_28_missing_approval — $FAIL_COUNT scenario(s) failed"
  exit 1
fi
echo "PASS: test_28_missing_approval"
exit 0
