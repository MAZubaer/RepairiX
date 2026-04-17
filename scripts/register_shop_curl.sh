#!/usr/bin/env bash
BASE=${1:-http://127.0.0.1:8000}
mkdir -p ./scripts

echo "GET $BASE/register/shop"
curl -sS -D ./scripts/get_headers.txt -c ./scripts/cookies.txt -o ./scripts/last_get.html "$BASE/register/shop"

XSRF_ENC=$(awk '/XSRF-TOKEN/ {print $7; exit}' ./scripts/cookies.txt || true)
if [ -z "$XSRF_ENC" ]; then
  echo "XSRF cookie not found in cookies.txt"
  exit 1
fi

# decode URL-encoded cookie value (requires python)
XSRF=$(printf "%s" "$XSRF_ENC" | python -c "import sys,urllib.parse;print(urllib.parse.unquote(sys.stdin.read().strip()))")
echo "DECODED_XSRF:$XSRF"

RAND=$RANDOM
echo "POST $BASE/register/shop"
curl -sS -D ./scripts/post_headers.txt -b ./scripts/cookies.txt \
  -H "X-XSRF-TOKEN: $XSRF" \
  -H "X-Requested-With: XMLHttpRequest" \
  -H "X-Inertia: true" \
  -X POST \
  -F "shop_name=CurlShop" \
  -F "email=curl${RAND}@example.com" \
  -F "phone=01811${RAND}" \
  -F "shop_address=Addr" \
  -F "password=password123" \
  -F "password_confirmation=password123" \
  -o ./scripts/last_post.html "$BASE/register/shop"

echo DONE
