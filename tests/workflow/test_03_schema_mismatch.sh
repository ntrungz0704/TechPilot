#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v node &> /dev/null; then
  echo "SKIP: test_03_schema_mismatch — node not available"
  exit 0
fi

EVIDENCE_DIR="checkpoints/CP03/evidence"
mkdir -p "$EVIDENCE_DIR"

CONTRACT="$EVIDENCE_DIR/test_03_contract.yaml"

cat > "$CONTRACT" << 'EOF'
CHECKPOINT_ID: INVALID
TITLE: "Test Contract Schema Mismatch"
LIFECYCLE_STATUS: ROADMAP_DEFINED
BASE_SHA: 039ff0d07ef1c5161a4bb4f459b9b008e5cab0dd
CANDIDATE_SHA: null
REVIEWED_SHA: null
SCOPE:
  TARGET_ROUTE: Homepage
  TARGET_VIEWPORT: 1366x768
  ACCEPTANCE_GATE: "test gate"
ALLOWED_PATHS:
  - tests/
FORBIDDEN_PATHS:
  - _none
ACCEPTANCE_CRITERIA:
  - "Test criterion"
REQUIRED_TESTS:
  - command: "echo hello"
    expected_exit_code: 0
REQUIRED_EVIDENCE:
  - "Test evidence"
EOF

set +e
scripts/workflow/validate-contract "$CONTRACT" > /dev/null 2>&1
EXIT_CODE=$?
set -e

rm -f "$CONTRACT"

if [ "$EXIT_CODE" -eq 1 ]; then
  echo "PASS: test_03_schema_mismatch"
  exit 0
else
  echo "FAIL: test_03_schema_mismatch — expected exit 1, got $EXIT_CODE"
  exit 1
fi
