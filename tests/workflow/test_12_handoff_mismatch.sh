#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &> /dev/null; then
  echo "SKIP: test_12_handoff_mismatch — jq not available"
  exit 0
fi
if ! command -v node &> /dev/null; then
  echo "SKIP: test_12_handoff_mismatch — node not available"
  exit 0
fi

EVIDENCE_DIR="checkpoints/CP03/evidence"
mkdir -p "$EVIDENCE_DIR"

HEAD_SHA=$(git rev-parse HEAD)
BASE_SHA=$(jq -r '.base_sha' checkpoints/STATE.json)

HANDOFF="$EVIDENCE_DIR/test_12_handoff.json"

jq -n \
  --arg schema_version "1" \
  --arg checkpoint_id "CHECKPOINT_3" \
  --arg base_sha "$BASE_SHA" \
  --arg candidate_sha "$HEAD_SHA" \
  '{
    schema_version: $schema_version,
    checkpoint_id: $checkpoint_id,
    base_sha: $base_sha,
    candidate_sha: $candidate_sha,
    changed_files: ["nonexistent_file_should_not_match.txt"],
    test_results: [],
    writer_declaration: "READY_FOR_REVIEW"
  }' > "$HANDOFF"

set +e
scripts/workflow/verify-handoff "$HANDOFF" > /dev/null 2>&1
EXIT_CODE=$?
set -e

rm -f "$HANDOFF"

if [ "$EXIT_CODE" -eq 1 ]; then
  echo "PASS: test_12_handoff_mismatch"
  exit 0
else
  echo "FAIL: test_12_handoff_mismatch — expected exit 1, got $EXIT_CODE"
  exit 1
fi
