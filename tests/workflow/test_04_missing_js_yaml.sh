#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v node &>/dev/null; then echo "SKIP: test_04 (no node)"; exit 0; fi

EVIDENCE_DIR="checkpoints/CP03/evidence"
mkdir -p "$EVIDENCE_DIR"
CONTRACT="$EVIDENCE_DIR/test_04_contract.yaml"
cat > "$CONTRACT" << 'EOF'
CHECKPOINT_ID: CHECKPOINT_3
TITLE: "Test Missing js-yaml"
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

# Temporarily hide js-yaml
JSYAML_PATH="node_modules/js-yaml"
if [ ! -d "$JSYAML_PATH" ]; then echo "SKIP: test_04 — js-yaml not installed"; rm -f "$CONTRACT"; exit 0; fi
mv "$JSYAML_PATH" "${JSYAML_PATH}.bak"
set +e
scripts/workflow/validate-contract "$CONTRACT" > /dev/null 2>&1
EXIT_CODE=$?
set -e
mv "${JSYAML_PATH}.bak" "$JSYAML_PATH"
rm -f "$CONTRACT"
if [ "$EXIT_CODE" -eq 1 ]; then echo "PASS: test_04_missing_js_yaml"; exit 0; else echo "FAIL: test_04_missing_js_yaml — expected exit 1, got $EXIT_CODE"; exit 1; fi
