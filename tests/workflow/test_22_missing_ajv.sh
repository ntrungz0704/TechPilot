#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v node &>/dev/null; then echo "SKIP: test_22 (no node)"; exit 0; fi

EVIDENCE_DIR="checkpoints/CP03/evidence"
mkdir -p "$EVIDENCE_DIR"
CONTRACT="$EVIDENCE_DIR/test_22_contract.yaml"
cat > "$CONTRACT" << 'EOF'
CHECKPOINT_ID: CHECKPOINT_3
TITLE: "Test"
LIFECYCLE_STATUS: ROADMAP_DEFINED
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
  - "Test"
REQUIRED_TESTS:
  - command: echo
    args: ["hello"]
    expected_exit_code: 0
REQUIRED_EVIDENCE:
  - "Test"
EOF

# Temporarily hide ajv
AJV_PATH="node_modules/ajv"
if [ ! -d "$AJV_PATH" ]; then echo "SKIP: test_22 — ajv not installed"; rm -f "$CONTRACT"; exit 0; fi
mv "$AJV_PATH" "${AJV_PATH}.bak"
set +e
scripts/workflow/validate-contract "$CONTRACT" > /dev/null 2>&1
EXIT_CODE=$?
set -e
mv "${AJV_PATH}.bak" "$AJV_PATH"
rm -f "$CONTRACT"
if [ "$EXIT_CODE" -eq 1 ]; then echo "PASS: test_22_missing_ajv"; exit 0; else echo "FAIL: test_22_missing_ajv — expected exit 1, got $EXIT_CODE"; exit 1; fi
