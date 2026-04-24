# JA-Platform - Backend

This is the backend service for **JA-Platform**.

## 🚀 Stack

- **Framework**: Laravel 12
- **PHP**: ^8.2
- **Database**: MySQL/PostgreSQL
- **Worker/Queue**: Laravel Horizon
- **Realtime**: Laravel Reverb

## 🛠️ Installation

```bash
# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Start development server
php artisan serve
```

---
For more information, please refer to the [Root README](../../README.md).
