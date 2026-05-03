#!/usr/bin/env bash
# Build the Vue dashboard and sync hashed chunks to Laravel public/assets (typical smkn1cijulang-style deploy).
#
# Usage (from ja-apps/):
#   npm run deploy:assets              # full rebuild + rsync
#   SYNC_ONLY=1 npm run deploy:assets  # rsync only (dist/ must already exist)
#
# Requires: Node 20+, npm, rsync.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC="$ROOT/frontend/dist/assets/"
DST="$ROOT/backend/public/assets/"

for bin in npm rsync; do
  if ! command -v "$bin" >/dev/null 2>&1; then
    echo "error: '$bin' not found in PATH" >&2
    exit 1
  fi
done

if [ "${SYNC_ONLY:-0}" != "1" ]; then
  cd "$ROOT/frontend"
  npm run rebuild
else
  echo "SYNC_ONLY=1 — skipping npm run rebuild"
fi

if [ ! -d "$SRC" ]; then
  echo "error: missing $SRC (run without SYNC_ONLY first)" >&2
  exit 1
fi

mkdir -p "$DST"
rsync -a --delete "$SRC" "$DST"
cp "$ROOT/frontend/dist/index.html" "$ROOT/backend/public/index.html"

echo "OK: synced $SRC → $DST and index.html"
echo "Tip: on the server, after pull: cd ja-apps && npm run deploy:assets"
echo "Tip: optional Laravel: cd ja-apps/backend && php artisan optimize:clear && php artisan view:cache"
