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
            'contents',
            'categories',
            'menus',
            'forms',
            'widgets',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('school_unit_id')->nullable()->after('id')->constrained('sch_ins_levels')->onDelete('cascade');
                $table->index('school_unit_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'contents',
            'categories',
            'menus',
            'forms',
            'widgets',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign([$tableName . '_school_unit_id_foreign']);
                $table->dropColumn('school_unit_id');
            });
        }
    }
};
