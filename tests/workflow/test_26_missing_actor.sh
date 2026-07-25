#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &>/dev/null; then echo "SKIP: test_26 (no jq)"; exit 0; fi

STATE_BEFORE=$(jq -r '.lifecycle_status' checkpoints/STATE.json)
cp checkpoints/STATE.json checkpoints/STATE.json.bak
if [ -f checkpoints/STATE_HISTORY.jsonl ]; then cp checkpoints/STATE_HISTORY.jsonl checkpoints/STATE_HISTORY.jsonl.bak; fi

restore() {
  cp checkpoints/STATE.json.bak checkpoints/STATE.json 2>/dev/null || true
  rm -f checkpoints/STATE.json.bak
  if [ -f checkpoints/STATE_HISTORY.jsonl.bak ]; then cp checkpoints/STATE_HISTORY.jsonl.bak checkpoints/STATE_HISTORY.jsonl 2>/dev/null || true; rm -f checkpoints/STATE_HISTORY.jsonl.bak; fi
}
trap restore EXIT

set +e
output=$(scripts/workflow/transition-state "$STATE_BEFORE" CONTRACT_DRAFTED "" "test missing actor" 2>&1)
EXIT=$?
set -e

STATE_AFTER=$(jq -r '.lifecycle_status' checkpoints/STATE.json)
if [ "$EXIT" -eq 1 ] && [ "$STATE_AFTER" = "$STATE_BEFORE" ]; then
  echo "PASS: test_26_missing_actor"
  exit 0
else
  echo "FAIL: test_26_missing_actor — exit=$EXIT, state unchanged=$([ "$STATE_AFTER" = "$STATE_BEFORE" ] && echo yes || echo no)"
  exit 1
fi
