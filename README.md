# JA-Platform
**Edition**: edu v.1.0.0  
**Theme**: janari v.1.0.0  
**Version**: 1.0.0-beta.1

## 📌 Overview
JA-Platform is an enterprise-grade school management ecosystem designed for modern educational institutions. This monorepo contains the backend (Laravel) and frontend (Vue 3 + Vite) components.

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.2+
- Node.js 22+
- PostgreSQL or MySQL
- Redis

### Installation
For automated server installation, use the provided script:
```bash
npm run install:server
```
Or follow the [Installation Documentation](docs/installer.md).

### Development
1. **Frontend**:
   ```bash
   cd frontend && npm install && npm run dev
   ```
2. **Backend**:
   ```bash
   cd backend && composer install && php artisan serve
   ```

---

## 📦 Release
To create a production bundle:
```bash
npm run release
```

---

## 🛠 Developer
Developed with ❤️ by **Jejakawan**  
Website: [sekolahk2.id](https://sekolahk2.id)

---

## ⚖️ License
Standard licensing applies. Mentions of Pro/Community editions are for future licensing modules.
