# JA-Platform: Post-Refactor Deep Audit Report

**Tanggal:** 16 Mei 2026
**Tujuan:** Memverifikasi fungsionalitas dan struktur modul pasca refactoring besar, membandingkannya dengan Master Plan (`platform_separation_master_plan.md`) dan kondisi kode sebelumnya (backup).

---

## 1. Analisis Struktur Modul (Deviasi & Konsistensi)

Secara umum, dekonstruksi modul monolitik CMS berjalan baik. Modul-modul Service Tier baru (`Forms`, `Newsletter`, `Search`, `Library`, `Layout`) telah memiliki struktur yang valid (tidak kosong) dan memiliki controller serta routing yang fungsional.

Namun, terdapat beberapa **deviasi signifikan** dari rancangan *Master Plan*:

| Komponen | Rencana Awal (Master Plan) | Implementasi Saat Ini | Analisis & Rekomendasi |
| :--- | :--- | :--- | :--- |
| **Category** | Tetap di modul **Cms** (§3.6, §7.5) | Dipindah ke modul **Library** | **Dapat Diterima.** Menjadikan kategori sebagai metadata global (seperti Tags) sangat masuk akal agar modul lain (seperti School) dapat menggunakannya. Namun, Master Plan perlu di-update. |
| **Themes** | Tetap di modul **Cms** (§6.3, §7.5) | Dipindah ke modul **Layout** | **Perlu Diskusi.** Master plan secara spesifik memisahkan *Layout* (struktur/data) dengan *Theme* (paket presentasi/Vue). Memindahkan Theme ke Layout mencampurkan konsep logika presentasi dengan struktur menu/widget. |
| **Email Template** | Menjadi bagian kernel **System** (§3.1) | File controller masih tersisa di folder **Cms** | **Tugas Tertunda.** File masih di CMS tetapi kehilangan routing-nya (lihat bagian fitur hilang di bawah). Harus segera dipindah ke System. |

---

## 2. Fitur yang Kehilangan Fungsi (Lost Features)

Berdasarkan perbandingan dengan versi backup (`ja-platform.bad`), berikut adalah fitur-fitur yang kode backend-nya (*Controller/Model*) masih ada, namun **akses API (Routing)-nya terhapus atau tidak terpasang dengan benar** dalam struktur `manage/` atau `public/` yang baru:

### A. Public Categories (Modul Library)
*   **Masalah:** Frontend publik tidak bisa lagi mengambil daftar kategori.
*   **Detail:** Setelah `Category` dipindah ke `Library`, route `manage/library/categories` sudah dibuat untuk admin. Namun, route `GET /api/v1/public/library/categories` (atau `public/cms/categories`) sama sekali **belum didaftarkan** di `Library/routes/api.php`.

### B. Comments Management Admin (Modul Cms)
*   **Masalah:** Admin tidak bisa lagi me-review, menyetujui (approve), atau menghapus komentar spam melalui Console.
*   **Detail:** Di backup, terdapat route `admin/cms/comments/*` untuk manajemen. Di file `Cms/routes/api.php` yang baru, `CommentController` hanya memiliki route publik untuk *submit* dan *read*. Blok routing untuk `manage/cms/comments` terlewatkan saat proses migrasi.

### C. Email Templates Management
*   **Masalah:** Manajemen template email tidak lagi bisa diakses sama sekali.
*   **Detail:** File `EmailTemplateController.php` masih berada di folder `Cms/app/Http/Controllers/Api/`. Namun, route untuk `email-templates` dihapus sepenuhnya dari `Cms/routes/api.php` dan belum dipindahkan ke `System/routes/api.php`.

---

## 3. Kesimpulan & Rencana Tindak Lanjut (Next Steps)

Refactoring telah sukses mendistribusikan kode ke dalam modul-modul independen (dengan tes yang 100% lulus karena pengujian pada fitur yang *hilang* mungkin tidak dijalankan/dihapus). Namun, ada beberapa proses migrasi (wiring routing) yang belum tuntas.

**Prioritas Perbaikan Berikutnya:**
1.  **Kembalikan Routing yang Hilang:** ✅ **SELESAI** (Route `public/library/categories` dan `manage/cms/comments` telah dipasang).
2.  **Selesaikan Migrasi Email Template:** ✅ **SELESAI** (`EmailTemplateController` telah dipindah ke `System/Console` dan di-route ke `manage/system/email-templates`).
3.  **Keputusan Arsitektur Themes:** ✅ **SELESAI** (Theme secara resmi dipindah ke modul **Layout**. `ThemeService` dan `ThemeHooksService` telah dipindahkan dari CMS ke Layout untuk menghilangkan ketergantungan antar-modul). Master Plan telah diperbarui.
