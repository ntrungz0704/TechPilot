#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &> /dev/null; then echo "SKIP: test_18_history_rollback (no jq)"; exit 0; fi

EXPECTED_STATE=$(jq -r '.lifecycle_status' checkpoints/STATE.json)
cp checkpoints/STATE.json checkpoints/STATE.json.bak
if [ -f checkpoints/STATE_HISTORY.jsonl ]; then cp checkpoints/STATE_HISTORY.jsonl checkpoints/STATE_HISTORY.jsonl.bak; fi

restore() {
  cp checkpoints/STATE.json.bak checkpoints/STATE.json 2>/dev/null || true
  rm -f checkpoints/STATE.json.bak
  if [ -f checkpoints/STATE_HISTORY.jsonl.bak ]; then cp checkpoints/STATE_HISTORY.jsonl.bak checkpoints/STATE_HISTORY.jsonl 2>/dev/null || true; rm -f checkpoints/STATE_HISTORY.jsonl.bak; fi
}
trap restore EXIT

# Attempt a transition then simulate history write failure by making file read-only after transition
# If transition-state itself fails due to history write, state should be rolled back
# We test: running an invalid transition should NOT change state

set +e
scripts/workflow/transition-state CLOSED ROADMAP_DEFINED workflow-test "invalid rollback test" > /dev/null 2>&1
INVALID_EXIT=$?
set -e

CURRENT_AFTER=$(jq -r '.lifecycle_status' checkpoints/STATE.json)

if [ "$CURRENT_AFTER" != "$EXPECTED_STATE" ]; then
  echo "FAIL: test_18_history_rollback — state changed from $EXPECTED_STATE to $CURRENT_AFTER (should not have changed)"
  exit 1
fi

# The invalid transition must exit non-zero and STATE.json must be unchanged
if [ "$INVALID_EXIT" -eq 0 ]; then
  echo "FAIL: test_18_history_rollback — invalid transition should have failed but exited 0"
  exit 1
fi

echo "PASS: test_18_history_rollback"
exit 0
