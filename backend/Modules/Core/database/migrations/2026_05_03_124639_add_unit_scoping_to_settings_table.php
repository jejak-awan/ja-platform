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
        Schema::table('core_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('workspace_id')->nullable()->after('id')->index();
            
            // Handle key unique constraint
            // We need to know the index name. Laravel usually uses 'settings_key_unique'
            // But to be safe we can use a try-catch or check if we can just drop it by column
            $table->dropUnique(['key']);
            
            $table->unique(['key', 'workspace_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('core_settings', function (Blueprint $table) {
            $table->dropUnique(['key', 'workspace_id']);
            $table->dropColumn('workspace_id');
            
            $table->unique('key');
        });
    }
};
