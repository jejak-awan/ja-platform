# JA-Platform - Backend
**Edition**: edu v.1.0.0  
**Theme**: janari v.1.0.0  
**Version**: 1.0.0-beta.1

This is the backend service for **JA-Platform**. Developed by **Jejakawan** (sekolahk2.id).

## 🚀 Stack

- **Framework**: Laravel 12
- **PHP**: ^8.2
- **Database**: MySQL/PostgreSQL (PostgreSQL recommended for Advanced Search)
- **Worker/Queue**: Laravel Horizon
- **Realtime**: Laravel Reverb

## Headless LMS Module (Native)

Modul LMS ini dibangun secara *native* di dalam modul School untuk mendukung pembelajaran daring yang terintegrasi penuh dengan data institusi dan siswa.

## Fitur Utama
- **Polymorphic Topics**: Materi pembelajaran mendukung berbagai tipe (RichText, Video, PDF, Quiz).
- **Quiz Engine**: Sistem penilaian otomatis dengan dukungan berbagai tipe pertanyaan.
- **Multi-tenancy**: Terintegrasi penuh dengan sistem sekolah (X-School-Id).
- **Student Progress**: Pelacakan progres belajar siswa secara real-time.
- **Supported Content Types**:
    - **RichText**: Standard HTML/Text materials.
    - **Video**: Support for direct MP4 links or YouTube embeds.
    - **PDF**: Document-based learning.
    - **Quiz**: Full assessment system with Multiple Choice, True/False, and Short Answer questions.

## 🛠️ Installation

```bash
# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Seed LMS Data (Optional)
php artisan db:seed --class="Modules\School\database\seeders\LmsSeeder"

# Start development server
php artisan serve
```

---
For more information, please refer to the [Root README](../../README.md).
