#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v jq &> /dev/null; then
  echo "SKIP: test_06_forbidden_path — jq not available"
  exit 0
fi
if ! command -v node &> /dev/null; then
  echo "SKIP: test_06_forbidden_path — node not available"
  exit 0
fi

if ! git diff --quiet 2>/dev/null || ! git diff --cached --quiet 2>/dev/null; then
  echo "SKIP: test_06_forbidden_path — working tree has uncommitted changes"
  exit 0
fi

EVIDENCE_DIR="checkpoints/CP03/evidence"
mkdir -p "$EVIDENCE_DIR"

cp checkpoints/STATE.json checkpoints/STATE.json.bak
SAVED_HEAD=$(git rev-parse HEAD)

CONTRACT="$EVIDENCE_DIR/test_06_contract.yaml"
cat > "$CONTRACT" << 'EOF'
CHECKPOINT_ID: CHECKPOINT_3
TITLE: "Test Forbidden Path"
LIFECYCLE_STATUS: CONTRACT_APPROVED
BASE_SHA: 039ff0d07ef1c5161a4bb4f459b9b008e5cab0dd
CANDIDATE_SHA: null
REVIEWED_SHA: null
SCOPE:
  TARGET_ROUTE: Homepage
  TARGET_VIEWPORT: 1366x768
  ACCEPTANCE_GATE: "test"
ALLOWED_PATHS:
  - checkpoints/**
FORBIDDEN_PATHS:
  - checkpoints/CP03/evidence/**
ACCEPTANCE_CRITERIA:
  - "Test criterion"
REQUIRED_TESTS:
  - command: "echo hello"
    expected_exit_code: 0
REQUIRED_EVIDENCE:
  - "Test evidence"
EOF

jq '.lifecycle_status = "CONTRACT_APPROVED"' checkpoints/STATE.json > checkpoints/STATE.json.tmp && mv checkpoints/STATE.json.tmp checkpoints/STATE.json

VIOLATION_FILE="checkpoints/CP03/evidence/test_06_forbidden.txt"
echo "test forbidden violation" > "$VIOLATION_FILE"
git add "$VIOLATION_FILE" > /dev/null 2>&1
git commit -m "test: temporary commit for test_06" > /dev/null 2>&1

set +e
scripts/workflow/check-changed-files "$CONTRACT" > /dev/null 2>&1
EXIT_CODE=$?
set -e

git reset --hard "$SAVED_HEAD" > /dev/null 2>&1
cp checkpoints/STATE.json.bak checkpoints/STATE.json
rm -f checkpoints/STATE.json.bak checkpoints/STATE.json.tmp "$CONTRACT"

if [ "$EXIT_CODE" -eq 1 ]; then
  echo "PASS: test_06_forbidden_path"
  exit 0
else
  echo "FAIL: test_06_forbidden_path — expected exit 1, got $EXIT_CODE"
  exit 1
fi
