#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &>/dev/null || ! command -v node &>/dev/null; then echo "SKIP: test_25 (missing deps)"; exit 0; fi

STATE_BEFORE=$(jq -r '.lifecycle_status' checkpoints/STATE.json)
cp checkpoints/STATE.json checkpoints/STATE.json.bak
cp checkpoints/STATE_HISTORY.jsonl checkpoints/STATE_HISTORY.jsonl.bak

restore() {
  cp checkpoints/STATE.json.bak checkpoints/STATE.json 2>/dev/null || true
  cp checkpoints/STATE_HISTORY.jsonl.bak checkpoints/STATE_HISTORY.jsonl 2>/dev/null || true
  rm -f checkpoints/STATE.json.bak checkpoints/STATE_HISTORY.jsonl.bak
}
trap restore EXIT

# Compute byte-for-byte SHA-256 originals
ORIG_STATE_SHA=$(sha256sum checkpoints/STATE.json | cut -d' ' -f1)
ORIG_HISTORY_SHA=$(sha256sum checkpoints/STATE_HISTORY.jsonl | cut -d' ' -f1)

# Run transition with fault injection at before_history_replace
export WORKFLOW_TEST_MODE=1
export WORKFLOW_TEST_FAULT=after_state_replaced
LOCK_PATH="checkpoints/.state-transition.lock"

set +e
output=$(scripts/workflow/transition-state "$STATE_BEFORE" CONTRACT_DRAFTED workflow-test "test write failure with fault" 2>&1)
EXIT=$?
set -e

unset WORKFLOW_TEST_MODE
unset WORKFLOW_TEST_FAULT

# Verify: exit non-zero
if [ "$EXIT" -eq 0 ]; then
  echo "FAIL: test_25 — transition-state should have failed but exited 0"
  echo "$output"
  exit 1
fi

# Verify: STATE byte-for-byte unchanged
CUR_STATE_SHA=$(sha256sum checkpoints/STATE.json | cut -d' ' -f1)
if [ "$CUR_STATE_SHA" != "$ORIG_STATE_SHA" ]; then
  echo "FAIL: test_25 — STATE.json changed (SHA mismatch)"
  exit 1
fi

# Verify: HISTORY byte-for-byte unchanged
CUR_HISTORY_SHA=$(sha256sum checkpoints/STATE_HISTORY.jsonl | cut -d' ' -f1)
if [ "$CUR_HISTORY_SHA" != "$ORIG_HISTORY_SHA" ]; then
  echo "FAIL: test_25 — STATE_HISTORY.jsonl changed (SHA mismatch)"
  exit 1
fi

# Verify: lock released (no .state-transition.lock)
if [ -d "$LOCK_PATH" ]; then
  echo "FAIL: test_25 — lock not released after rollback"
  exit 1
fi

# Verify: no dangling WAL
WAL_DIRS=$(find checkpoints -maxdepth 1 -name ".wal-*" -type d 2>/dev/null)
if [ -n "$WAL_DIRS" ]; then
  echo "FAIL: test_25 — dangling WAL directories found after rollback:"
  echo "$WAL_DIRS"
  exit 1
fi
echo "OK: No dangling WAL directories"

echo "PASS: test_25_state_write_failure (fault injection rollback verified)"
exit 0
