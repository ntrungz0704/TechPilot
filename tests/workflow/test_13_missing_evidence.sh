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
NONEXISTENT_CONTRACT="checkpoints/CP03/NONEXISTENT_CONTRACT.yaml"

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

set +e
scripts/workflow/verify-handoff "$HANDOFF" "$NONEXISTENT_CONTRACT" > /dev/null 2>&1
EXIT_CODE=$?
set -e

rm -f "$HANDOFF"

if [ "$EXIT_CODE" -eq 1 ]; then
  echo "PASS: test_13_missing_evidence"
  exit 0
else
  echo "FAIL: test_13_missing_evidence — expected exit 1, got $EXIT_CODE"
  exit 1
fi
