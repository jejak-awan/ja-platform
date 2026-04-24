#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

PHP_BIN="${PHP_BIN:-php}"
PCOV_DIR="${PCOV_DIRECTORY:-$ROOT_DIR}"

BASE_ARGS=(
  "-d" "pcov.enabled=1"
  "-d" "pcov.directory=${PCOV_DIR}"
  "-d" "opcache.jit=0"
)

if "$PHP_BIN" -r 'exit(extension_loaded("pcov") ? 0 : 1);' >/dev/null 2>&1; then
  EXTRA_ARGS=()
elif "$PHP_BIN" -d extension=pcov -r 'exit(extension_loaded("pcov") ? 0 : 1);' >/dev/null 2>&1; then
  EXTRA_ARGS=("-d" "extension=pcov")
else
  echo "[pcov] Extension pcov tidak tersedia. Install/aktifkan PCOV dulu." >&2
  exit 1
fi

echo "[pcov] Enable untuk sesi test ini (auto-disable setelah proses selesai)."

if [ "$#" -eq 0 ]; then
  exec "$PHP_BIN" "${EXTRA_ARGS[@]}" "${BASE_ARGS[@]}" ./vendor/bin/phpunit --coverage-text
else
  exec "$PHP_BIN" "${EXTRA_ARGS[@]}" "${BASE_ARGS[@]}" ./vendor/bin/phpunit "$@" --coverage-text
fi
