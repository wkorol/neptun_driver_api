#!/bin/bash
set -euo pipefail

URL="${IMPORT_LOOP_URL:-http://localhost:8000/api/proxy/import-orders/5}"
LOG="/var/log/import_orders_loop.log"
INTERVAL="${IMPORT_LOOP_INTERVAL:-5}"

while true; do
  curl -s -X GET "$URL" >> "$LOG" 2>&1 || true
  sleep "$INTERVAL"
done
