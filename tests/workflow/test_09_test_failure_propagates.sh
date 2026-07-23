#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &> /dev/null; then
  echo "SKIP: test_09_test_failure_propagates — jq not available"
  exit 0
fi
if ! command -v node &> /dev/null; then
  echo "SKIP: test_09_test_failure_propagates — node not available"
  exit 0
fi

EVIDENCE_DIR="checkpoints/CP03/evidence"
mkdir -p "$EVIDENCE_DIR"

cp checkpoints/STATE.json checkpoints/STATE.json.bak

jq '.lifecycle_status = "CONTRACT_APPROVED"' checkpoints/STATE.json > checkpoints/STATE.json.tmp && mv checkpoints/STATE.json.tmp checkpoints/STATE.json

CONTRACT="$EVIDENCE_DIR/test_09_contract.yaml"
cat > "$CONTRACT" << 'EOF'
CHECKPOINT_ID: CHECKPOINT_3
TITLE: "Test Failure Propagates"
LIFECYCLE_STATUS: CONTRACT_APPROVED
BASE_SHA: 039ff0d07ef1c5161a4bb4f459b9b008e5cab0dd
CANDIDATE_SHA: null
REVIEWED_SHA: null
SCOPE:
  TARGET_ROUTE: Homepage
  TARGET_VIEWPORT: 1366x768
  ACCEPTANCE_GATE: "test"
ALLOWED_PATHS:
  - tests/
FORBIDDEN_PATHS:
  - _none
ACCEPTANCE_CRITERIA:
  - "Test criterion"
REQUIRED_TESTS:
  - command: "bash -c 'exit 1'"
    expected_exit_code: 0
REQUIRED_EVIDENCE:
  - "Test evidence"
EOF

set +e
scripts/workflow/collect-test-evidence "$CONTRACT" "$EVIDENCE_DIR" > /dev/null 2>&1
EXIT_CODE=$?
set -e

cp checkpoints/STATE.json.bak checkpoints/STATE.json
rm -f checkpoints/STATE.json.bak checkpoints/STATE.json.tmp "$CONTRACT"
rm -f "$EVIDENCE_DIR/test-summary.txt"

if [ "$EXIT_CODE" -eq 1 ]; then
  echo "PASS: test_09_test_failure_propagates"
  exit 0
else
  echo "FAIL: test_09_test_failure_propagates — expected exit 1, got $EXIT_CODE"
  exit 1
fi
