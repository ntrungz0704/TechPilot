#!/usr/bin/env bash
set -euo pipefail

echo "=== Negative: validate-contract with malformed contract ==="
SCRIPT_DIR="$(cd "$(dirname "$0")/../../scripts/workflow" && pwd)"
TEST_DIR="$(mktemp -d)"
trap 'rm -rf "$TEST_DIR"' EXIT

# Create a malformed contract (missing required field)
cat > "$TEST_DIR/malformed_contract.yaml" << 'EOF'
CHECKPOINT_ID: CHECKPOINT_3
TITLE: "Malformed contract"
# Missing LIFECYCLE_STATUS, BASE_SHA, SCOPE, etc.
SOME_RANDOM_KEY: "not valid"
EOF

SCHEMA_PATH="$(cd "$(dirname "$0")/../../docs/workflow/schemas" && pwd)/task-contract.schema.json"

set +e
output=$("$SCRIPT_DIR/validate-contract" "$TEST_DIR/malformed_contract.yaml" "$SCHEMA_PATH" 2>&1)
exit_code=$?
set -e

if [ $exit_code -ne 0 ]; then
  echo "PASS: validate-contract exited non-zero ($exit_code) for malformed contract"
else
  echo "FAIL: validate-contract exited 0 for malformed contract (expected non-zero)"
  echo "Output: $output"
  exit 1
fi

echo "=== Negative test PASSED ==="
