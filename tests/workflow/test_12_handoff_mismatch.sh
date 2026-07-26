#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &>/dev/null || ! command -v node &>/dev/null; then echo "SKIP: test_12 (missing jq/node)"; exit 0; fi

EVIDENCE_DIR="checkpoints/CP03/evidence"
mkdir -p "$EVIDENCE_DIR"
HEAD_SHA=$(git rev-parse HEAD)
BASE_SHA=$(jq -r '.base_sha' checkpoints/STATE.json)
if [ -z "$BASE_SHA" ] || [ "$BASE_SHA" = "null" ]; then echo "SKIP: test_12 (no base_sha)"; exit 0; fi
if ! GIT_FILES=$(git diff --name-only "${BASE_SHA}...${HEAD_SHA}" 2>/dev/null); then
  echo "SKIP: test_12 (git diff failed)"
  exit 0
fi
FILES_JSON=$(echo "$GIT_FILES" | jq -R -s -c 'split("\n") | map(select(length > 0))')
HANDOFF="$EVIDENCE_DIR/test_12_handoff.json"

# Create evidence files matching contract REQUIRED_EVIDENCE for testing
mkdir -p "$EVIDENCE_DIR"
echo "test" > "$EVIDENCE_DIR/homepage-1366x768.png"
echo "test" > "$EVIDENCE_DIR/geometry-gate.json"
echo "test" > "$EVIDENCE_DIR/test-summary.txt"

ALL_EVIDENCE='["checkpoints/CP03/evidence/homepage-1366x768.png","checkpoints/CP03/evidence/geometry-gate.json","checkpoints/CP03/evidence/test-summary.txt"]'

SCENARIOS_PASSED=0
SCENARIOS_FAILED=0

# Helper
run_verify() {
  local expected_exit="${1:-0}"
  shift
  jq -n "$@" > "$HANDOFF"
  set +e
  scripts/workflow/verify-handoff "$HANDOFF" > /tmp/verify-handoff-output.txt 2>&1
  local EC=$?
  set -e
  rm -f "$HANDOFF"
  if [ "$EC" -eq "$expected_exit" ]; then
    SCENARIOS_PASSED=$((SCENARIOS_PASSED+1))
    echo "  PASS (ec=$EC expected=$expected_exit)"
    return 0
  else
    SCENARIOS_FAILED=$((SCENARIOS_FAILED+1))
    echo "  FAIL (ec=$EC expected=$expected_exit)"
    cat /tmp/verify-handoff-output.txt
    return 1
  fi
}

# A. Valid handoff (complete, schema-compliant, matches contract REQUIRED_TESTS and REQUIRED_EVIDENCE) → PASS
echo "--- A: valid handoff ---"
run_verify 0 \
  --arg schema_version "1" --arg checkpoint_id "CHECKPOINT_3" \
  --arg base_sha "$BASE_SHA" --arg candidate_sha "$HEAD_SHA" \
  --argjson changed_files "$FILES_JSON" \
  --argjson test_results '[{"executable":"bash","args":["tests/browser/serve-and-test.sh"],"expected_exit_code":0,"actual_exit_code":0,"result":"PASS","evidence_path":""},{"executable":"php","args":["tests/CatalogGroupTest.php"],"expected_exit_code":0,"actual_exit_code":0,"result":"PASS","evidence_path":""}]' \
  --arg writer_declaration "READY_FOR_REVIEW" \
  --argjson evidence_paths "$ALL_EVIDENCE" \
  '{schema_version:$schema_version, checkpoint_id:$checkpoint_id, base_sha:$base_sha, candidate_sha:$candidate_sha, changed_files:$changed_files, test_results:$test_results, writer_declaration:$writer_declaration, evidence_paths:$evidence_paths}' || true

# B. Handoff with wrong actual_exit_code + result=PASS → FAIL (contradiction)
# Same exact test set as A, but first test has actual_exit_code=1 while result=PASS
echo "--- B: wrong exit + result=PASS ---"
run_verify 1 \
  --arg schema_version "1" --arg checkpoint_id "CHECKPOINT_3" \
  --arg base_sha "$BASE_SHA" --arg candidate_sha "$HEAD_SHA" \
  --argjson changed_files "$FILES_JSON" \
  --argjson test_results '[{"executable":"bash","args":["tests/browser/serve-and-test.sh"],"expected_exit_code":0,"actual_exit_code":1,"result":"PASS","evidence_path":""},{"executable":"php","args":["tests/CatalogGroupTest.php"],"expected_exit_code":0,"actual_exit_code":0,"result":"PASS","evidence_path":""}]' \
  --arg writer_declaration "READY_FOR_REVIEW" \
  --argjson evidence_paths "$ALL_EVIDENCE" \
  '{schema_version:$schema_version, checkpoint_id:$checkpoint_id, base_sha:$base_sha, candidate_sha:$candidate_sha, changed_files:$changed_files, test_results:$test_results, writer_declaration:$writer_declaration, evidence_paths:$evidence_paths}' || true

# C. Handoff with correct exit + result=FAIL → FAIL (contradiction)
# Same exact test set as A, but first test has result=FAIL while actual_exit_code=0
echo "--- C: correct exit + result=FAIL ---"
run_verify 1 \
  --arg schema_version "1" --arg checkpoint_id "CHECKPOINT_3" \
  --arg base_sha "$BASE_SHA" --arg candidate_sha "$HEAD_SHA" \
  --argjson changed_files "$FILES_JSON" \
  --argjson test_results '[{"executable":"bash","args":["tests/browser/serve-and-test.sh"],"expected_exit_code":0,"actual_exit_code":0,"result":"FAIL","evidence_path":""},{"executable":"php","args":["tests/CatalogGroupTest.php"],"expected_exit_code":0,"actual_exit_code":0,"result":"PASS","evidence_path":""}]' \
  --arg writer_declaration "READY_FOR_REVIEW" \
  --argjson evidence_paths "$ALL_EVIDENCE" \
  '{schema_version:$schema_version, checkpoint_id:$checkpoint_id, base_sha:$base_sha, candidate_sha:$candidate_sha, changed_files:$changed_files, test_results:$test_results, writer_declaration:$writer_declaration, evidence_paths:$evidence_paths}' || true

# D. changed_files mismatch → FAIL
echo "--- D: changed_files mismatch ---"
run_verify 1 \
  --arg schema_version "1" --arg checkpoint_id "CHECKPOINT_3" \
  --arg base_sha "$BASE_SHA" --arg candidate_sha "$HEAD_SHA" \
  --argjson changed_files '["nonexistent.txt"]' \
  --argjson test_results '[]' \
  --arg writer_declaration "READY_FOR_REVIEW" \
  --argjson evidence_paths "$ALL_EVIDENCE" \
  '{schema_version:$schema_version, checkpoint_id:$checkpoint_id, base_sha:$base_sha, candidate_sha:$candidate_sha, changed_files:$changed_files, test_results:$test_results, writer_declaration:$writer_declaration, evidence_paths:$evidence_paths}' || true

rm -f "$EVIDENCE_DIR/homepage-1366x768.png" "$EVIDENCE_DIR/geometry-gate.json" "$EVIDENCE_DIR/test-summary.txt"

echo "--- Summary: $SCENARIOS_PASSED pass, $SCENARIOS_FAILED fail ---"
if [ "$SCENARIOS_FAILED" -eq 0 ] && [ "$SCENARIOS_PASSED" -eq 4 ]; then
  echo "PASS: test_12_handoff_mismatch"
  exit 0
else
  echo "FAIL: test_12_handoff_mismatch"
  exit 1
fi
