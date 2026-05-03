# Multi-Unit Architecture: Global Context Implementation

Sistem kini mendukung mode **"Pusat / Yayasan"** (Global Context) yang memungkinkan pengguna dengan hak akses tinggi (Super Admin, Global Admin, Pengurus Yayasan) untuk melihat data lintas unit atau mengelola data tingkat lembaga secara keseluruhan.

## 🚀 Fitur Baru & Perubahan

### 1. Global Context Switcher (Frontend)
- **Opsi "Pusat / Yayasan"**: Muncul di dropdown switcher hanya untuk pengguna dengan `role_rank >= 95`.
- **Visual Indicator**: Menggunakan ikon gedung (`Building2`) untuk membedakan mode Global dengan unit operasional (sekolah).
- **Auto-Sync**: Memilih mode Global akan membersihkan filter unit di backend, sehingga menampilkan data dari seluruh unit (konsolidasi).

### 2. Backend Switch Logic (Laravel)
- **Switch API Update**: Controller `SchoolUnitController@switch` kini menerima ID `0` untuk berpindah ke mode Global.
- **Session Management**: Mengirim ID `0` akan menghapus `active_school_unit_id` dari session, yang secara otomatis menonaktifkan Global Scope pada model-model yang mendukung unit scoping.
- **Security**: Perpindahan ke mode Global tetap dilindungi oleh otorisasi role `super-admin` atau `admin` global.

### 3. Context Awareness
- Saat berada di mode Global, model yang menggunakan trait `ScopedByUnit` akan menampilkan semua data tanpa filter `school_unit_id`.
- Data yang bersifat "Shared" (lintas unit) tetap dapat diakses dan dikelola dengan benar.

## 🛠️ Cara Menggunakan
1. Login sebagai **Super Admin**.
2. Klik dropdown **"Pilih Jenjang"** di pojok kanan atas.
3. Pilih opsi **"Pusat / Yayasan"**.
4. Halaman akan dimuat ulang, dan Anda sekarang berada di konteks Global (melihat semua data siswa, konten, dll dari semua jenjang).

---
**Status**: Operasional & Terintegrasi.
