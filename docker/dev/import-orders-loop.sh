#!/bin/bash
set -euo pipefail

PUBLIC_TOKEN="${ORDER_LIST_PUBLIC_TOKEN:-3f9b0e1b-1616-4be0-962b-aa63409d4650}"
HOW_MANY="${IMPORT_LOOP_HOW_MANY:-25}"
BATCH_SIZE="${IMPORT_LOOP_BATCH_SIZE:-25}"
CONCURRENCY="${IMPORT_LOOP_CONCURRENCY:-10}"
URL="${IMPORT_LOOP_URL:-http://127.0.0.1/api/proxy/import-orders/${HOW_MANY}?publicToken=${PUBLIC_TOKEN}&batchSize=${BATCH_SIZE}&concurrency=${CONCURRENCY}}"
LOG="/var/log/import_orders_loop.log"
INTERVAL="${IMPORT_LOOP_INTERVAL:-5}"
TMP_BODY="/tmp/import_orders_loop_response.txt"

if [[ "${ORDERS_FETCHING_DISABLED:-0}" == "1" ]]; then
  echo "$(date -u '+%Y-%m-%dT%H:%M:%SZ') SKIP orders fetching disabled" >> "$LOG" 2>&1
  exit 0
fi

while true; do
  http_code="$(curl -sS --max-time 30 -o "$TMP_BODY" -w '%{http_code}' -X GET "$URL" || echo '000')"
  body="$(tr -d '\n' < "$TMP_BODY" 2>/dev/null || true)"

  if [[ "$http_code" =~ ^2[0-9][0-9]$ ]]; then
    echo "$(date -u '+%Y-%m-%dT%H:%M:%SZ') OK [$http_code] ${body}" >> "$LOG"
  else
    echo "$(date -u '+%Y-%m-%dT%H:%M:%SZ') ERROR [$http_code] url=${URL} body=${body}" >> "$LOG" 2>&1
  fi

  sleep "$INTERVAL"
done
