<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only run for Postgres
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $tables = [
            // School Module
            'sch_ins_levels',
            'sch_acad_years',
            'sch_acad_semesters',
            'sch_acad_subjects',
            'sch_acad_departments',
            'sch_acad_study_groups',
            'sch_acad_grades',
            'sch_acad_schedules',
            'sch_log_rooms',
            'students',
            'staff',
            // CMS Module
            'contents',
            'categories',
            'menus',
            'forms',
            'widgets',
            // Core Module
            'settings',
            'media',
            'media_folders',
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) continue;
            
            $columnsToFix = [];
            if (Schema::hasColumn($tableName, 'school_unit_id')) {
                $columnsToFix['school_unit_id'] = 'sch_ins_levels';
            }
            // Avoid circular or incorrect school_id references for school units
            if (Schema::hasColumn($tableName, 'school_id') && $tableName !== 'sch_ins_levels') {
                $columnsToFix['school_id'] = 'sch_ins_schools';
            }

            foreach ($columnsToFix as $column => $parentTable) {
                $constraintName = "{$tableName}_{$column}_foreign";

                // 1. Drop if exists
                DB::statement("ALTER TABLE \"{$tableName}\" DROP CONSTRAINT IF EXISTS \"{$constraintName}\"");
                
                // 2. Add with cascade
                DB::statement("ALTER TABLE \"{$tableName}\" ADD CONSTRAINT \"{$constraintName}\" FOREIGN KEY (\"{$column}\") REFERENCES \"{$parentTable}\" (\"id\") ON DELETE CASCADE");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
