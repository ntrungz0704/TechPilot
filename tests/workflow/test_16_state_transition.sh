#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &> /dev/null; then
  echo "SKIP: test_16_state_transition — jq not available"
  exit 0
fi

cp checkpoints/STATE.json checkpoints/STATE.json.bak
if [ -f checkpoints/STATE_HISTORY.jsonl ]; then
  cp checkpoints/STATE_HISTORY.jsonl checkpoints/STATE_HISTORY.jsonl.bak
fi

scripts/workflow/transition-state ROADMAP_DEFINED CONTRACT_DRAFTED workflow-test "test 16 transition" > /dev/null

NEW_STATE=$(jq -r '.lifecycle_status' checkpoints/STATE.json)
if [ "$NEW_STATE" != "CONTRACT_DRAFTED" ]; then
  echo "FAIL: test_16_state_transition — STATE.json not updated (got $NEW_STATE)"
  cp checkpoints/STATE.json.bak checkpoints/STATE.json
  rm -f checkpoints/STATE.json.bak checkpoints/STATE_HISTORY.jsonl.bak
  exit 1
fi

cp checkpoints/STATE.json.bak checkpoints/STATE.json
rm -f checkpoints/STATE.json.bak
if [ -f checkpoints/STATE_HISTORY.jsonl.bak ]; then
  cp checkpoints/STATE_HISTORY.jsonl.bak checkpoints/STATE_HISTORY.jsonl
  rm -f checkpoints/STATE_HISTORY.jsonl.bak
fi

echo "PASS: test_16_state_transition"
exit 0
