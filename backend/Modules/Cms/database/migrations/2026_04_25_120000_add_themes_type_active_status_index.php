<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Speeds up Theme::getActiveTheme (type + is_active + status).
     */
    public function up(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->index(['type', 'is_active', 'status'], 'themes_type_active_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropIndex('themes_type_active_status_idx');
        });
    }
};
