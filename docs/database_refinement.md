# Database Refinement & Consistency Report - Level 9

This document outlines the refinements made to ensure the database remains consistent, synchronized with models, and "ready-to-use" immediately after a `php artisan migrate:fresh --seed` command.

## 1. Migration Flattening & Consolidation (COMPLETED)

Previously, the School module had **51 fragmented migrations**. We have consolidated them into **9 Domain-Based Migration Sets** to improve performance and developer experience.

### New Migration Structure:
1. `001_sch_infrastructure_tables`: Schools, Levels, and Departments.
2. `002_sch_academic_tables`: Academic Years, Semesters, Subjects, Study Groups (Classes), Schedules, Journals, and Grades.
3. `003_sch_hr_tables`: Staff, Attendance, Salary, and Payroll.
4. `004_sch_student_tables`: Students, Achievements, Discipline, and Alumni.
5. `005_sch_logistics_tables`: Assets, Buildings, Inventory, Hostel, and Transport.
6. `006_sch_operations_tables`: Graduation Settings & Results, Visitors, Document Templates.
7. `007_sch_admission_tables`: PPDB (Enrollments) and Verification.
8. `008_sch_lms_tables`: LMS Courses, Lessons (Modules), Topics, and Progress.
9. `009_sch_osis_tables`: OSIS Programs, Members, and Suggestions.

**Status**: All tables now use the `sch_` prefix and native **JSONB** types for all metadata/settings columns.

---

## 2. Model-Migration Synchronization

### Core Traits & Scoping
- **ScopedByUnit**: All school-related models (Students, Staff, Classes, Schedules, Assets) MUST use the `ScopedByUnit` trait.
- **school_unit_id**: Migrations for these models MUST include `$table->foreignId('school_unit_id')->constrained('sch_ins_levels')->onDelete('cascade');`.
- **SoftDeletes**: Mandatory for all transactional and master data tables. Ensure migrations have `$table->softDeletes();`.

### Global JSONB Standard
- All models with dynamic configurations (Staff, Student, Course, GraduationSetting) MUST:
    - Use `jsonb` in migrations.
    - Cast to `array` or `object` in Eloquent models.

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

1. **Table Naming**: Strictly use the `sch_{subdomain}_{table}` pattern (e.g., `sch_acad_schedules`).
2. **Column Naming**: Use consistent foreign keys (e.g., always `staff_id` for employees, not `teacher_id` or `counselor_id`).
3. **Data Types**: Always use `jsonb` for dynamic data.
4. **Unit Context**: Never skip `school_unit_id` on entities that belong to a specific school level.
5. **Seeding**: Graduation settings for the current/upcoming year (e.g., 2026) must be seeded to avoid 404s.

---
*Last Audit: 2026-05-03 | Standard: Level 9 Refinement (Flattened)*
