<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add soft deletes to critical tables that should never be permanently deleted.
     */
    public function up(): void
    {
        $tables = [
            'sch_ins_schools',
            'sch_std_students',
            'sch_hr_staff',
            'sch_acad_years',
            'sch_acad_semesters',

            'sch_acad_schedules',
            'sch_acad_study_groups',
            'sch_acad_subjects',
            'sch_acad_departments',
            'sch_ins_levels',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'sch_ins_schools',
            'sch_std_students',
            'sch_hr_staff',
            'sch_acad_years',
            'sch_acad_semesters',

            'sch_acad_schedules',
            'sch_acad_study_groups',
            'sch_acad_subjects',
            'sch_acad_departments',
            'sch_ins_levels',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
