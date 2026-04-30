# JA-Platform - Backend

This is the backend service for **JA-Platform**.

## 🚀 Stack

- **Framework**: Laravel 12
- **PHP**: ^8.2
- **Database**: MySQL/PostgreSQL (PostgreSQL recommended for Advanced Search)
- **Worker/Queue**: Laravel Horizon
- **Realtime**: Laravel Reverb

## 🎓 LMS Module (Headless Architecture)

The School module now features a robust, headless Learning Management System (LMS) inspired by the **EscolaLMS** architecture.

### Key Features:
- **Hierarchical Structure**: `Course -> Lesson -> Topic`.
- **Polymorphic Content**: Topics support multiple content types via a `topicable` relationship.
- **Supported Content Types**:
    - **RichText**: Standard HTML/Text materials.
    - **Video**: Support for direct MP4 links or YouTube embeds.
    - **PDF**: Document-based learning.
    - **Quiz**: Full assessment system with Multiple Choice, True/False, and Short Answer questions.
- **Progress Tracking**: Automatic progress saving and pass-score validation for Quizzes.

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
