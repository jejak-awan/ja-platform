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
        Schema::table('settings', function (Blueprint $table) {
            $table->foreignId('school_unit_id')->nullable()->after('id')->constrained('sch_ins_levels')->onDelete('cascade');
            
            // Handle key unique constraint
            // We need to know the index name. Laravel usually uses 'settings_key_unique'
            // But to be safe we can use a try-catch or check if we can just drop it by column
            $table->dropUnique(['key']);
            
            $table->unique(['key', 'school_unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['key', 'school_unit_id']);
            $table->dropForeign(['school_unit_id']);
            $table->dropColumn('school_unit_id');
            
            $table->unique('key');
        });
    }
};
