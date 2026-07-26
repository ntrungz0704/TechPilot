#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$REPO_ROOT"

VERIFIER="scripts/workflow/verify-remediation-marker"

if [ ! -f "$VERIFIER" ]; then echo "FAIL: test_30 — verifier not found"; exit 1; fi

# ── Extract canonical paths from verifier heredoc ──────────────────────────────
# The CANONICAL_PATHS heredoc is between CANONICAL_PATHS and PATHEOF markers
CANONICAL_RAW=$(sed -n '/^CANONICAL_PATHS=\$(cat <<'\''PATHEOF'\''/,/^PATHEOF/p' "$VERIFIER" \
  | sed '1d;$d' \
  | grep -v '^[[:space:]]*$')

if [ -z "$CANONICAL_RAW" ]; then
  echo "FAIL: test_30 — could not extract canonical allowlist"
  exit 1
fi

echo "--- Canonical remediation allowlist ---"
echo "$CANONICAL_RAW"
echo "---"

FAILED=0

# ── MUST ACCEPT: exact path match in allowlist ─────────────────────────────────
for path in \
  "public/assets/css/style.css" \
  "public/assets/js/main.js" \
  "tests/browser/home_first_fold.spec.js"; do

  found=0
  while IFS= read -r entry; do
    [ -z "$entry" ] && continue

    case "$entry" in
      *\*\*)
        # Glob entry ending with /**
        prefix="${entry%\*\*}"
        if [ "${path#$prefix}" != "$path" ]; then
          found=1
          break
        fi
        ;;
      *)
        # Exact match
        if [ "$path" = "$entry" ]; then
          found=1
          break
        fi
        ;;
    esac
  done <<EOF
$CANONICAL_RAW
EOF

  if [ "$found" -eq 1 ]; then
    echo "PASS: ACCEPT '$path'"
  else
    echo "FAIL: REJECT '$path' (expected ACCEPT)"
    FAILED=$((FAILED + 1))
  fi
done

# ── MUST REJECT: not matched by any canonical entry ───────────────────────────
for path in \
  "public/assets/css/news.css" \
  "public/assets/css/other.css" \
  "public/assets/js/news.js" \
  "public/assets/js/other.js" \
  "app/views/home/index.php"; do

  found=0
  while IFS= read -r entry; do
    [ -z "$entry" ] && continue

    case "$entry" in
      *\*\*)
        prefix="${entry%\*\*}"
        if [ "${path#$prefix}" != "$path" ]; then
          found=1
          break
        fi
        ;;
      *)
        if [ "$path" = "$entry" ]; then
          found=1
          break
        fi
        ;;
    esac
  done <<EOF
$CANONICAL_RAW
EOF

  if [ "$found" -eq 1 ]; then
    echo "FAIL: ACCEPT '$path' (expected REJECT)"
    FAILED=$((FAILED + 1))
  else
    echo "PASS: REJECT '$path'"
  fi
done

# ── REPORT ────────────────────────────────────────────────────────────────────
if [ "$FAILED" -eq 0 ]; then
  echo "PASS: test_30_canonical_allowlist"
  exit 0
else
  echo "FAIL: test_30_canonical_allowlist — $FAILED assertion(s) failed"
  exit 1
fi
