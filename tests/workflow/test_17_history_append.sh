#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &> /dev/null; then echo "SKIP: test_17_history_append (no jq)"; exit 0; fi

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
scripts/workflow/transition-state "$KNOWNS" CONTRACT_DRAFTED workflow-test "test 17 history" > /dev/null 2>&1
TRANSITION_EXIT=$?
set -e

if [ "$TRANSITION_EXIT" -ne 0 ]; then
  echo "FAIL: test_17_history_append — transition-state exited $TRANSITION_EXIT"
  exit 1
fi

if ! grep -q '"to":"CONTRACT_DRAFTED"' checkpoints/STATE_HISTORY.jsonl 2>/dev/null; then
  echo "FAIL: test_17_history_append — history entry not found"
  exit 1
fi

LAST_LINE=$(tail -1 checkpoints/STATE_HISTORY.jsonl)
for field in timestamp from to checkpoint_id actor head_sha reason; do
  if ! echo "$LAST_LINE" | jq -e ".$field" &>/dev/null; then
    echo "FAIL: test_17_history_append — missing field $field"
    exit 1
  fi
done

echo "PASS: test_17_history_append"
exit 0
