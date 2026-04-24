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
        $tables = [
            'academic_years',
            'semesters',
            'subjects',
            'staff',
            'uks_visits',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (! Schema::hasColumn($tableName, 'school_level_id')) {
                        $table->foreignId('school_level_id')->nullable()->after('school_id')->constrained()->onDelete('set null');
                    }
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
            'academic_years',
            'semesters',
            'subjects',
            'staff',
            'uks_visits',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->dropForeign([$tableName.'_school_level_id_foreign']);
                    $table->dropColumn('school_level_id');
                });
            }
        }
    }
};
