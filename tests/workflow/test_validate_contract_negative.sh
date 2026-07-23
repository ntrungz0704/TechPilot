#!/usr/bin/env bash
set -euo pipefail

echo "=== Negative: validate-contract with malformed contract ==="

REPO_ROOT="$(cd "$(dirname "$0")"/../.. && pwd)"
cd "$REPO_ROOT"

# Use evidence dir for temp files
TEST_FILE="checkpoints/CP03/evidence/test_malformed_contract.yaml"

# Create a malformed contract (missing required field)
cat > "$TEST_FILE" << 'EOF'
CHECKPOINT_ID: CHECKPOINT_3
TITLE: "Malformed contract"
# Missing LIFECYCLE_STATUS, BASE_SHA, SCOPE, etc.
SOME_RANDOM_KEY: "not valid"
EOF

set +e
output=$(scripts/workflow/validate-contract "$TEST_FILE" "docs/workflow/schemas/task-contract.schema.json" 2>&1)
exit_code=$?
set -e

rm -f "$TEST_FILE"

if [ $exit_code -ne 0 ]; then
  echo "PASS: validate-contract exited non-zero ($exit_code) for malformed contract"
else
  echo "FAIL: validate-contract exited 0 for malformed contract (expected non-zero)"
  echo "Output:"
  echo "$output"
  exit 1
fi

echo "=== Negative test PASSED ==="
