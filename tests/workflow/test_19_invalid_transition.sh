#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &> /dev/null; then
  echo "SKIP: test_19_invalid_transition — jq not available"
  exit 0
fi

cp checkpoints/STATE.json checkpoints/STATE.json.bak

STATE_BEFORE=$(jq -r '.lifecycle_status' checkpoints/STATE.json)
STATE_BEFORE_SHA=$(jq -j '.candidate_sha, .reviewed_sha, .last_updated' checkpoints/STATE.json)

set +e
scripts/workflow/transition-state CLOSED ROADMAP_DEFINED workflow-test "test 19 invalid" > /dev/null 2>&1
EXIT_CODE=$?
set -e

STATE_AFTER=$(jq -r '.lifecycle_status' checkpoints/STATE.json)

cp checkpoints/STATE.json.bak checkpoints/STATE.json
rm -f checkpoints/STATE.json.bak

if [ "$EXIT_CODE" -eq 1 ] && [ "$STATE_AFTER" = "$STATE_BEFORE" ]; then
  echo "PASS: test_19_invalid_transition"
  exit 0
else
  echo "FAIL: test_19_invalid_transition — exit=$EXIT_CODE, expected exit 1 and STATE unchanged (was $STATE_BEFORE, got $STATE_AFTER)"
  exit 1
fi
