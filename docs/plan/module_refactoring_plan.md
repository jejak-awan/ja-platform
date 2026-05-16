# Backend Module Separation & Refactoring Plan

This document outlines the audit results of the current `Modules/Core` and provides a recommendation for a cleaner, tiered modular architecture.

## 1. Audit of Current `Modules/Core`

Currently, `Core` acts as a "catch-all" for anything that isn't CMS or School specific. This creates high coupling and makes the system harder to maintain.

### Current Feature Categorization
| Category | Models / Features | Dependency Type |
| :--- | :--- | :--- |
| **Kernel (Core)** | User, Auth, Settings, Plugins, Languages, Translations | Fundamental |
| **Security** | SecurityLogs, IpList, CspReport, Vulnerability, Integrity | Operational |
| **Media** | Media, MediaFolders, MediaUsage, Thumbnails | Functional Service |
| **Analytics** | AnalyticsEvents, Sessions, Visits | Observability |
| **Infrastructure** | Backups, Webhooks, ScheduledTasks, ActivityLogs | System Support |
| **Content Base** | CustomFields, FieldGroups, Tags, Redirects | Shared Library |
| **Communication** | EmailTemplates, NewsletterSubscribers, Notifications | Feature |

---

## 2. Proposed Modular Tiers

To achieve a "Clean Architecture" within a modular monolith, we should separate modules into four logical tiers.

### Tier 1: System (The Kernel)
*Purpose: Bootstraps the platform, handles authentication, and manages the module registry.*
- **Module: `System`** (Rename of Core's essentials)
    - Auth & User Management (SuperAdmin, Permissions)
    - System Settings (Redis, Cache, DB Config)
    - Plugin/Module Registry
    - Multi-tenancy / Workspace scoping logic
    - **Prefix**: `sys_` (Table), `System` (Namespace)

### Tier 2: Infrastructure (The Ops)
*Purpose: Handles system maintenance, security, and external integrations.*
- **Module: `Security`**
    - Firewalls, IP Blacklisting, CSP Reporting, Security Audits.
- **Module: `Infra`** (or `Registry`)
    - Backups, Webhooks, Cron/Scheduled Tasks.
- **Prefix**: `infra_` / `sec_`

### Tier 3: Service (The Engines)
*Purpose: Provides specialized, reusable functionality to any Application module.*
- **Module: `Media`**
    - File storage, Image processing, Folder organization.
- **Module: `Engagement`**
    - Analytics, Newsletters, Tracking.
- **Module: `Library`** (or `Metadata`)
    - Shared Tagging system, Custom Fields (ACF-style), Translations.
- **Prefix**: `srv_` / `med_` / `eng_`

### Tier 4: Application (The Domain)
*Purpose: Business logic for specific products.*
- **Module: `Cms`**: Blogging, Themes, Menus.
- **Module: `School`**: Academic, HR, Student records.
- **Prefix**: `cms_` / `sch_`

---

## 3. Folder Structure & Naming Convention

### File Naming & Prefixes
1. **Models**: No prefix needed in the class name (e.g., `Modules\Media\Models\File`), but tables **must** keep prefixes.
2. **Controllers**: Use suffix (e.g., `Modules\Media\Http\Controllers\Api\MediaUploadController`).
3. **Services**: Keep it specific (e.g., `Modules\System\Services\AuthService`).

### Proposed Directory Layout
```text
backend/Modules/
├── System/             # Kernel & Registry
│   ├── app/Kernel/     # Base classes for other modules
│   ├── app/Models/     # User, Setting, Plugin
│   └── ...
├── Security/           # Firewall & Audit
├── Infra/              # Webhooks, Backups, Tasks
├── Media/              # File Engine
├── Engagement/         # Analytics & Newsletter
├── Cms/                # Application: CMS
└── School/             # Application: School Management
```

---

## 4. Immediate Refactoring Recommendations

1. **Extract Media**: Move `Media.php`, `MediaFolder.php`, and `GenerateMediaThumbnails` command to a new `Media` module.
2. **Extract Analytics**: Move `Analytics*` models and `CleanupAnalytics` command to an `Engagement` or `Analytics` module.
3. **Rename Core to System**: Narrow the scope of `Core` to only the absolute essentials (User, Auth, Settings, Plugins).
4. **Shared Traits**: Create `Modules/System/app/Traits/` for shared logic like `HasWorkspace` or `LogsActivity` so they can be imported by any module without depending on a bloated `Core`.

---

## 5. Audit of `CoreServiceProvider`
The current `CoreServiceProvider` is too busy. 
- **Move Command Schedules**: Each module should define its own schedules in its own ServiceProvider.
- **Move Config Overrides**: Security-specific config (CSP) should be in a `SecurityServiceProvider`.
- **Dashboard Registry**: Keep in `System` as it's a kernel feature.

---

## 6. Console Rebranding & Dynamic Access Control

To move away from the generic and "vulgar" "Admin" terminology, the platform will adopt a **Console** branding and a **Dynamic Capability-based Access** model.

### Rebranding
- **"Admin" -> "Console"**: All management interfaces will be rebranded as the **Console** (e.g., *Jejakawan Console*).
- **URL Prefixes**: 
    - Frontend: `/admin` -> `/console`
    - API: `/api/v1/admin` -> `/api/v1/manage` or `/api/v1/console`
- **Entry Points**: 
    - `admin.html` will be deprecated in favor of a single entry point or renamed to `console.html`.

### Dynamic Access (Unified Controllers)
Instead of "Hard-Separation" between Admin and User controllers, the system will move towards **Domain-driven Controllers**:
- **Consolidation**: `AdminLmsController` and `StudentLmsController` should be merged into a single `LmsController`.
- **Authorization**: Use Laravel Policies and Spatie Permissions to dynamically filter data within the same endpoint.
    - *Staff/Manager*: Sees all records in their scope.
    - *Student/User*: Sees only their own records.
- **Benefits**: Reduces code duplication, ensures consistent business logic, and simplifies API maintenance.

### Permission-Driven UI
- The frontend will no longer rely on hardcoded "admin" folders or entry points.
- The UI will be rendered dynamically based on the user's **Capability List** fetched during login.
- Access to the "Console" layout is granted if the user has any management-level permissions.

> [!IMPORTANT]
> This refactor will require updating all `use` statements across the project. I recommend doing this module-by-module, starting with extracting **Media** as it is the most isolated "service" currently inside Core.
