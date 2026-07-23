#!/usr/bin/env bash
set -euo pipefail

echo "=== Running all workflow regression tests ==="
SCRIPT_DIR="$(dirname "$0")"

TESTS=(
  "test_validate_contract_positive.sh"
  "test_validate_contract_negative.sh"
)

TOTAL=0
PASSED=0
FAILED=0

for test in "${TESTS[@]}"; do
  if [ ! -f "$SCRIPT_DIR/$test" ]; then
    echo "SKIP: $test (not found)"
    continue
  fi
  TOTAL=$((TOTAL + 1))
  echo ""
  echo "--- Running $test ---"
  set +e
  bash "$SCRIPT_DIR/$test" 2>&1
  exit_code=$?
  set -e
  if [ $exit_code -eq 0 ]; then
    echo "RESULT: $test PASSED"
    PASSED=$((PASSED + 1))
  else
    echo "RESULT: $test FAILED"
    FAILED=$((FAILED + 1))
  fi
done

echo ""
echo "========================================"
echo "Test Results: $PASSED/$TOTAL passed, $FAILED failed"
echo "========================================"

if [ "$FAILED" -gt 0 ]; then
  exit 1
fi
exit 0
