#!/bin/bash
set -euo pipefail

URL="${IMPORT_LOOP_URL:-http://localhost:8000/api/proxy/import-orders/5}"
LOG="/var/log/import_orders_loop.log"
INTERVAL="${IMPORT_LOOP_INTERVAL:-3}"

if [[ "${ORDERS_FETCHING_DISABLED:-0}" == "1" ]]; then
  echo "Order fetching disabled; import loop skipped." >> "$LOG" 2>&1
  exit 0
fi

while true; do
  curl -s -X GET "$URL" >> "$LOG" 2>&1 || true
  sleep "$INTERVAL"
done
