#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

# ── Dependency checks ─────────────────────────────────────────────────────────
MISSING=0
for tool in node jq git; do
  if ! command -v "$tool" &>/dev/null; then
    echo "FAIL: test_31 requires $tool"
    MISSING=1
  fi
done
[ "$MISSING" -eq 1 ] && exit 1

# Source files needed in temp repo
CHECK_CHANGED="$REPO_ROOT/scripts/workflow/check-changed-files"
PATH_POLICY="$REPO_ROOT/scripts/workflow/lib/path-policy.js"
STATE_JSON="$REPO_ROOT/checkpoints/STATE.json"
TASK_CONTRACT="$REPO_ROOT/checkpoints/CP03/TASK_CONTRACT.yaml"

for f in "$CHECK_CHANGED" "$PATH_POLICY" "$STATE_JSON" "$TASK_CONTRACT"; do
  if [ ! -f "$f" ]; then echo "FAIL: test_31 — missing source file: $f"; exit 1; fi
done

# ── Create isolated temp repo ─────────────────────────────────────────────────
TMPROOT=$(mktemp -d)
trap 'rm -rf "$TMPROOT"' EXIT

echo "=== Creating isolated temp repository ==="
cd "$TMPROOT"
git init -q
git config user.email "test31@ci.local"
git config user.name "Test 31 CI"

# Seed necessary files into the temp repo
mkdir -p scripts/workflow/lib checkpoints/CP03
cp "$CHECK_CHANGED" scripts/workflow/check-changed-files
cp "$PATH_POLICY" scripts/workflow/lib/path-policy.js
cp "$STATE_JSON" checkpoints/STATE.json
cp "$TASK_CONTRACT" checkpoints/CP03/TASK_CONTRACT.yaml

# Force lifecycle to ROADMAP_DEFINED
jq '.lifecycle_status = "ROADMAP_DEFINED"' checkpoints/STATE.json > checkpoints/STATE.json.tmp
mv checkpoints/STATE.json.tmp checkpoints/STATE.json

# Add and create baseline commit on initial branch, then rename to feature/hieu-news
git add -A
git commit -q -m "baseline"
# Rename current branch to feature/hieu-news
git branch -m feature/hieu-news
BASELINE_SHA=$(git rev-parse HEAD)

# Create fix/workflow-qa-v3-post-merge branch from feature/hieu-news
git branch fix/workflow-qa-v3-post-merge feature/hieu-news

echo "Baseline SHA: $BASELINE_SHA"
echo "Lifecycle: $(jq -r .lifecycle_status checkpoints/STATE.json)"

FAILURES=0

# ── Helper: run one scenario ──────────────────────────────────────────────────
# $1 = type (ACCEPT/REJECT), $2 = scenario name, $3 = file path to create
run_scenario() {
  local type="$1" name="$2" filepath="$3"

  # Reset: recreate fix/workflow-qa-v3-post-merge from feature/hieu-news baseline
  git checkout -q feature/hieu-news 2>/dev/null
  git checkout -q -B fix/workflow-qa-v3-post-merge feature/hieu-news
  git clean -fdq 2>/dev/null || true

  # Create the scenario file and commit it
  mkdir -p "$(dirname "$filepath")"
  echo "// test scenario $name" > "$filepath"
  git add "$filepath" 2>/dev/null || true
  git commit -q -m "add $filepath" 2>/dev/null || true

  # Run check-changed-files against baseline on this branch
  local exit_code=0
  local output=""
  set +e
  output=$(bash "$CHECK_CHANGED" checkpoints/CP03/TASK_CONTRACT.yaml "$BASELINE_SHA" 2>&1)
  exit_code=$?
  set -e

  if [ "$type" = "ACCEPT" ]; then
    # Must PASS (exit 0, no violation, no FAIL)
    if [ "$exit_code" -ne 0 ]; then
      echo "FAIL: $name — expected exit 0, got $exit_code"
      echo "  output: $(echo "$output" | tail -3)"
      FAILURES=$((FAILURES+1))
      return
    fi
    if echo "$output" | grep -q "VIOLATION:"; then
      echo "FAIL: $name — unexpected VIOLATION"
      echo "  output: $(echo "$output" | tail -3)"
      FAILURES=$((FAILURES+1))
      return
    fi
    if echo "$output" | grep -q "FAIL:"; then
      echo "FAIL: $name — unexpected FAIL in output"
      echo "  output: $(echo "$output" | tail -3)"
      FAILURES=$((FAILURES+1))
      return
    fi
    if ! echo "$output" | grep -q "bootstrap allowlist"; then
      echo "FAIL: $name — bootstrap mode not detected"
      FAILURES=$((FAILURES+1))
      return
    fi
    if ! echo "$output" | grep -q "$filepath"; then
      echo "FAIL: $name — path '$filepath' not found in output (possible false positive)"
      FAILURES=$((FAILURES+1))
      return
    fi
    echo "PASS: $name"
  else
    # Must FAIL (exit != 0, VIOLATION present, FAIL: Bootstrap present)
    if [ "$exit_code" -eq 0 ]; then
      echo "FAIL: $name — expected non-zero exit, got 0"
      echo "  output: $(echo "$output" | tail -3)"
      FAILURES=$((FAILURES+1))
      return
    fi
    if ! echo "$output" | grep -q "VIOLATION:.*$filepath"; then
      echo "FAIL: $name — expected VIOLATION for '$filepath'"
      echo "  output: $(echo "$output" | tail -3)"
      FAILURES=$((FAILURES+1))
      return
    fi
    if ! echo "$output" | grep -q "FAIL: Bootstrap allowlist check failed"; then
      echo "FAIL: $name — expected 'FAIL: Bootstrap allowlist check failed'"
      FAILURES=$((FAILURES+1))
      return
    fi
    echo "PASS: $name"
  fi
}

# ── ACCEPT scenarios ──────────────────────────────────────────────────────────
echo ""
echo "=== ACCEPT scenarios ==="
run_scenario ACCEPT "ACCEPT_STYLE_CSS" "public/assets/css/style.css"
run_scenario ACCEPT "ACCEPT_MAIN_JS" "public/assets/js/main.js"
run_scenario ACCEPT "ACCEPT_BROWSER_SPEC" "tests/browser/home_first_fold.spec.js"

# ── REJECT scenarios ──────────────────────────────────────────────────────────
echo ""
echo "=== REJECT scenarios ==="
run_scenario REJECT "REJECT_NEWS_CSS" "public/assets/css/news.css"
run_scenario REJECT "REJECT_OTHER_CSS" "public/assets/css/other.css"
run_scenario REJECT "REJECT_NEWS_JS" "public/assets/js/news.js"
run_scenario REJECT "REJECT_OTHER_JS" "public/assets/js/other.js"
run_scenario REJECT "REJECT_HOME_INDEX" "app/views/home/index.php"

# ── Report ────────────────────────────────────────────────────────────────────
echo ""
echo "=== Test 31 Summary ==="
SCENARIOS_TOTAL=8
SCENARIOS_PASSED=$((SCENARIOS_TOTAL - FAILURES))
echo "TEST_31_SCENARIOS_TOTAL=$SCENARIOS_TOTAL"
echo "TEST_31_SCENARIOS_PASSED=$SCENARIOS_PASSED"
echo "TEST_31_SCENARIOS_FAILED=$FAILURES"

if [ "$FAILURES" -eq 0 ]; then
  echo "PASS: test_31_bootstrap_exact_production_paths"
  exit 0
else
  echo "FAIL: test_31_bootstrap_exact_production_paths — $FAILURES scenario(s) failed"
  exit 1
fi
