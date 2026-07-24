#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

if ! command -v jq >/dev/null 2>&1; then echo "FAIL: test_29 requires jq"; exit 1; fi
if ! command -v node >/dev/null 2>&1; then echo "FAIL: test_29 requires node"; exit 1; fi
if ! command -v git >/dev/null 2>&1; then echo "FAIL: test_29 requires git"; exit 1; fi

CLEANUP_DIRS=()
cleanup_all() { for d in "${CLEANUP_DIRS[@]}"; do [ -d "$d" ] && rm -rf "$d" 2>/dev/null || true; done; }
trap cleanup_all EXIT

TMP=$(mktemp -d)
CLEANUP_DIRS+=("$TMP")
cd "$TMP"
git init -q
git config user.email "test@test.com"
git config user.name "Test"

# Copy the actual scanner
mkdir -p scripts/workflow/lib
cp "$REPO_ROOT/scripts/workflow/scan-forbidden-patterns" scripts/workflow/
cp "$REPO_ROOT/scripts/workflow/lib/"*.js scripts/workflow/lib/ 2>/dev/null || true
cp -R "$REPO_ROOT/docs/workflow/schemas/" docs/workflow/schemas/ 2>/dev/null || true

# Create minimal STATE.json for pre-approval mode
mkdir -p checkpoints
cat > checkpoints/STATE.json << 'EOF'
{"lifecycle_status":"ROADMAP_DEFINED","base_sha":"0000000000000000000000000000000000000001"}
EOF
git add -A && git commit -q -m "init" > /dev/null 2>&1

# Fix base_sha to a valid commit
BASE_SHA=$(git rev-parse HEAD)
jq --arg sha "$BASE_SHA" '.base_sha = $sha' checkpoints/STATE.json > checkpoints/STATE.json.tmp && mv checkpoints/STATE.json.tmp checkpoints/STATE.json
git add -A && git commit -q -m "fixture" > /dev/null 2>&1

SCENARIOS=0; PASS=0; FAIL_COUNT=0
OUTPUT_FILE="$TMP/scanner-output.txt"

run_scanner() {
  local desc="$1" expected="$2"
  SCENARIOS=$((SCENARIOS+1))
  set +e
  bash scripts/workflow/scan-forbidden-patterns > "$OUTPUT_FILE" 2>&1
  local EC=$?
  set -e
  if [ "$EC" -eq "$expected" ]; then
    PASS=$((PASS+1))
    echo "  PASS: $desc (ec=$EC)"
  else
    FAIL_COUNT=$((FAIL_COUNT+1))
    echo "  FAIL: $desc (ec=$EC, expected=$expected)"
    head -5 "$OUTPUT_FILE"
  fi
}

run_scanner_contains() {
  local desc="$1" expected_ec="$2" expected_text="$3"
  SCENARIOS=$((SCENARIOS+1))
  set +e
  bash scripts/workflow/scan-forbidden-patterns > "$OUTPUT_FILE" 2>&1
  local EC=$?
  set -e
  local OUT=$(cat "$OUTPUT_FILE")
  allpass=true
  if [ "$EC" -ne "$expected_ec" ]; then allpass=false; echo "  FAIL: $desc (ec=$EC, expected=$expected_ec)"; fi
  if echo "$OUT" | grep -qF "command not found" 2>/dev/null; then allpass=false; echo "  FAIL: $desc contains command not found"; fi
  if ! echo "$OUT" | grep -qF "$expected_text" 2>/dev/null; then allpass=false; echo "  FAIL: $desc missing '$expected_text'"; fi
  if $allpass; then PASS=$((PASS+1)); echo "  PASS: $desc (ec=$EC)"; else FAIL_COUNT=$((FAIL_COUNT+1)); fi
}

# Scenario 1: JavaScript escape sequence — NOT a violation
echo "=== 1. JS escape sequence ==="
cat > scripts/workflow/testfile.js << 'JS'
throw new Error('Schema validation failed:\n  - ' + msgs.join('\n  - '));
JS
git add -A && git commit -q -m "scenario 1" > /dev/null 2>&1
run_scanner "1. JS escape NOT violation" 0

# Scenario 2: Windows backslash path — IS a violation
echo "=== 2. Windows backslash ==="
rm -f scripts/workflow/testfile.js
if git diff --name-only HEAD 2>/dev/null | grep -q .; then git add -A && git commit -q -m "remove scenario 1 fixture" > /dev/null 2>&1; fi
cat > scripts/workflow/testfile2.js << 'JS'
var path = "C:\Users\Admin\file.txt";
JS
git add -A && git commit -q -m "scenario 2" > /dev/null 2>&1
run_scanner_contains "2. Windows backslash path" 1 "VIOLATION: Absolute Windows path in scripts/workflow/testfile2.js"

# Scenario 3: Windows forward-slash path — IS a violation
echo "=== 3. Windows forward slash ==="
rm -f scripts/workflow/testfile2.js
git add -A && git commit -q -m "remove scenario 2 fixture" > /dev/null 2>&1
test ! -e scripts/workflow/testfile2.js || { echo "FAIL: testfile2.js not removed"; exit 1; }
echo "SCENARIO_3_BACKSLASH_FIXTURE_PRESENT=NO"
cat > scripts/workflow/testfile3.js << 'JS'
var path = "C:/Users/Admin/file.txt";
JS
git add -A && git commit -q -m "scenario 3" > /dev/null 2>&1
run_scanner_contains "3. Windows forward slash path" 1 "VIOLATION: Absolute Windows path in scripts/workflow/testfile3.js"

echo "--- Summary: $PASS/$SCENARIOS pass, $FAIL_COUNT fail ---"
if [ "$FAIL_COUNT" -gt 0 ]; then
  echo "FAIL: test_29_windows_path_scan — $FAIL_COUNT scenario(s) failed"
  exit 1
fi
echo "PASS: test_29_windows_path_scan"
exit 0
