# Role-Based Access Control (RBAC) Documentation - Level 9 Standard

This document serves as the primary reference for all agents (AI/Human) regarding user roles, permissions, and security scoping within the JA-Platform Education ecosystem.

## 1. Role Hierarchy (Rank System)

The system uses a numerical rank to determine authority. A higher rank can manage or restrict lower ranks. Logic is enforced in `Modules\Core\Models\User.php`.

| Role Name | Rank | Domain Access | Description |
| :--- | :---: | :--- | :--- |
| **super** | 100 | **Global** | Full system access, security, and global infrastructure. |
| **admin** | 95 | **Global (Core/CMS)** | Global operational management for CMS and Core modules. |
| **admin-yayasan** | 90 | **School-Scoped** | Foundation access. Manages all units within a specific school. |
| **operator-yayasan**| 85 | **School-Scoped** | Foundation-level data monitoring and reporting. |
| **operator** | 85 | **Global** | Global data entry and operational monitoring. |
| **admin-unit** | 80 | **Unit-Scoped** | Principal/Unit Admin. Full access to ONE specific unit (SMK/SMA). |
| **operator-unit** | 70 | **Unit-Scoped** | TU Staff / Academic Admin for ONE specific unit. |
| **wali-kelas** | 55 | **Unit-Scoped** | Teaching + specialized student class management. |
| **staff** / **guru** | 50 | **Unit-Scoped** | Teaching and standard employee tasks. |
| **pembina-ekskul**| 50 | **Unit-Scoped** | Extracurricular and student organization management. |
| **siswa** | 10 | **Personal** | Individual access to LMS and profile. |
| **orang-tua** | 5 | **Personal** | Parent access to monitor linked student data. |

## 2. Scoping & Multi-Unit Security

Otoritas data dibatasi oleh `school_id` dan `school_unit_id`.

- **Global Scope**: Roles with rank >= 95. No unit filters applied.
- **School Scope**: Roles with rank 85-90. Filtered by `school_id`. Can access all units in that school.
- **Unit Scope**: Roles with rank 50-80. Strictly filtered by `school_unit_id` via `UnitContextMiddleware`.
- **Personal Scope**: Rank < 50. Strictly filtered by `user_id`.

## 3. Position (Jabatan) vs Role

Jangan tertukar antara **Role** (Izin Sistem) dan **Jabatan** (Atribut Pegawai).

- **Role**: `admin-unit` (Menentukan menu apa yang bisa diklik).
- **Jabatan**: `Wakasek Kurikulum` (Label di profil staff, disimpan di tabel `sch_hr_staff`).
- **Tugas Tambahan**: `Wali Kelas` (Ditangani via relasi di tabel `sch_acad_study_groups`).

## 4. Permission Mapping

- **super**: `syncPermissions(Permission::all())`.
- **admin-unit**: Fokus pada `manage academic`, `manage students`, `manage staff` (Unit Scope).
- **guru**: Fokus pada `grade assignments`, `input journal`, `view attendance`.

---
*Updated: 2026-05-03 | Standard: Level 9 Refinement*
