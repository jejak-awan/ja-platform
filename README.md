# JA-Platform - Inti Aplikasi

**JA-Platform** adalah ekosistem digital modular yang dirancang untuk efisiensi tinggi, estetika premium, dan skalabilitas *enterprise*. Platform ini menggabungkan fleksibilitas backend Laravel, kecepatan frontend Vue 3, dan keandalan PostgreSQL.

## 🏢 Identitas & Pengembang
- **Nama Proyek**: JA-Platform
- **Versi**: 1.0.0
- **Pengembang**: [Jejakawan](https://jejakawan.com) (@jejakawan007)
- **Web**: [jejakawan.com](https://jejakawan.com)
- **Perusahaan**: [K2NET](https://k2net.id)
- **Web Perusahaan**: [k2net.id](https://k2net.id)
- **Status**: Active Development

## 🚀 Stack Teknologi Inti
- **Backend**: [Laravel 12](https://laravel.com) (PHP 8.2+) - Arsitektur Modular.
- **Frontend**: [Vue 3](https://vuejs.org) + [Vite 7](https://vitejs.dev) + [TypeScript](https://www.typescriptlang.org) + [Tailwind CSS 4](https://tailwindcss.com).
- **Database**: **PostgreSQL** (Primary) & Redis (Caching/Queue).
- **Animasi**: [GSAP](https://gsap.com) (GreenSock Animation Platform) - High-end UI Effects.

## 📂 Struktur Aplikasi

- **[`/backend`](./backend)**: API Service berbasis Laravel 12.
- **[`/frontend`](./frontend)**: Client application berbasis Vue 3 & Vite 7.
- **[`/scripts`](./scripts)**: Script lintas-aplikasi (orchestration root monorepo).

### Konvensi Folder Script

- **`/scripts` (root)**: script yang menyentuh lebih dari satu app (contoh sinkronisasi `frontend/dist` ke `backend/public`).
- **`/frontend/scripts`**: script khusus frontend (build tooling, perf budget, dll).
- **`/backend/scripts`**: script khusus backend (testing helper, PHP tooling, dll).

Contoh saat ini:
- `scripts/sync-frontend-assets-to-backend.sh` -> lintas frontend + backend (tepat di root).
- `frontend/scripts/check-perf-budget.mjs` -> khusus frontend.
- `backend/scripts/test-with-pcov.sh` -> khusus backend.

## 🛠️ Developer Experience (DX)

JA-Platform menyediakan perangkat untuk mempermudah pengembangan:
- **Dokumentasi API (Scramble)**: Tersedia di `/docs/api` (Hanya untuk Admin/Super-Admin). Dokumentasi ini di-generate secara otomatis dari kode program.
- **Docker Environment (Sail)**: Konfigurasi Docker siap pakai menggunakan Laravel Sail untuk environment yang konsisten (PostgreSQL ready).

---

## 🚀 Fitur Utama

JA-Platform menggunakan arsitektur modular yang memungkinkan skalabilitas tinggi. Berikut adalah modul utama yang tersedia:

### 1. 🏫 Modul School (Sistem Informasi Sekolah)
Modul yang komprehensif untuk mendigitalkan seluruh operasional institusi pendidikan:
- **Akademik**: Manajemen kurikulum, tahun ajaran, semester, jadwal pelajaran, dan sistem penilaian otomatis.
- **Kesiswaan**: Portal penerimaan siswa baru (PPDB), manajemen data siswa, absensi, rekam konseling, pelacakan alumni (Tracer Study), hingga asrama dan transportasi.
- **SDM & HR**: Manajemen staf, struktur gaji, absensi pegawai, dan sistem payroll.
- **Operasional & Keuangan**: Manajemen anggaran (budgeting), invoicing, inventaris barang, tiket pemeliharaan (maintenance), hingga manajemen pengunjung.
- **LMS & CBT**: Sistem ujian berbasis komputer (Computer Based Test) dan manajemen konten pembelajaran digital.

### 2. 📰 Modul CMS (Content Management System)
Sistem manajemen konten tingkat lanjut untuk portal publik dan internal:
- **Studio (Content)**: Editor konten kaya (Rich Editor) dengan dukungan revisi, kategori, tag, dan custom fields.
- **Media Center**: Manajemen aset gambar, video, dan dokumen dengan pengelompokan folder yang rapi.
- **Reach (Engagement)**: Manajemen formulir dinamis, newsletter, dan sistem komentar.
- **Style & Layout**: Pengaturan tema, menu navigasi, widget, dan penyesuaian visual platform.
- **Performance & Marketing**: Integrasi SEO, Google Analytics, manajemen sitemap, dan sistem redirect URL.

### 3. ⚙️ Modul Core
Fondasi sistem yang menangani fungsionalitas dasar:
- **Security**: Autentikasi modern, sistem peran dan izin (RBAC), serta Two-Factor Authentication (2FA).
- **Logging**: Pencatatan aktivitas pengguna (Activity Logs) untuk audit trail.
- **System**: Pengaturan global platform, manajemen bahasa (i18n), dan optimasi performa.

---

## 🎨 UI & Animations (Aesthetics)

JA-Platform mengutamakan pengalaman pengguna yang premium dan modern:
- **Tailwind CSS 4**: Menggunakan standar terbaru untuk styling yang cepat dan konsisten.
- **GSAP (GreenSock Animation Platform)**: Engine animasi utama untuk menciptakan transisi halus, micro-interactions, dan efek visual yang memukau (WOW effect).
- **Responsive Design**: Optimal di seluruh perangkat (Desktop, Tablet, Mobile).

---

## 🛠️ Panduan Pengembangan

### Prasyarat
- PHP 8.2+
- Node.js 22.11+
- Composer & NPM

### Setup Cepat

#### Menggunakan Docker (Rekomendasi)
```bash
# Clone & pindah ke backend
cd ja-apps/backend
# Jalankan Sail
./vendor/bin/sail up -d
# Jalankan migrasi di dalam container
./vendor/bin/sail artisan migrate
```

#### Manual
1. Masuk ke direktori backend, jalankan `composer install` dan `php artisan migrate`.
2. Masuk ke direktori frontend, jalankan `npm install`.
3. Gunakan `script setup` pada komponen Vue dan manfaatkan GSAP untuk setiap interaksi UI yang membutuhkan sentuhan premium.

---
© 2026 K2NET & Jejakawan. All rights reserved.
