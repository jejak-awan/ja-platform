#!/usr/bin/env bash

set -euo pipefail

PHP_BIN="${PHP_BIN:-php}"

echo "[pcov] Disable check untuk proses CLI saat ini."
"$PHP_BIN" -d pcov.enabled=0 -r 'echo "pcov.enabled=0\n";'
echo "[pcov] Catatan: wrapper test-with-pcov.sh tidak mengubah config global, jadi setelah proses test selesai PCOV otomatis nonaktif kembali."
