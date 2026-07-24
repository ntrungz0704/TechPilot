#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &> /dev/null; then
  echo "SKIP: test_13_missing_evidence — jq not available"
  exit 0
fi
if ! command -v node &> /dev/null; then
  echo "SKIP: test_13_missing_evidence — node not available"
  exit 0
fi

EVIDENCE_DIR="checkpoints/CP03/evidence"
mkdir -p "$EVIDENCE_DIR"

HEAD_SHA=$(git rev-parse HEAD)
BASE_SHA=$(jq -r '.base_sha' checkpoints/STATE.json)

GIT_FILES=$(git diff --name-only "${BASE_SHA}...${HEAD_SHA}" 2>/dev/null || true)
FILES_JSON=$(echo "$GIT_FILES" | jq -R -s -c 'split("\n") | map(select(length > 0))')

HANDOFF="$EVIDENCE_DIR/test_13_handoff.json"
CONTRACT="checkpoints/CP03/TASK_CONTRACT.yaml"

# Ensure the evidence file truly does not exist
NONEXISTENT_EVIDENCE="checkpoints/CP03/evidence/nonexistent_evidence_file_13.txt"
rm -f "$NONEXISTENT_EVIDENCE"

jq -n \
  --arg schema_version "1" \
  --arg checkpoint_id "CHECKPOINT_3" \
  --arg base_sha "$BASE_SHA" \
  --arg candidate_sha "$HEAD_SHA" \
  --argjson changed_files "$FILES_JSON" \
  '{
    schema_version: $schema_version,
    checkpoint_id: $checkpoint_id,
    base_sha: $base_sha,
    candidate_sha: $candidate_sha,
    changed_files: $changed_files,
    test_results: [],
    writer_declaration: "READY_FOR_REVIEW",
    evidence_paths: ["checkpoints/CP03/evidence/nonexistent_evidence_file_13.txt"]
  }' > "$HANDOFF"

outfile=$(mktemp)
set +e
scripts/workflow/verify-handoff "$HANDOFF" "$CONTRACT" > "$outfile" 2>&1
EXIT_CODE=$?
set -e
OUTPUT=$(cat "$outfile")

rm -f "$HANDOFF" "$outfile"

PASS=true

if [ "$EXIT_CODE" -ne 1 ]; then
  echo "FAIL: test_13_missing_evidence — expected exit 1, got $EXIT_CODE"
  PASS=false
fi

if ! echo "$OUTPUT" | grep -qF "FAIL: Evidence path does not exist:"; then
  echo "FAIL: test_13_missing_evidence — missing 'FAIL: Evidence path does not exist:' in output"
  PASS=false
fi

if echo "$OUTPUT" | grep -qF "No such file or directory" 2>/dev/null; then
  echo "FAIL: test_13_missing_evidence — output contains 'No such file or directory'"
  PASS=false
fi

if echo "$OUTPUT" | grep -qF "command not found" 2>/dev/null; then
  echo "FAIL: test_13_missing_evidence — output contains 'command not found'"
  PASS=false
fi

if echo "$OUTPUT" | grep -qF "unbound variable" 2>/dev/null; then
  echo "FAIL: test_13_missing_evidence — output contains 'unbound variable'"
  PASS=false
fi

if $PASS; then
  echo "PASS: test_13_missing_evidence"
  exit 0
else
  echo "--- verify-handoff output ---"
  echo "$OUTPUT" | head -10
  exit 1
fi
