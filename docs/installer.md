# JA-Platform Installation System Documentation
Version: 2.7 (Infrastructure Ready)

## 📌 Overview
The JA-Platform features a multi-channel installation system designed for maximum resilience, security, and ease of use. It supports both headless server provisioning and GUI-driven end-user setup.

---

## 🛠 1. Automated Infrastructure Provisioner (`scripts/install.sh`)
This is a robust Bash script designed for Linux environments (Ubuntu/Debian & RHEL/CentOS/AlmaLinux).

### Key Features:
- **OS Intelligence**: Automatically detects package managers (apt/dnf) and directory structures.
- **Resilience Engine**:
  - **Retry Mechanism**: Critical commands attempt 3 retries with delays.
  - **DNS Fallback**: Switches to Google DNS (8.8.8.8) if resolution fails.
  - **Mirror Switcher**: Fallbacks to global Ubuntu mirrors if local mirrors are down.
- **Full Provisioning**:
  - **Nginx**: Automatically creates Server Blocks (SPA + API + Storage).
  - **Database**: Automatically creates Database and User (PostgreSQL/MySQL).
  - **Redis**: Deploys optimized configuration.
  - **PHP-FPM**: Detects sockets and tunes performance (1GB RAM, 512MB Upload).
  - **Workers**: Configures Supervisor for queue management.
  - **Schedule**: Automatically registers Cron jobs.
- **Sudo Keep-alive**: Maintains sudo session to avoid multiple password prompts.

---

## 🌐 2. Web Installation Wizard
A Vue 3 based wizard accessible via `/install` when the system is uninitialized.

### Workflow:
1. **Language Selection**: Supports English and Indonesian (i18n).
2. **Environment Audit**: Real-time checking of PHP extensions, disk space, and OS requirements.
3. **Configuration**: GUI for Database, App Info, and Mail settings.
4. **Execution**: Automatically runs migrations, seeders, and creates the **Super Administrator** account.

---

## 💻 3. CLI Installer (`php artisan ja:install`)
An interactive command-line tool for developers or advanced users.
- Handles `.env` generation.
- Interactive database setup with adaptive port detection.
- Final summary with access URL and default credentials.

---

## 📦 4. Release Bundling (`scripts/bundle.sh`)
A script to prepare production-ready distribution packages.

### Process:
1. **Build**: Compiles frontend assets into `frontend/dist`.
2. **Copy**: Transfers necessary backend files to a versioned release folder.
3. **Protect**: Placeholder for Code Obfuscation (e.g., YAKPRO-PO).
4. **Cleanup**: Removes `.git`, `node_modules`, `tests`, and development logs.
5. **Pack**: Creates a clean ZIP archive for the client.

---

## 🔐 5. Default Credentials
After installation, the following account is automatically created:
- **Username**: `super`
- **Password**: `Senja@jejakawan`

---

## 🚀 6. Update Strategy for Obfuscated Releases
Updates are handled via **Incremental Overwrites**:
1. Develop on clean source code.
2. Build and Obfuscate the release package.
3. Client downloads the obfuscated bundle and overwrites existing files.
4. Run migrations if necessary.
