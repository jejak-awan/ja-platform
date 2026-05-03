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
        $tables = [
            'contents',
            'categories',
            'menus',
            'forms',
            'widgets',
            'settings',
            'media',
            'media_folders',
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) continue;
            
            if (Schema::hasColumn($tableName, 'school_unit_id')) {
                $column = 'school_unit_id';
                $parentTable = 'sch_ins_levels';
                $constraintName = "{$tableName}_{$column}_foreign";

                DB::statement("ALTER TABLE \"{$tableName}\" DROP CONSTRAINT IF EXISTS \"{$constraintName}\"");
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
