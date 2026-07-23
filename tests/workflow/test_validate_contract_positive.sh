#!/usr/bin/env bash
set -euo pipefail

echo "=== Positive: validate-contract with valid contract ==="
SCRIPT_DIR="$(cd "$(dirname "$0")/../../scripts/workflow" && pwd)"
TEST_DIR="$(mktemp -d)"
trap 'rm -rf "$TEST_DIR"' EXIT

# Create a valid test contract
cat > "$TEST_DIR/valid_contract.yaml" << 'EOF'
CHECKPOINT_ID: CHECKPOINT_3
TITLE: "Test contract"
LIFECYCLE_STATUS: ROADMAP_DEFINED
BASE_SHA: 039ff0d07ef1c5161a4bb4f459b9b008e5cab0dd
CANDIDATE_SHA: null
REVIEWED_SHA: null
SCOPE:
  TARGET_ROUTE: Homepage
  TARGET_VIEWPORT: 1366x768
  SCROLL_Y: 0
  ACCEPTANCE_GATE: "featuresBar.getBoundingClientRect().bottom <= 764"
  VISIBLE_SECTIONS:
    - Topbar
    - Main Header
  OUT_OF_SCOPE:
    - Category drawer
  ALLOWED_PATHS:
    - tests/**
  FORBIDDEN_PATHS:
    - docs/governance/**
ACCEPTANCE_CRITERIA:
  - "Test criterion"
REQUIRED_TESTS:
  - command: "echo ok"
    expected_exit_code: 0
REQUIRED_EVIDENCE:
  - "test output"
EOF

SCHEMA_PATH="$(cd "$(dirname "$0")/../../docs/workflow/schemas" && pwd)/task-contract.schema.json"

set +e
output=$("$SCRIPT_DIR/validate-contract" "$TEST_DIR/valid_contract.yaml" "$SCHEMA_PATH" 2>&1)
exit_code=$?
set -e

if [ $exit_code -eq 0 ]; then
  echo "PASS: validate-contract exited 0 for valid contract"
else
  echo "FAIL: validate-contract exited $exit_code for valid contract"
  echo "Output: $output"
  exit 1
fi

echo "=== Positive test PASSED ==="
