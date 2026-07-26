#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &> /dev/null; then
  echo "SKIP: test_15_null_reviewed_sha — jq not available"
  exit 0
fi

cp checkpoints/STATE.json checkpoints/STATE.json.bak

jq '.lifecycle_status = "HERMES_VERIFIED" | .reviewed_sha = null | .candidate_sha = "0000000000000000000000000000000000000000"' checkpoints/STATE.json > checkpoints/STATE.json.tmp && mv checkpoints/STATE.json.tmp checkpoints/STATE.json

set +e
scripts/workflow/verify-review-sha > /dev/null 2>&1
EXIT_CODE=$?
set -e

cp checkpoints/STATE.json.bak checkpoints/STATE.json
rm -f checkpoints/STATE.json.bak checkpoints/STATE.json.tmp

if [ "$EXIT_CODE" -eq 1 ]; then
  echo "PASS: test_15_null_reviewed_sha"
  exit 0
else
  echo "FAIL: test_15_null_reviewed_sha — expected exit 1, got $EXIT_CODE"
  exit 1
fi
