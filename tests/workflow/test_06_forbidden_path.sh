#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v node &> /dev/null; then echo "SKIP: test_06 (no node)"; exit 0; fi

EVIDENCE_DIR="checkpoints/CP03/evidence"; mkdir -p "$EVIDENCE_DIR"
cp checkpoints/STATE.json checkpoints/STATE.json.bak
trap 'cp checkpoints/STATE.json.bak checkpoints/STATE.json 2>/dev/null; rm -f "$EVIDENCE_DIR/test_06_contract.yaml" checkpoints/STATE.json.bak' EXIT

# Create contract where checkpoints/ is forbidden
cat > "$EVIDENCE_DIR/test_06_contract.yaml" << 'EOF'
CHECKPOINT_ID: CHECKPOINT_3
TITLE: "Test"
LIFECYCLE_STATUS: CONTRACT_APPROVED
BASE_SHA: 039ff0d07ef1c5161a4bb4f459b9b008e5cab0dd
CANDIDATE_SHA: null
REVIEWED_SHA: null
SCOPE:
  TARGET_ROUTE: Homepage
  TARGET_VIEWPORT: 1366x768
  ACCEPTANCE_GATE: "test"
ALLOWED_PATHS:
  - scripts/**
  - checkpoints/CP03/**
FORBIDDEN_PATHS:
  - checkpoints/STATE.json
ACCEPTANCE_CRITERIA:
  - "Test"
REQUIRED_TESTS:
  - command: "echo ok"
    expected_exit_code: 0
REQUIRED_EVIDENCE:
  - "Test"
EOF

jq '.lifecycle_status = "CONTRACT_APPROVED"' checkpoints/STATE.json > checkpoints/STATE.json.tmp
mv checkpoints/STATE.json.tmp checkpoints/STATE.json

set +e
output=$(scripts/workflow/check-changed-files "$EVIDENCE_DIR/test_06_contract.yaml" 2>&1)
EXIT=$?
set -e

if [ "$EXIT" -eq 1 ]; then
  echo "PASS: test_06_forbidden_path"
  exit 0
else
  echo "FAIL: test_06_forbidden_path — expected exit 1, got $EXIT"
  echo "$output"
  exit 1
fi
