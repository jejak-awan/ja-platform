<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing indexes on frequently queried columns for performance.
     * Column-existence checks ensure safety across different schema states.
     */
    public function up(): void
    {
        // Students table indexes
        Schema::table('sch_std_students', function (Blueprint $table) {
            $table->index('school_id', 'idx_students_school_id');
            $table->index('school_level_id', 'idx_students_school_level_id');
            $table->index('department_id', 'idx_students_department_id');
            $table->index(['school_id', 'school_level_id'], 'idx_students_school_level');
        });

        if (Schema::hasColumn('sch_std_students', 'status')) {
            Schema::table('sch_std_students', function (Blueprint $table) {
                $table->index('status', 'idx_students_status');
                $table->index(['school_id', 'status'], 'idx_students_school_status');
            });
        }

        if (Schema::hasColumn('sch_std_students', 'user_id')) {
            Schema::table('sch_std_students', function (Blueprint $table) {
                $table->index('user_id', 'idx_students_user_id');
            });
        }

        // Staff table indexes
        Schema::table('sch_hr_staff', function (Blueprint $table) {
            $table->index('school_id', 'idx_staff_school_id');
        });

        if (Schema::hasColumn('sch_hr_staff', 'ptk_type')) {
            Schema::table('sch_hr_staff', function (Blueprint $table) {
                $table->index('ptk_type', 'idx_staff_ptk_type');
            });
        }

        if (Schema::hasColumn('sch_hr_staff', 'employment_status')) {
            Schema::table('sch_hr_staff', function (Blueprint $table) {
                $table->index('employment_status', 'idx_staff_employment_status');
            });
        }

        // Student Bills indexes
        Schema::table('sch_fin_bills', function (Blueprint $table) {
            $table->index('school_id', 'idx_bills_school_id');
            $table->index('student_id', 'idx_bills_student_id');
            $table->index('status', 'idx_bills_status');
            $table->index(['student_id', 'fee_type_id', 'academic_year_id'], 'idx_bills_unique_check');
            $table->index(['school_id', 'status'], 'idx_bills_school_status');
        });

        // Payment Transactions indexes
        Schema::table('sch_fin_transactions', function (Blueprint $table) {
            $table->index('student_bill_id', 'idx_transactions_bill_id');
            $table->index('payment_date', 'idx_transactions_payment_date');
            $table->index(['student_bill_id', 'payment_date'], 'idx_transactions_bill_date');
        });

        // Schedules indexes
        Schema::table('sch_acad_schedules', function (Blueprint $table) {
            $table->index('staff_id', 'idx_schedules_staff_id');
            $table->index('study_group_id', 'idx_schedules_study_group_id');
            $table->index('day', 'idx_schedules_day');
            $table->index(['staff_id', 'day'], 'idx_schedules_staff_day');
            $table->index(['study_group_id', 'day'], 'idx_schedules_group_day');
        });

        if (Schema::hasColumn('sch_acad_schedules', 'room_id')) {
            Schema::table('sch_acad_schedules', function (Blueprint $table) {
                $table->index(['room_id', 'day'], 'idx_schedules_room_day');
            });
        }

        // Attendances indexes
        Schema::table('sch_acad_attendances', function (Blueprint $table) {
            $table->index('student_id', 'idx_attendances_student_id');
            $table->index('date', 'idx_attendances_date');
            $table->index('status', 'idx_attendances_status');
            $table->index(['student_id', 'date'], 'idx_attendances_student_date');
        });

        // Expenses indexes
        Schema::table('sch_fin_expenses', function (Blueprint $table) {
            $table->index('school_id', 'idx_expenses_school_id');
            $table->index('category', 'idx_expenses_category');
            $table->index('date', 'idx_expenses_date');
        });

        // Teaching Journals indexes
        Schema::table('sch_acad_teaching_journals', function (Blueprint $table) {
            $table->index('schedule_id', 'idx_journals_schedule_id');
        });

        // LMS Enrollments indexes
        if (Schema::hasTable('sch_lms_enrollments')) {
            Schema::table('sch_lms_enrollments', function (Blueprint $table) {
                $table->index('course_id', 'idx_enrollments_course_id');
                $table->index('student_id', 'idx_enrollments_student_id');
                $table->index(['course_id', 'student_id'], 'idx_enrollments_course_student');
            });
        }

        // Academic Years indexes
        Schema::table('sch_acad_years', function (Blueprint $table) {
            $table->index('school_id', 'idx_years_school_id');
            $table->index('is_active', 'idx_years_is_active');
        });

        // Exam Results indexes
        if (Schema::hasTable('sch_lms_results')) {
            Schema::table('sch_lms_results', function (Blueprint $table) {
                $table->index('exam_id', 'idx_results_exam_id');
                $table->index('student_id', 'idx_results_student_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexDrops = [
            'sch_std_students' => ['idx_students_school_id', 'idx_students_school_level_id', 'idx_students_department_id', 'idx_students_school_level', 'idx_students_status', 'idx_students_school_status', 'idx_students_user_id'],
            'sch_hr_staff' => ['idx_staff_school_id', 'idx_staff_ptk_type', 'idx_staff_employment_status'],
            'sch_fin_bills' => ['idx_bills_school_id', 'idx_bills_student_id', 'idx_bills_status', 'idx_bills_unique_check', 'idx_bills_school_status'],
            'sch_fin_transactions' => ['idx_transactions_bill_id', 'idx_transactions_payment_date', 'idx_transactions_bill_date'],
            'sch_acad_schedules' => ['idx_schedules_staff_id', 'idx_schedules_study_group_id', 'idx_schedules_day', 'idx_schedules_staff_day', 'idx_schedules_group_day', 'idx_schedules_room_day'],
            'sch_acad_attendances' => ['idx_attendances_student_id', 'idx_attendances_date', 'idx_attendances_status', 'idx_attendances_student_date'],
            'sch_fin_expenses' => ['idx_expenses_school_id', 'idx_expenses_category', 'idx_expenses_date'],
            'sch_acad_teaching_journals' => ['idx_journals_schedule_id'],
            'sch_lms_enrollments' => ['idx_enrollments_course_id', 'idx_enrollments_student_id', 'idx_enrollments_course_student'],
            'sch_acad_years' => ['idx_years_school_id', 'idx_years_is_active'],
            'sch_lms_results' => ['idx_results_exam_id', 'idx_results_student_id'],
        ];

        foreach ($indexDrops as $table => $indexes) {
            if (Schema::hasTable($table)) {
                foreach ($indexes as $index) {
                    try {
                        Schema::table($table, function (Blueprint $table) use ($index) {
                            $table->dropIndex($index);
                        });
                    } catch (\Exception $e) {
                        // Index may not exist, skip
                    }
                }
            }
        }
    }
};
