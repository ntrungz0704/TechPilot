#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &>/dev/null || ! command -v node &>/dev/null; then echo "SKIP: test_18 (missing deps)"; exit 0; fi

STATE_BEFORE=$(jq -r '.lifecycle_status' checkpoints/STATE.json)
cp checkpoints/STATE.json checkpoints/STATE.json.bak
cp checkpoints/STATE_HISTORY.jsonl checkpoints/STATE_HISTORY.jsonl.bak

restore() {
  cp checkpoints/STATE.json.bak checkpoints/STATE.json 2>/dev/null || true
  cp checkpoints/STATE_HISTORY.jsonl.bak checkpoints/STATE_HISTORY.jsonl 2>/dev/null || true
  rm -f checkpoints/STATE.json.bak checkpoints/STATE_HISTORY.jsonl.bak
}
trap restore EXIT

ORIG_STATE_SHA=$(sha256sum checkpoints/STATE.json | cut -d' ' -f1)
ORIG_HISTORY_SHA=$(sha256sum checkpoints/STATE_HISTORY.jsonl | cut -d' ' -f1)

# Real rollback test with fault injection
export WORKFLOW_TEST_MODE=1
export WORKFLOW_TEST_FAULT=before_history_replace
LOCK_PATH="checkpoints/.state-transition.lock"

set +e
output=$(scripts/workflow/transition-state "$STATE_BEFORE" CONTRACT_DRAFTED workflow-test "rollback fault injection test" 2>&1)
EXIT=$?
set -e

unset WORKFLOW_TEST_MODE
unset WORKFLOW_TEST_FAULT

# Must exit non-zero
if [ "$EXIT" -eq 0 ]; then
  echo "FAIL: test_18 — transition should have failed but exited 0"
  echo "$output"
  exit 1
fi

# STATE must be byte-for-byte original
CUR_STATE_SHA=$(sha256sum checkpoints/STATE.json | cut -d' ' -f1)
if [ "$CUR_STATE_SHA" != "$ORIG_STATE_SHA" ]; then
  echo "FAIL: test_18 — STATE SHA changed (expected $ORIG_STATE_SHA, got $CUR_STATE_SHA)"
  exit 1
fi
echo "OK: STATE SHA-256 unchanged ($CUR_STATE_SHA)"

# HISTORY must be byte-for-byte original
CUR_HISTORY_SHA=$(sha256sum checkpoints/STATE_HISTORY.jsonl | cut -d' ' -f1)
if [ "$CUR_HISTORY_SHA" != "$ORIG_HISTORY_SHA" ]; then
  echo "FAIL: test_18 — HISTORY SHA changed (expected $ORIG_HISTORY_SHA, got $CUR_HISTORY_SHA)"
  exit 1
fi
echo "OK: HISTORY SHA-256 unchanged ($CUR_HISTORY_SHA)"

# Lock released
if [ -d "$LOCK_PATH" ]; then
  echo "FAIL: test_18 — lock not released"
  exit 1
fi
echo "OK: Lock released"

# No dangling WAL directories
WAL_DIRS=$(find checkpoints -maxdepth 1 -name ".wal-*" -type d 2>/dev/null)
if [ -n "$WAL_DIRS" ]; then
  echo "FAIL: test_18 — dangling WAL directories found:"
  echo "$WAL_DIRS"
  exit 1
fi
echo "OK: No dangling WAL directories"

echo "PASS: test_18_history_rollback (real WAL rollback via fault injection)"
exit 0
