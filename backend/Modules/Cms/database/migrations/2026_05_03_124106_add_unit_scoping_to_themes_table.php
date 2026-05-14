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
            // Add workspace_id column
            // Keep CMS independent from School module schema: no FK constraint here.
            $table->unsignedBigInteger('workspace_id')->nullable()->after('id')->index();
            
            // Drop old unique constraint on slug
            $table->dropUnique(['slug']);
            
            // Add new composite unique constraint
            // This allows multiple units to have the same theme but with different settings/activation status
            $table->unique(['slug', 'workspace_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropUnique(['slug', 'workspace_id']);
            $table->dropColumn('workspace_id');
            
            // Re-add original unique constraint
            $table->unique('slug');
        });
    }
};
