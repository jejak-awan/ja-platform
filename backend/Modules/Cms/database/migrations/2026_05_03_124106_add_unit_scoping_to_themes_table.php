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
        Schema::table('themes', function (Blueprint $table) {
            // Add school_unit_id column
            // Keep CMS independent from School module schema: no FK constraint here.
            $table->unsignedBigInteger('school_unit_id')->nullable()->after('id')->index();
            
            // Drop old unique constraint on slug
            $table->dropUnique(['slug']);
            
            // Add new composite unique constraint
            // This allows multiple units to have the same theme but with different settings/activation status
            $table->unique(['slug', 'school_unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropUnique(['slug', 'school_unit_id']);
            $table->dropColumn('school_unit_id');
            
            // Re-add original unique constraint
            $table->unique('slug');
        });
    }
};
