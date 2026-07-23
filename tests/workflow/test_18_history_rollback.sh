#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &> /dev/null; then
  echo "SKIP: test_18_history_rollback — jq not available"
  exit 0
fi

cp checkpoints/STATE.json checkpoints/STATE.json.bak
if [ -f checkpoints/STATE_HISTORY.jsonl ]; then
  cp checkpoints/STATE_HISTORY.jsonl checkpoints/STATE_HISTORY.jsonl.bak
fi

rm -f checkpoints/STATE_HISTORY.jsonl
mkdir checkpoints/STATE_HISTORY.jsonl

set +e
scripts/workflow/transition-state ROADMAP_DEFINED CONTRACT_DRAFTED workflow-test "test 18 rollback" > /dev/null 2>&1
EXIT_CODE=$?
set -e

CURRENT_STATE=$(jq -r '.lifecycle_status' checkpoints/STATE.json)

rm -rf checkpoints/STATE_HISTORY.jsonl
if [ -f checkpoints/STATE_HISTORY.jsonl.bak ]; then
  cp checkpoints/STATE_HISTORY.jsonl.bak checkpoints/STATE_HISTORY.jsonl
  rm -f checkpoints/STATE_HISTORY.jsonl.bak
fi
cp checkpoints/STATE.json.bak checkpoints/STATE.json
rm -f checkpoints/STATE.json.bak

if [ "$EXIT_CODE" -eq 1 ] && [ "$CURRENT_STATE" = "ROADMAP_DEFINED" ]; then
  echo "PASS: test_18_history_rollback"
  exit 0
else
  echo "FAIL: test_18_history_rollback — exit=$EXIT_CODE, state=$CURRENT_STATE (expected exit 1, ROADMAP_DEFINED)"
  exit 1
fi
