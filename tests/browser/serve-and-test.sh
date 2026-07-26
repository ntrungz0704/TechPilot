#!/usr/bin/env bash
set -euo pipefail

# ── CP03 Browser Geometry Test Server Wrapper ──────────────────────────────
# Sole server lifecycle owner. home_first_fold.spec.js MUST NOT start its own.
# ────────────────────────────────────────────────────────────────────────────

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$REPO_ROOT"

PORT="${PORT:-8000}"
TEST_SCRIPT="tests/browser/home_first_fold.spec.js"
SERVER_PID=""
TEST_EXIT=""

cleanup() {
  if [ -n "$SERVER_PID" ]; then
    kill "$SERVER_PID" 2>/dev/null || true
    wait "$SERVER_PID" 2>/dev/null || true
  fi
  rm -f "/tmp/php-server-$$.log" "/tmp/php-server-$$.pid"
}
trap cleanup EXIT ERR SIGTERM SIGINT

# Check prerequisites
if ! command -v php &>/dev/null; then
  echo "FAIL: php is required"
  exit 1
fi
if ! command -v node &>/dev/null; then
  echo "FAIL: node is required"
  exit 1
fi
if [ ! -f "$TEST_SCRIPT" ]; then
  echo "FAIL: Test script not found: $TEST_SCRIPT"
  exit 1
fi

# Start PHP development server
php -S "0.0.0.0:$PORT" -t public/ > "/tmp/php-server-$$.log" 2>&1 &
SERVER_PID=$!
echo "$SERVER_PID" > "/tmp/php-server-$$.pid"
echo "OK: PHP server started (PID=$SERVER_PID, port=$PORT)"

# Readiness probe
STARTUP_TIMEOUT=15
for i in $(seq 1 "$STARTUP_TIMEOUT"); do
  if curl -s -o /dev/null -w "%{http_code}" "http://localhost:$PORT/" 2>/dev/null | grep -q '200\|302\|301'; then
    echo "OK: Server ready on attempt $i"
    break
  fi
  if [ "$i" -eq "$STARTUP_TIMEOUT" ]; then
    echo "FAIL: Server did not start within $STARTUP_TIMEOUT seconds"
    echo "--- Last 20 lines of server log ---"
    tail -20 "/tmp/php-server-$$.log" 2>/dev/null || true
    exit 1
  fi
  sleep 1
done

# Run test
export TEST_URL="http://localhost:$PORT"
echo "OK: TEST_URL=$TEST_URL"
echo "--- Running $TEST_SCRIPT ---"

set +e
node "$TEST_SCRIPT"
TEST_EXIT=$?
set -e

if [ "$TEST_EXIT" -eq 0 ]; then
  echo "PASS: serve-and-test.sh"
else
  echo "FAIL: serve-and-test.sh (test exit code: $TEST_EXIT)"
fi

exit "$TEST_EXIT"
