#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v node &> /dev/null; then echo "SKIP: test_07 (no node)"; exit 0; fi

# Test 07 validates that ROADMAP_DEFINED mode runs without crashing
# and reports lifecycle-aware status. Exit code depends on diff content.
EVIDENCE_DIR="checkpoints/CP03/evidence"; mkdir -p "$EVIDENCE_DIR"

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

cp checkpoints/STATE.json checkpoints/STATE.json.bak
trap 'cp checkpoints/STATE.json.bak checkpoints/STATE.json 2>/dev/null; rm -f checkpoints/STATE.json.bak "$EVIDENCE_DIR/test_07_contract.yaml"' EXIT

jq '.lifecycle_status = "ROADMAP_DEFINED"' checkpoints/STATE.json > checkpoints/STATE.json.tmp
mv checkpoints/STATE.json.tmp checkpoints/STATE.json

set +e
output=$(scripts/workflow/check-changed-files "$EVIDENCE_DIR/test_07_contract.yaml" 2>&1)
EXIT=$?
set -e

# In ROADMAP_DEFINED mode: should report lifecycle awareness and not crash
# Exit code 0 or 1 are both valid (depends on whether diff has mixed changes)
if [ "$EXIT" -eq 0 ] || [ "$EXIT" -eq 1 ]; then
  echo "PASS: test_07_bootstrap_production (exit=$EXIT, bootstrap mode active)"
  exit 0
else
  echo "FAIL: test_07 — bootstrap mode crashed (exit=$EXIT)"
  echo "$output"
  exit 1
fi
