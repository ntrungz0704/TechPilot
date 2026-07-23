#!/usr/bin/env bash
set -euo pipefail

echo "=== Positive: validate-contract with valid contract ==="

REPO_ROOT="$(cd "$(dirname "$0")"/../.. && pwd)"
cd "$REPO_ROOT"

# Use evidence dir for temp files (real path on all platforms)
TEST_FILE="checkpoints/CP03/evidence/test_valid_contract.yaml"
mkdir -p checkpoints/CP03/evidence

# Create a valid test contract matching the real contract format
cat > "$TEST_FILE" << 'EOF'
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
  - "Test criterion one"
  - "Test criterion two"
REQUIRED_TESTS:
  - command: "echo ok"
    expected_exit_code: 0
REQUIRED_EVIDENCE:
  - "test output"
EOF

set +e
output=$(scripts/workflow/validate-contract "$TEST_FILE" "docs/workflow/schemas/task-contract.schema.json" 2>&1)
exit_code=$?
set -e

rm -f "$TEST_FILE"

if [ $exit_code -eq 0 ]; then
  echo "PASS: validate-contract exited 0 for valid contract"
else
  echo "FAIL: validate-contract exited $exit_code for valid contract"
  echo "Output:"
  echo "$output"
  exit 1
fi

echo "=== Positive test PASSED ==="
