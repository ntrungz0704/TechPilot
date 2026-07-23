#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &> /dev/null; then
  echo "SKIP: test_17_history_append — jq not available"
  exit 0
fi

cp checkpoints/STATE.json checkpoints/STATE.json.bak
if [ -f checkpoints/STATE_HISTORY.jsonl ]; then
  cp checkpoints/STATE_HISTORY.jsonl checkpoints/STATE_HISTORY.jsonl.bak
fi

BEFORE_LINES=0
if [ -f checkpoints/STATE_HISTORY.jsonl ]; then
  BEFORE_LINES=$(wc -l < checkpoints/STATE_HISTORY.jsonl | tr -d ' ')
fi

scripts/workflow/transition-state ROADMAP_DEFINED CONTRACT_DRAFTED workflow-test "test 17 history" > /dev/null

AFTER_LINES=$(wc -l < checkpoints/STATE_HISTORY.jsonl | tr -d ' ')

if [ "$AFTER_LINES" -le "$BEFORE_LINES" ]; then
  echo "FAIL: test_17_history_append — STATE_HISTORY.jsonl was not appended ($BEFORE_LINES -> $AFTER_LINES)"
  cp checkpoints/STATE.json.bak checkpoints/STATE.json
  if [ -f checkpoints/STATE_HISTORY.jsonl.bak ]; then
    cp checkpoints/STATE_HISTORY.jsonl.bak checkpoints/STATE_HISTORY.jsonl
    rm -f checkpoints/STATE_HISTORY.jsonl.bak
  fi
  rm -f checkpoints/STATE.json.bak
  exit 1
fi

LAST_LINE=$(tail -n 1 checkpoints/STATE_HISTORY.jsonl)
HEAD_SHA=$(git rev-parse HEAD)

VERIFY=$(echo "$LAST_LINE" | jq -e '
  .from == "ROADMAP_DEFINED" and
  .to == "CONTRACT_DRAFTED" and
  .actor == "workflow-test" and
  .head_sha != null and
  .timestamp != null
' 2>/dev/null) || VERIFY="false"

cp checkpoints/STATE.json.bak checkpoints/STATE.json
rm -f checkpoints/STATE.json.bak
if [ -f checkpoints/STATE_HISTORY.jsonl.bak ]; then
  cp checkpoints/STATE_HISTORY.jsonl.bak checkpoints/STATE_HISTORY.jsonl
  rm -f checkpoints/STATE_HISTORY.jsonl.bak
fi

if [ "$VERIFY" = "true" ]; then
  echo "PASS: test_17_history_append"
  exit 0
else
  echo "FAIL: test_17_history_append — history line fields incorrect"
  exit 1
fi
