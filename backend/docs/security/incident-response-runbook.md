# Incident Response Runbook (SMKN1 Cijulang)

## 1) Tujuan
Panduan respon insiden untuk menjaga SLA layanan, membatasi dampak, dan memastikan pemulihan cepat.

## 2) Severity
- **SEV-1**: layanan inti tidak tersedia / data sensitif terpapar.
- **SEV-2**: gangguan fitur penting, indikasi serangan aktif terbatas.
- **SEV-3**: anomali non-kritis, probing, atau false positive.

## 3) Trigger Deteksi
- Lonjakan `login_failed`, `permission_denied`, `ip_blocked_*`.
- Spike 403/429/5xx di API.
- Alert `permission_probe` / brute force / multiple blocked IP.

## 4) Alur Respon (0-30 Menit)
1. **Triage (0-5m)**
   - Validasi alert: endpoint terdampak, IP/user, waktu, scope.
   - Tandai SEV level dan assign Incident Commander.
2. **Containment (5-15m)**
   - Block IP/ASN terkait via Security blocklist.
   - Revoke session akun terindikasi (force logout).
   - Aktifkan maintenance terbatas jika dampak meluas.
3. **Eradication (10-25m)**
   - Patch rule/rate-limit yang dieksploitasi.
   - Rotasi credential berisiko (API key, webhook secret, token).
4. **Recovery (20-30m)**
   - Validasi endpoint sehat, error rate normal.
   - Nonaktifkan containment sementara (jika aman).

## 5) Checklist Teknis
- [ ] Export bukti: request samples, security logs, auth logs.
- [ ] Snapshot konfigurasi security sebelum/after.
- [ ] Jalankan integrity check + dependency audit bila relevan.
- [ ] Verifikasi backup restore point terbaru tersedia.
- [ ] Jalankan `php artisan security:recovery-drill --max-rto-seconds=600 --max-rpo-minutes=1440` dan arsipkan report JSON.

## 6) Komunikasi
- Internal update tiap 15 menit untuk SEV-1/2.
- Simpan timeline: deteksi, aksi, hasil, PIC.
- Setelah normal, kirim ringkasan insiden + dampak + mitigasi.

## 7) Postmortem (Maks 24 Jam)
- Root cause, blast radius, data affected.
- Apa yang gagal terdeteksi dini.
- Action item terukur (owner + due date):
  - hardening policy,
  - alert tuning,
  - test coverage,
  - runbook update.
