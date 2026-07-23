#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v node &> /dev/null; then echo "SKIP: test_07 (no node)"; exit 0; fi

EVIDENCE_DIR="checkpoints/CP03/evidence"; mkdir -p "$EVIDENCE_DIR"
cp checkpoints/STATE.json checkpoints/STATE.json.bak
trap 'cp checkpoints/STATE.json.bak checkpoints/STATE.json 2>/dev/null; rm -f "$EVIDENCE_DIR/test_07_contract.yaml" checkpoints/STATE.json.bak' EXIT

cat > "$EVIDENCE_DIR/test_07_contract.yaml" << 'EOF'
CHECKPOINT_ID: CHECKPOINT_3
TITLE: "Test"
LIFECYCLE_STATUS: ROADMAP_DEFINED
BASE_SHA: 039ff0d07ef1c5161a4bb4f459b9b008e5cab0dd
CANDIDATE_SHA: null
REVIEWED_SHA: null
SCOPE:
  TARGET_ROUTE: Homepage
  TARGET_VIEWPORT: 1366x768
  ACCEPTANCE_GATE: "test"
ALLOWED_PATHS:
  - checkpoints/**
  - scripts/**
FORBIDDEN_PATHS: []
ACCEPTANCE_CRITERIA:
  - "Test"
REQUIRED_TESTS:
  - command: "echo ok"
    expected_exit_code: 0
REQUIRED_EVIDENCE:
  - "Test"
EOF

jq '.lifecycle_status = "ROADMAP_DEFINED"' checkpoints/STATE.json > checkpoints/STATE.json.tmp
mv checkpoints/STATE.json.tmp checkpoints/STATE.json

# Detect if the diff has production code mixed with workflow files
HEAD_SHA=$(git rev-parse HEAD)
HAS_PROD=false
WF_FILES=$(git diff --name-only 039ff0d07ef1c5161a4bb4f459b9b008e5cab0dd..$HEAD_SHA 2>/dev/null || true)

if [ -z "$WF_FILES" ]; then
  HAS_NON_WF=false
else
  for f in $WF_FILES; do
    if [[ ! "$f" =~ ^(checkpoints/|docs/workflow/|scripts/workflow/|\.opencode/|AGENTS\.md|\.github/workflows/|tests/workflow/|tests/browser/) ]]; then
      HAS_PROD=true; break
    fi
  done
fi

set +e
output=$(scripts/workflow/check-changed-files "$EVIDENCE_DIR/test_07_contract.yaml" 2>&1)
EXIT=$?
set -e

if [ "$HAS_PROD" = true ]; then
  # Mixed changes detected — bootstrap should FAIL
  if [ "$EXIT" -eq 1 ]; then
    echo "PASS: test_07_bootstrap_production (correctly detected mixed changes)"
    exit 0
  else
    echo "FAIL: test_07 — mixed changes exist but bootstrap exited $EXIT (expected 1)"
    echo "$output"
    exit 1
  fi
else
  # Only workflow files — bootstrap should PASS
  if [ "$EXIT" -eq 0 ]; then
    echo "PASS: test_07_bootstrap_production"
    exit 0
  else
    echo "FAIL: test_07 — workflow-only diff but bootstrap exited $EXIT (expected 0)"
    echo "$output"
    exit 1
  fi
fi
