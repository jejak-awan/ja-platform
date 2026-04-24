# Security Permission Migration (Least Privilege)

## Tujuan
Memisahkan akses keamanan kritikal dari permission umum `manage settings`.

## Permission Baru
- `manage security operations`
- `manage security logs`
- `manage security ip-lists`
- `manage security integrity`
- `manage security maintenance`

## Status Rute
- **Sudah strict (tanpa fallback `manage settings`)**:
  - Domain logs/reporting (`journal`, `alerts`, `health`, CSP reports, slow queries)
  - Domain IP list (`/admin/core/security/block-*`, whitelist, bulk ops)
  - Domain integrity (`/admin/core/security/file-integrity*`, dependency audit, shield logs)
  - Domain maintenance (`/admin/core/security/maintenance*`, security settings update)

## Langkah Rollout
1. Jalankan seeder foundation terbaru.
2. Assign role `security-officer` ke operator SOC/IT.
3. Verifikasi akses endpoint security via akun `security-officer`.
4. Monitor 403 spike selama 24 jam.
5. Monitor 403 spike selama 24-48 jam, lalu evaluasi fine-tuning per role.

## Auto-Assign Security Officer
- Seeder membaca env `SECURITY_OFFICER_EMAILS` (CSV email), lalu assign role `security-officer` otomatis.
- Contoh:
  - `SECURITY_OFFICER_EMAILS=secops@smkn1cijulang.sch.id,it.lead@smkn1cijulang.sch.id`
- Alternatif tanpa reseed full:
  - `php artisan security:assign-officer --emails="secops@smkn1cijulang.sch.id,it.lead@smkn1cijulang.sch.id"`

## Smoke Check Pasca Deploy
- Jalankan:
  - `php artisan security:smoke-check`
- Check ini memvalidasi:
  - Semua permission granular security tersedia di database.
  - Semua route `api/v1/admin/core/security/*` tetap memakai guard permission.
  - Tidak ada fallback lama `manage settings` pada domain security strict.
  - Domain route (logs/ip-lists/integrity/maintenance) masih sesuai permission matrix.

## Recovery Drill Terukur (RTO/RPO)
- Jalankan:
  - `php artisan security:recovery-drill --create-backup --max-rto-seconds=600 --max-rpo-minutes=1440`
- Output:
  - Laporan JSON disimpan di `storage/app/security/recovery-drills/*.json`
  - Berisi nilai RTO/RPO aktual + pass/fail terhadap threshold.

## KPI Bulanan Security
- Jalankan:
  - `composer security:kpi`
- Command ini membaca:
  - histori recovery drill (`storage/app/security/recovery-drills/*.json`)
  - signal log security (`permission_denied`, `login_failed`, `ip_blocked*`, `login_blocked`)
- Output:
  - report KPI ke `storage/app/security/kpi/*.json`
  - metrik utama: drill pass rate, rata-rata RTO/RPO, detection noise-rate.

## Tuning Threshold Alert Per Environment
- `SecurityAlertService` sekarang membaca threshold dengan urutan:
  - `setting` scoped env (`security_alert_*_<environment>`) -> setting global -> ENV (`SECURITY_ALERT_*`) -> default profil environment
- Contoh ENV override:
  - `SECURITY_ALERT_FAILED_LOGIN_THRESHOLD=8`
  - `SECURITY_ALERT_BLOCKED_IP_THRESHOLD=4`
  - `SECURITY_ALERT_SUSPICIOUS_IP_THRESHOLD=14`
  - `SECURITY_ALERT_WINDOW_MINUTES=90`

## PCOV Toggle Saat Coverage Test
- Di server LMS umumnya **pcov dimatikan** di `php.ini` agar **JIT Opcache** tetap aktif; itu normal.
- Tes harian (`composer test` / `php artisan test`) memakai **`phpunit.xml`** tanpa blok coverage, sehingga **tidak** memerlukan driver pcov/xdebug dan tidak memunculkan peringatan coverage.
- Coverage hanya saat diminta:
  - `composer test:coverage` memakai `scripts/test-with-pcov.sh` + **`phpunit.coverage.xml`** (pcov di-override lewat CLI, JIT dimatikan hanya untuk proses itu).
- Validasi/force off (opsional):
  - `composer pcov:off`

## Rollback Cepat
- Jika operator kehilangan akses kritikal:
  - Sementara assign `manage security operations`.
  - Atau rollback middleware route ke versi kompatibel sebelumnya.
