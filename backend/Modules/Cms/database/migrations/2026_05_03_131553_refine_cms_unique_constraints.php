<?php
/**
 * Refine CMS Unique Constraints for Multi-Unit Support.
 * 
 * This migration drops the standard unique index on 'slug' for CMS tables
 * and replaces it with a composite unique index that includes 'school_unit_id'.
 * This allows multiple units to have identical slugs (e.g., 'uncategorized' or 'home').
 *
 * @author Antigravity
 */

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
        $slugTables = ['contents', 'categories', 'menus', 'forms'];

        foreach ($slugTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // Drop existing unique index
                // Note: Laravel usually names it 'table_column_unique'
                $table->dropUnique($tableName . '_slug_unique');
                
                // Add composite unique index
                $table->unique(['slug', 'school_unit_id'], $tableName . '_slug_unit_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $slugTables = ['contents', 'categories', 'menus', 'forms'];

        foreach ($slugTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropUnique($tableName . '_slug_unit_unique');
                $table->unique('slug', $tableName . '_slug_unique');
            });
        }
    }
};
