#!/usr/bin/env bash
set -euo pipefail
ROOT=$(cd "$(dirname "$0")/.." && pwd)
PHP_CLI=$(command -v php)

# Start built-in server
$PHP_CLI -S 127.0.0.1:8081 -t "$ROOT/public" "$ROOT/public/index.php" >/tmp/php-todo.log 2>&1 &
PID=$!
trap 'kill $PID || true' EXIT
sleep 0.5

# Create
curl -s -X POST http://127.0.0.1:8081/api/todos -H 'content-type: application/json' -d '{"title":"Test item"}' | jq .
# List
curl -s http://127.0.0.1:8081/api/todos | jq 'length'
