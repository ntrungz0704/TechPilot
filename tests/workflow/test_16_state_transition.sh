#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &> /dev/null; then echo "SKIP: test_16_state_transition (no jq)"; exit 0; fi

KNOWNS=$(jq -r '.lifecycle_status' checkpoints/STATE.json)
cp checkpoints/STATE.json checkpoints/STATE.json.bak
if [ -f checkpoints/STATE_HISTORY.jsonl ]; then cp checkpoints/STATE_HISTORY.jsonl checkpoints/STATE_HISTORY.jsonl.bak; fi

restore() {
  cp checkpoints/STATE.json.bak checkpoints/STATE.json 2>/dev/null || true
  rm -f checkpoints/STATE.json.bak
  if [ -f checkpoints/STATE_HISTORY.jsonl.bak ]; then cp checkpoints/STATE_HISTORY.jsonl.bak checkpoints/STATE_HISTORY.jsonl 2>/dev/null || true; rm -f checkpoints/STATE_HISTORY.jsonl.bak; fi
}
trap restore EXIT

set +e
scripts/workflow/transition-state "$KNOWNS" CONTRACT_DRAFTED workflow-test "test 16 transition" > /dev/null 2>&1
TRANSITION_EXIT=$?
set -e

if [ "$TRANSITION_EXIT" -ne 0 ]; then
  echo "FAIL: test_16_state_transition — transition-state exited $TRANSITION_EXIT"
  exit 1
fi

NEW_STATE=$(jq -r '.lifecycle_status' checkpoints/STATE.json)
if [ "$NEW_STATE" != "CONTRACT_DRAFTED" ]; then
  echo "FAIL: test_16_state_transition — expected CONTRACT_DRAFTED, got $NEW_STATE"
  exit 1
fi

echo "PASS: test_16_state_transition"
exit 0
