# Database Refinement & Consistency Report - Level 9

This document outlines the refinements made to ensure the database remains consistent, synchronized with models, and "ready-to-use" immediately after a `php artisan migrate:fresh --seed` command.

## 1. Migration Flattening & Consolidation (COMPLETED)

Previously, the School module had **51 fragmented migrations**. We have consolidated them into **8 Domain-Based Migration Sets** to improve performance and developer experience.

### New Migration Structure:
1. `001_sch_infrastructure_tables`: Schools, Levels, and Departments.
2. `002_sch_academic_tables`: Academic Years, Semesters, Subjects, Classes, and Grades.
3. `003_sch_hr_tables`: Staff, Attendance, Salary, and Payroll.
4. `004_sch_student_tables`: Students, Achievements, Discipline, and Alumni.
5. `005_sch_logistics_tables`: Assets, Buildings, Inventory, Hostel, and Transport.
6. `006_sch_operations_tables`: Visitors, UKS, Library, and OSIS.
7. `007_sch_admission_tables`: PPDB (Enrollments) and Verification.
8. `008_sch_lms_tables`: LMS Courses, Lessons, Topics, and Progress.

**Status**: All tables now use the `sch_` prefix and native **JSONB** types for all metadata/settings columns.

---

## 2. Model-Migration Synchronization

### Global JSONB Standard
- All models (`Staff`, `Student`, `Content`, `Course`, `TopicProgress`) have been updated with:
    - `$casts`: Ensuring `jsonb` columns are cast to `array`.
    - `$fillable`: Including `metadata` or `meta` fields.
    - `SoftDeletes`: Standardized across all core entities.

### Castings Refinement
- **Fixed**: Boolean fields (e.g., `is_multi_unit`, `is_active`, `is_global`) now correctly cast, preventing UI glitches in the frontend.

---

## 3. Seeder Optimization (The "One-Click" Setup)

### Global Infrastructure
- **Media Folders**: Standard folders (`Logos`, `Students`, `Staff`, `CMS Content`) are automatically created.
- **Admin Linkage**: The default admin user is now perfectly linked to the `Staff` record in Unit 15 with full metadata, ensuring zero middleware blocks.

### Theme Activation
- **Theme Scanning**: Automatic scanning and activation of the "Janari" theme during seeding.
- **Academic Subjects**: A new `SubjectSeeder` provides 12+ default subjects (General & Vocational) to make the system usable immediately.

---

## 4. Maintenance Standards

1. **Table Naming**: Strictly use the `sch_{subdomain}_{table}` pattern for all new school tables.
2. **Data Types**: Always use `jsonb` for dynamic data; never use plain `json` or `text` for configuration.
3. **Seeding**: Any new module must provide a Seeder that integrates with the main `DatabaseSeeder`.

---
*Last Audit: 2026-05-03 | Standard: Level 9 Refinement (Flattened)*
