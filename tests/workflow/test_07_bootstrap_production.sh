#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &> /dev/null; then
  echo "SKIP: test_07_bootstrap_production — jq not available"
  exit 0
fi
if ! command -v node &> /dev/null; then
  echo "SKIP: test_07_bootstrap_production — node not available"
  exit 0
fi

if ! git diff --quiet 2>/dev/null || ! git diff --cached --quiet 2>/dev/null; then
  echo "SKIP: test_07_bootstrap_production — working tree has uncommitted changes"
  exit 0
fi

cp checkpoints/STATE.json checkpoints/STATE.json.bak
SAVED_HEAD=$(git rev-parse HEAD)

jq '.lifecycle_status = "ROADMAP_DEFINED"' checkpoints/STATE.json > checkpoints/STATE.json.tmp && mv checkpoints/STATE.json.tmp checkpoints/STATE.json

mkdir -p app
VIOLATION_FILE="app/test_07_app_violation.txt"
echo "app violation" > "$VIOLATION_FILE"

WORKFLOW_FILE="scripts/workflow/test_07_allowed.txt"
echo "workflow file" > "$WORKFLOW_FILE"

git add "$VIOLATION_FILE" "$WORKFLOW_FILE" > /dev/null 2>&1
git commit -m "test: temporary commit for test_07" > /dev/null 2>&1

set +e
scripts/workflow/check-changed-files > /dev/null 2>&1
EXIT_CODE=$?
set -e

git reset --hard "$SAVED_HEAD" > /dev/null 2>&1
cp checkpoints/STATE.json.bak checkpoints/STATE.json
rm -f checkpoints/STATE.json.bak checkpoints/STATE.json.tmp

if [ "$EXIT_CODE" -eq 1 ]; then
  echo "PASS: test_07_bootstrap_production"
  exit 0
else
  echo "FAIL: test_07_bootstrap_production — expected exit 1, got $EXIT_CODE"
  exit 1
fi
