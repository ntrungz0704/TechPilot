#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

MANDATORY_TESTS=(
  "test_01_contract_positive.sh"
  "test_02_contract_negative.sh"
  "test_03_schema_mismatch.sh"
  "test_04_missing_node.sh"
  "test_05_outside_allowlist.sh"
  "test_06_forbidden_path.sh"
  "test_07_bootstrap_production.sh"
  "test_08_earlier_pr_violation.sh"
  "test_09_test_failure_propagates.sh"
  "test_10_missing_required_test.sh"
  "test_11_unsafe_command.sh"
  "test_12_handoff_mismatch.sh"
  "test_13_missing_evidence.sh"
  "test_14_candidate_sha_mismatch.sh"
  "test_15_null_reviewed_sha.sh"
  "test_16_state_transition.sh"
  "test_17_history_append.sh"
  "test_18_history_rollback.sh"
  "test_19_invalid_transition.sh"
  "test_20_approval_invalidation.sh"
)

MISSING=0
for test_file in "${MANDATORY_TESTS[@]}"; do
  if [ ! -f "tests/workflow/$test_file" ]; then
    echo "MISSING: tests/workflow/$test_file"
    MISSING=1
  fi
done

if [ "$MISSING" -eq 1 ]; then
  echo "FAIL: Mandatory test files are missing"
  exit 1
fi

ALL_GLOB=(tests/workflow/test_*.sh)
ALL_TESTS=()
for f in "${ALL_GLOB[@]}"; do
  base=$(basename "$f")
  if [ "$base" != "test_control.sh" ]; then
    ALL_TESTS+=("$f")
  fi
done

if [ ${#ALL_TESTS[@]} -eq 0 ]; then
  echo "FAIL: No test files found via glob tests/workflow/test_*.sh"
  exit 1
fi

echo "=== Test Control ==="
echo "Found ${#ALL_TESTS[@]} test files"
echo "Mandatory: ${#MANDATORY_TESTS[@]}"
echo ""

PASS=0
FAIL=0
SKIP=0
RESULTS=()

set +e
for test_path in "${ALL_TESTS[@]}"; do
  test_name=$(basename "$test_path")
  echo "--- $test_name ---"
  OUTPUT=$(bash "$test_path" 2>&1)
  EXIT=$?
  printf "%s\n" "$OUTPUT"

  if echo "$OUTPUT" | grep -q "^SKIP:"; then
    SKIP=$((SKIP + 1))
    RESULTS+=("SKIP  $test_name")
  elif [ "$EXIT" -eq 0 ]; then
    PASS=$((PASS + 1))
    RESULTS+=("PASS  $test_name")
  else
    FAIL=$((FAIL + 1))
    RESULTS+=("FAIL  $test_name")
  fi
  echo ""
done
set -e

echo "=== Summary ==="
for r in "${RESULTS[@]}"; do
  echo "$r"
done

echo ""
echo "Total:   ${#MANDATORY_TESTS[@]}"
echo "Ran:     $((PASS + FAIL + SKIP))"
echo "Passed:  $PASS"
echo "Failed:  $FAIL"
echo "Skipped: $SKIP"

if [ "$FAIL" -gt 0 ]; then
  exit 1
fi
exit 0
