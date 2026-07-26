#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT"

if ! command -v node &>/dev/null; then echo "SKIP: test_21 (no node)"; exit 0; fi

EVIDENCE_DIR="checkpoints/CP03/evidence"
mkdir -p "$EVIDENCE_DIR"
CONTRACT="$EVIDENCE_DIR/test_21_malformed.yaml"
cat > "$CONTRACT" << 'EOF'
CHECKPOINT_ID: "unclosed quote
TITLE: "Bad YAML"
EOF

set +e
scripts/workflow/validate-contract "$CONTRACT" > /dev/null 2>&1
EXIT_CODE=$?
set -e
rm -f "$CONTRACT"
if [ "$EXIT_CODE" -eq 1 ]; then echo "PASS: test_21_malformed_yaml"; exit 0; else echo "FAIL: test_21_malformed_yaml — expected exit 1, got $EXIT_CODE"; exit 1; fi
