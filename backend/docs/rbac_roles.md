# Role-Based Access Control (RBAC) Documentation

This document serves as the primary reference for all agents (AI/Human) regarding user roles, permissions, and security scoping within the JA-Platform Education ecosystem.

## 1. Role Hierarchy (Rank System)

The system uses a numerical rank to determine authority. A higher rank can manage or restrict lower ranks.

| Role Name | Rank | Domain Access | Description |
| :--- | :--- | :--- | :--- |
| `super-admin` | 100 | **Global** | Full system access, security settings, and global infrastructure. |
| `ketua-yayasan` | 100 | **Global (School/CMS)** | Full access to all school units and CMS content across the foundation. |
| `admin-yayasan` | 95 | **Global (School/CMS)** | Broad management access across all units, slightly restricted from core system configs. |
| `kepala-sekolah` | 90 | **Unit-Scoped** | Full access to a specific school unit (SMP/SMA/etc). |
| `admin-sekolah` | 90 | **Unit-Scoped** | Operational admin for a specific school unit. |
| `admin-kurikulum`| 85 | **Unit-Scoped** | Academic-focused access (Curriculum, Schedule, Grades). |
| `admin` | 80 | **CMS Global** | Operational CMS admin (Blog, Pages, Media) with no access to vital system functions. |
| `guru` | 70 | **Unit-Scoped** | Teaching staff with access to their specific classes and subjects. |
| `wali-kelas` | 75 | **Unit-Scoped** | Guru rank + additional access to class analytics and student management. |

---

## 2. Security Scoping (Unit Isolation)

### Unit Context (X-Level-ID)
The system uses the `X-Level-ID` header to scope database queries via the `ScopedByUnit` trait.

- **Global Admins (Rank >= 95)**: Can pass any `X-Level-ID` to switch context between units.
- **Unit Admins (Rank < 95)**: 
    - MUST pass an `X-Level-ID` that is assigned to them (via the `staff` table relationship).
    - If they attempt to access an unassigned unit, the `UnitContextMiddleware` will return **403 Forbidden**.
    - If no header is provided, the system defaults to their first assigned unit.

### Middleware Implementation
- File: `backend/Modules/School/app/Http/Middleware/UnitContextMiddleware.php`
- Logic: Validates user rank and assigned levels before setting the global database context.

---

## 3. Core Permissions Categories

- **Institution**: `manage schools`, `manage organization` (Rank >= 95 only).
- **Academic**: `manage academic`, `manage curriculum`, `manage schedule`.
- **HR**: `view staff`, `manage staff`, `manage payroll`.
- **CMS**: `manage content`, `publish content`, `manage media`.

---

## 4. Guidelines for Updates
- When adding new roles, update `Modules\Core\Models\User.php` (`getRoleRank` and `isAtLeastRole` methods).
- When adding new scoped tables, apply the `ScopedByUnit` trait to the Eloquent model.
- Always run `php artisan db:seed --class=SchoolRoleSeeder` after changing permissions in seeders.

---
*Last Updated: 2026-05-03*
