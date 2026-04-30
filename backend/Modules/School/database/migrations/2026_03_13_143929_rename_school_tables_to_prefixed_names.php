<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Institution
        if (Schema::hasTable('schools')) Schema::rename('schools', 'sch_ins_schools');
        if (Schema::hasTable('school_levels')) Schema::rename('school_levels', 'sch_ins_levels');

        // Academic
        if (Schema::hasTable('academic_years')) Schema::rename('academic_years', 'sch_acad_years');
        if (Schema::hasTable('semesters')) Schema::rename('semesters', 'sch_acad_semesters');
        if (Schema::hasTable('departments')) Schema::rename('departments', 'sch_acad_departments');
        if (Schema::hasTable('subjects')) Schema::rename('subjects', 'sch_acad_subjects');
        if (Schema::hasTable('study_groups')) Schema::rename('study_groups', 'sch_acad_study_groups');
        if (Schema::hasTable('study_group_members')) Schema::rename('study_group_members', 'sch_acad_study_group_members');
        if (Schema::hasTable('schedules')) Schema::rename('schedules', 'sch_acad_schedules');
        if (Schema::hasTable('attendances')) Schema::rename('attendances', 'sch_acad_attendances');
        if (Schema::hasTable('teaching_journals')) Schema::rename('teaching_journals', 'sch_acad_teaching_journals');

        // Admission
        if (Schema::hasTable('enrollments')) Schema::rename('enrollments', 'sch_adm_enrollments');
        if (Schema::hasTable('enrollment_documents')) Schema::rename('enrollment_documents', 'sch_adm_documents');
        if (Schema::hasTable('document_verifications')) Schema::rename('document_verifications', 'sch_adm_verifications');

        // LMS
        if (Schema::hasTable('question_banks')) Schema::rename('question_banks', 'sch_lms_question_banks');
        if (Schema::hasTable('questions')) Schema::rename('questions', 'sch_lms_questions');
        if (Schema::hasTable('exams')) Schema::rename('exams', 'sch_lms_exams');
        if (Schema::hasTable('exam_results')) Schema::rename('exam_results', 'sch_lms_results');



        // HR
        if (Schema::hasTable('staff')) Schema::rename('staff', 'sch_hr_staff');
        if (Schema::hasTable('staff_attendances')) Schema::rename('staff_attendances', 'sch_hr_attendances');
        if (Schema::hasTable('staff_shifts')) Schema::rename('staff_shifts', 'sch_hr_shifts');
        if (Schema::hasTable('leave_requests')) Schema::rename('leave_requests', 'sch_hr_leaves');
        if (Schema::hasTable('salary_structures')) Schema::rename('salary_structures', 'sch_hr_salary_structures');
        if (Schema::hasTable('payrolls')) Schema::rename('payrolls', 'sch_hr_payrolls');
        if (Schema::hasTable('job_vacancies')) Schema::rename('job_vacancies', 'sch_hr_job_vacancies');
        if (Schema::hasTable('job_applications')) Schema::rename('job_applications', 'sch_hr_job_applications');

        // Student
        if (Schema::hasTable('students')) Schema::rename('students', 'sch_std_students');
        if (Schema::hasTable('achievements')) Schema::rename('achievements', 'sch_std_achievements');
        if (Schema::hasTable('violations')) Schema::rename('violations', 'sch_std_violations');
        if (Schema::hasTable('counseling_records')) Schema::rename('counseling_records', 'sch_std_counseling_records');
        if (Schema::hasTable('alumni')) Schema::rename('alumni', 'sch_std_alumni');
        if (Schema::hasTable('tracer_studies')) Schema::rename('tracer_studies', 'sch_std_tracer_studies');

        // Logistics
        if (Schema::hasTable('school_assets')) Schema::rename('school_assets', 'sch_log_assets');
        if (Schema::hasTable('land_assets')) Schema::rename('land_assets', 'sch_log_land');
        if (Schema::hasTable('buildings')) Schema::rename('buildings', 'sch_log_buildings');
        if (Schema::hasTable('rooms')) Schema::rename('rooms', 'sch_log_rooms');
        if (Schema::hasTable('maintenance_tickets')) Schema::rename('maintenance_tickets', 'sch_log_maintenance_tickets');
        if (Schema::hasTable('hostel_blocks')) Schema::rename('hostel_blocks', 'sch_log_hostel_blocks');
        if (Schema::hasTable('hostel_rooms')) Schema::rename('hostel_rooms', 'sch_log_hostel_rooms');
        if (Schema::hasTable('hostel_beds')) Schema::rename('hostel_beds', 'sch_log_hostel_beds');
        if (Schema::hasTable('hostel_allocations')) Schema::rename('hostel_allocations', 'sch_log_hostel_allocations');
        if (Schema::hasTable('vehicles')) Schema::rename('vehicles', 'sch_log_vehicles');
        if (Schema::hasTable('vehicle_routes')) Schema::rename('vehicle_routes', 'sch_log_transport_routes');
        if (Schema::hasTable('transport_registrations')) Schema::rename('transport_registrations', 'sch_log_transport_registrations');
        if (Schema::hasTable('inventory_categories')) Schema::rename('inventory_categories', 'sch_log_inventory_categories');
        if (Schema::hasTable('inventory_items')) Schema::rename('inventory_items', 'sch_log_inventory_items');
        if (Schema::hasTable('inventory_transactions')) Schema::rename('inventory_transactions', 'sch_log_inventory_transactions');

        // Operations
        if (Schema::hasTable('activity_log')) Schema::rename('activity_log', 'sch_ops_audit_logs');
        if (Schema::hasTable('visitors')) Schema::rename('visitors', 'sch_ops_visitors');
        if (Schema::hasTable('guest_logs')) Schema::rename('guest_logs', 'sch_ops_guest_logs');
        if (Schema::hasTable('uks_visits')) Schema::rename('uks_visits', 'sch_ops_uks_visits');
        if (Schema::hasTable('library_books')) Schema::rename('library_books', 'sch_ops_library_books');
        if (Schema::hasTable('library_circulations')) Schema::rename('library_circulations', 'sch_ops_library_circulations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse operations in reverse order
        Schema::rename('sch_ops_library_circulations', 'library_circulations');
        Schema::rename('sch_ops_library_books', 'library_books');
        Schema::rename('sch_ops_uks_visits', 'uks_visits');
        Schema::rename('sch_ops_guest_logs', 'guest_logs');
        Schema::rename('sch_ops_visitors', 'visitors');
        Schema::rename('sch_ops_audit_logs', 'activity_log');

        Schema::rename('sch_log_inventory_transactions', 'inventory_transactions');
        Schema::rename('sch_log_inventory_items', 'inventory_items');
        Schema::rename('sch_log_inventory_categories', 'inventory_categories');
        Schema::rename('sch_log_transport_registrations', 'transport_registrations');
        Schema::rename('sch_log_transport_routes', 'vehicle_routes');
        Schema::rename('sch_log_vehicles', 'vehicles');
        Schema::rename('sch_log_hostel_allocations', 'hostel_allocations');
        Schema::rename('sch_log_hostel_beds', 'hostel_beds');
        Schema::rename('sch_log_hostel_rooms', 'hostel_rooms');
        Schema::rename('sch_log_hostel_blocks', 'hostel_blocks');
        Schema::rename('sch_log_maintenance_tickets', 'maintenance_tickets');
        Schema::rename('sch_log_rooms', 'rooms');
        Schema::rename('sch_log_buildings', 'buildings');
        Schema::rename('sch_log_land', 'land_assets');
        Schema::rename('sch_log_assets', 'school_assets');

        Schema::rename('sch_std_tracer_studies', 'tracer_studies');
        Schema::rename('sch_std_alumni', 'alumni');
        Schema::rename('sch_std_counseling_records', 'counseling_records');
        Schema::rename('sch_std_violations', 'violations');
        Schema::rename('sch_std_achievements', 'achievements');
        Schema::rename('sch_std_students', 'students');

        Schema::rename('sch_hr_job_applications', 'job_applications');
        Schema::rename('sch_hr_job_vacancies', 'job_vacancies');
        Schema::rename('sch_hr_payrolls', 'payrolls');
        Schema::rename('sch_hr_salary_structures', 'salary_structures');
        Schema::rename('sch_hr_leaves', 'leave_requests');
        Schema::rename('sch_hr_shifts', 'staff_shifts');
        Schema::rename('sch_hr_attendances', 'staff_attendances');
        Schema::rename('sch_hr_staff', 'staff');



        Schema::rename('sch_lms_results', 'exam_results');
        Schema::rename('sch_lms_exams', 'exams');
        Schema::rename('sch_lms_questions', 'questions');
        Schema::rename('sch_lms_question_banks', 'question_banks');

        Schema::rename('sch_adm_verifications', 'document_verifications');
        Schema::rename('sch_adm_documents', 'enrollment_documents');
        Schema::rename('sch_adm_enrollments', 'enrollments');

        Schema::rename('sch_acad_teaching_journals', 'teaching_journals');
        Schema::rename('sch_acad_attendances', 'attendances');
        Schema::rename('sch_acad_schedules', 'schedules');
        Schema::rename('sch_acad_study_group_members', 'study_group_members');
        Schema::rename('sch_acad_study_groups', 'study_groups');
        Schema::rename('sch_acad_subjects', 'subjects');
        Schema::rename('sch_acad_departments', 'departments');
        Schema::rename('sch_acad_semesters', 'semesters');
        Schema::rename('sch_acad_years', 'academic_years');

        Schema::rename('sch_ins_levels', 'school_levels');
        Schema::rename('sch_ins_schools', 'schools');
    }
};
