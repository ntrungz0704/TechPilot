#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &> /dev/null; then
  echo "SKIP: test_08_earlier_pr_violation — jq not available"
  exit 0
fi
if ! command -v node &> /dev/null; then
  echo "SKIP: test_08_earlier_pr_violation — node not available"
  exit 0
fi

cp checkpoints/STATE.json checkpoints/STATE.json.bak

ROOT_COMMIT=$(git rev-list --max-parents=0 HEAD 2>/dev/null || true)
if [ -z "$ROOT_COMMIT" ]; then
  echo "SKIP: test_08_earlier_pr_violation — could not find root commit"
  cp checkpoints/STATE.json.bak checkpoints/STATE.json
  rm -f checkpoints/STATE.json.bak
  exit 0
fi

jq --arg sha "$ROOT_COMMIT" '.base_sha = $sha | .lifecycle_status = "ROADMAP_DEFINED"' checkpoints/STATE.json > checkpoints/STATE.json.tmp && mv checkpoints/STATE.json.tmp checkpoints/STATE.json

set +e
scripts/workflow/check-changed-files > /dev/null 2>&1
EXIT_CODE=$?
set -e

cp checkpoints/STATE.json.bak checkpoints/STATE.json
rm -f checkpoints/STATE.json.bak checkpoints/STATE.json.tmp

if [ "$EXIT_CODE" -eq 1 ]; then
  echo "PASS: test_08_earlier_pr_violation"
  exit 0
else
  echo "FAIL: test_08_earlier_pr_violation — expected exit 1, got $EXIT_CODE"
  exit 1
fi
