#!/usr/bin/env bash
# Build the Vue dashboard and sync whole dist content to Laravel public (including root files like logo.png, favicon.ico).
#
# Usage (from ja-apps/):
#   npm run deploy:assets              # full rebuild + rsync
#   SYNC_ONLY=1 npm run deploy:assets  # rsync only (dist/ must already exist)
#
# Requires: Node 20+, npm, rsync.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC_DIST="$ROOT/frontend/dist/"
DST_PUBLIC="$ROOT/backend/public/"

for bin in npm rsync; do
  if ! command -v "$bin" >/dev/null 2>&1; then
    echo "error: '$bin' not found in PATH" >&2
    exit 1
  fi
done

if [ "${SYNC_ONLY:-0}" != "1" ]; then
  echo "Full build and sync..."
  cd "$ROOT/frontend"
  npm run rebuild
else
  echo "SYNC_ONLY=1 — skipping npm run rebuild"
fi

if [ ! -d "$SRC_DIST" ]; then
  echo "error: missing $SRC_DIST (run without SYNC_ONLY first)" >&2
  exit 1
fi

mkdir -p "$DST_PUBLIC"
# Sync all contents of dist to backend/public, but PROTECT core Laravel entrance files
rsync -a --delete \
  --exclude='index.php' \
  --exclude='.htaccess' \
  --exclude='robots.txt' \
  --exclude='.well-known' \
  --exclude='storage' \
  "$SRC_DIST" "$DST_PUBLIC"

echo "OK: synced $SRC_DIST → $DST_PUBLIC"
echo "Tip: on the server, after pull: cd ja-apps && npm run deploy:assets"
echo "Tip: optional Laravel: cd ja-apps/backend && php artisan optimize:clear && php artisan view:cache"
