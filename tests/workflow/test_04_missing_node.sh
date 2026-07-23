#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if [ -n "${CI:-}" ]; then
  echo "SKIP: test_04_missing_node — CI environment (node is always present)"
  exit 0
fi

NODE_PATH=$(command -v node 2>/dev/null || true)
if [ -z "$NODE_PATH" ]; then
  echo "SKIP: test_04_missing_node — node not found on this system"
  exit 0
fi
NODE_DIR=$(dirname "$NODE_PATH")

EVIDENCE_DIR="checkpoints/CP03/evidence"
mkdir -p "$EVIDENCE_DIR"

CONTRACT="$EVIDENCE_DIR/test_04_contract.yaml"

cat > "$CONTRACT" << 'EOF'
CHECKPOINT_ID: CHECKPOINT_3
TITLE: "Test Missing Node"
LIFECYCLE_STATUS: ROADMAP_DEFINED
BASE_SHA: 039ff0d07ef1c5161a4bb4f459b9b008e5cab0dd
CANDIDATE_SHA: null
REVIEWED_SHA: null
SCOPE:
  TARGET_ROUTE: Homepage
  TARGET_VIEWPORT: 1366x768
  ACCEPTANCE_GATE: "test"
ALLOWED_PATHS:
  - tests/
FORBIDDEN_PATHS:
  - _none
ACCEPTANCE_CRITERIA:
  - "Test criterion"
REQUIRED_TESTS:
  - command: "echo hello"
    expected_exit_code: 0
REQUIRED_EVIDENCE:
  - "Test evidence"
EOF

OLD_PATH="$PATH"
PATH=$(echo ":$PATH:" | sed "s|:$NODE_DIR:|:|g" | sed 's/^://' | sed 's/:$//')

if command -v node &> /dev/null; then
  echo "SKIP: test_04_missing_node — could not remove node from PATH"
  PATH="$OLD_PATH"
  rm -f "$CONTRACT"
  exit 0
fi

set +e
scripts/workflow/validate-contract "$CONTRACT" > /dev/null 2>&1
EXIT_CODE=$?
set -e

PATH="$OLD_PATH"

if [ "$EXIT_CODE" -eq 1 ]; then
  echo "PASS: test_04_missing_node"
  exit 0
else
  echo "FAIL: test_04_missing_node — expected exit 1, got $EXIT_CODE"
  exit 1
fi
