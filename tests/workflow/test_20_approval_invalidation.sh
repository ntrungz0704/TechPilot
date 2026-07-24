#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &>/dev/null; then echo "SKIP: test_20 (no jq)"; exit 0; fi

HEAD_SHA=$(git rev-parse HEAD)
LOCK_PATH="checkpoints/.state-transition.lock"
cp checkpoints/STATE.json checkpoints/STATE.json.bak
cp checkpoints/STATE_HISTORY.jsonl checkpoints/STATE_HISTORY.jsonl.bak

# Count original history lines
ORIG_HISTORY_COUNT=$(wc -l < checkpoints/STATE_HISTORY.jsonl)

restore() {
  cp checkpoints/STATE.json.bak checkpoints/STATE.json 2>/dev/null || true
  cp checkpoints/STATE_HISTORY.jsonl.bak checkpoints/STATE_HISTORY.jsonl 2>/dev/null || true
  rm -f checkpoints/STATE.json.bak checkpoints/STATE_HISTORY.jsonl.bak
}
trap restore EXIT

# Capture original SHA-256
ORIG_STATE_SHA=$(sha256sum checkpoints/STATE.json | cut -d' ' -f1)
ORIG_HISTORY_SHA=$(sha256sum checkpoints/STATE_HISTORY.jsonl | cut -d' ' -f1)

# Set lifecycle to HERMES_VERIFIED with reviewed_sha != HEAD (simulating stale approval)
jq --arg sha "$HEAD_SHA" '.lifecycle_status = "HERMES_VERIFIED" | .reviewed_sha = "0000000000000000000000000000000000000000" | .candidate_sha = "0000000000000000000000000000000000000000"' checkpoints/STATE.json > checkpoints/STATE.json.tmp && mv checkpoints/STATE.json.tmp checkpoints/STATE.json

# Run transition-state from HERMES_VERIFIED to WAITING_FOR_SEMANTIC_REVIEW
# Should detect reviewed_sha != HEAD, invalidate, and redirect to IMPLEMENTING
set +e
output=$(scripts/workflow/transition-state HERMES_VERIFIED WAITING_FOR_SEMANTIC_REVIEW workflow-test "test approval invalidation" 2>&1)
EXIT=$?
set -e

# Verify: must exit 0 (transition occurred, with invalidation)
if [ "$EXIT" -ne 0 ]; then
  echo "FAIL: test_20 — transition-state should have succeeded but exited $EXIT"
  echo "$output"
  exit 1
fi

# Verify final lifecycle is IMPLEMENTING (overridden by invalidation)
FINAL_LIFECYCLE=$(jq -r '.lifecycle_status' checkpoints/STATE.json)
if [ "$FINAL_LIFECYCLE" != "IMPLEMENTING" ]; then
  echo "FAIL: test_20 — expected IMPLEMENTING, got $FINAL_LIFECYCLE"
  exit 1
fi
echo "OK: Final lifecycle = IMPLEMENTING"

# Verify candidate_sha and reviewed_sha cleared
FIN_CANDIDATE=$(jq -r '.candidate_sha // "null"' checkpoints/STATE.json)
FIN_REVIEWED=$(jq -r '.reviewed_sha // "null"' checkpoints/STATE.json)
if [ "$FIN_CANDIDATE" != "null" ]; then
  echo "FAIL: test_20 — candidate_sha should be null, got $FIN_CANDIDATE"
  exit 1
fi
if [ "$FIN_REVIEWED" != "null" ]; then
  echo "FAIL: test_20 — reviewed_sha should be null, got $FIN_REVIEWED"
  exit 1
fi
echo "OK: candidate_sha = null, reviewed_sha = null"

# Verify history: should have ORIG_HISTORY_COUNT + 2 entries (transition + invalidation)
NEW_HISTORY_COUNT=$(wc -l < checkpoints/STATE_HISTORY.jsonl)
EXPECTED_COUNT=$((ORIG_HISTORY_COUNT + 2))
if [ "$NEW_HISTORY_COUNT" -ne "$EXPECTED_COUNT" ]; then
  echo "FAIL: test_20 — expected $EXPECTED_COUNT history lines, got $NEW_HISTORY_COUNT"
  exit 1
fi
echo "OK: History has $NEW_HISTORY_COUNT lines (original $ORIG_HISTORY_COUNT + 2 new)"

# Verify last two history entries are valid JSON and form a coherent WAL transaction
HISTORY_ENTRY_1=$(tail -2 checkpoints/STATE_HISTORY.jsonl | head -1)
HISTORY_ENTRY_2=$(tail -1 checkpoints/STATE_HISTORY.jsonl)

# First entry (second-to-last) should be the transition from HERMES_VERIFIED to IMPLEMENTING
ENTRY1_FROM=$(echo "$HISTORY_ENTRY_1" | jq -r '.from')
ENTRY1_TO=$(echo "$HISTORY_ENTRY_1" | jq -r '.to')
if [ "$ENTRY1_FROM" != "HERMES_VERIFIED" ]; then
  echo "FAIL: test_20 — expected transition from HERMES_VERIFIED, got from=$ENTRY1_FROM"
  exit 1
fi
if [ "$ENTRY1_TO" != "IMPLEMENTING" ]; then
  echo "FAIL: test_20 — expected transition to IMPLEMENTING, got to=$ENTRY1_TO"
  exit 1
fi
echo "OK: History entry 1: $ENTRY1_FROM -> $ENTRY1_TO"

# Second entry (last line) should be the invalidation event
ENTRY2_FROM=$(echo "$HISTORY_ENTRY_2" | jq -r '.from')
ENTRY2_TO=$(echo "$HISTORY_ENTRY_2" | jq -r '.to')
ENTRY2_REASON=$(echo "$HISTORY_ENTRY_2" | jq -r '.reason // ""')
if [ "$ENTRY2_FROM" != "HERMES_VERIFIED" ]; then
  echo "FAIL: test_20 — invalidation from should be HERMES_VERIFIED, got $ENTRY2_FROM"
  exit 1
fi
if [ "$ENTRY2_TO" != "IMPLEMENTING" ]; then
  echo "FAIL: test_20 — invalidation to should be IMPLEMENTING, got $ENTRY2_TO"
  exit 1
fi
if ! echo "$ENTRY2_REASON" | grep -qi "approval invalidated"; then
  echo "FAIL: test_20 — invalidation reason missing 'approval invalidated'"
  echo "  Reason: $ENTRY2_REASON"
  exit 1
fi
echo "OK: History entry 2: $ENTRY2_FROM -> $ENTRY2_TO ($ENTRY2_REASON)"

# Verify atomic commit: no dangling WAL
WAL_DIRS=$(find checkpoints -maxdepth 1 -name ".wal-*" -type d 2>/dev/null)
if [ -n "$WAL_DIRS" ]; then
  echo "FAIL: test_20 — dangling WAL directories after commit"
  echo "$WAL_DIRS"
  exit 1
fi
echo "OK: No dangling WAL (atomic WAL transaction committed)"

# Verify lock released
if [ -d "$LOCK_PATH" ]; then
  echo "FAIL: test_20 — lock not released"
  exit 1
fi
echo "OK: Lock released"

echo "PASS: test_20_approval_invalidation (real transition invalidation to IMPLEMENTING)"
exit 0
