#!/bin/bash
set -euo pipefail

URL="http://localhost/api/proxy/import-orders/5"
LOG="/var/log/import_orders_loop.log"

while true; do
  curl -s -X GET "$URL" >> "$LOG" 2>&1 || true
  sleep 5
done
