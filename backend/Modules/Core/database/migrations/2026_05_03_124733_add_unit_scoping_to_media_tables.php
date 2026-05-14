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
        Schema::table('core_media', function (Blueprint $table) {
            $table->unsignedBigInteger('workspace_id')->nullable()->after('id')->index();
            $table->index(['workspace_id', 'is_shared']);
        });

        Schema::table('core_media_folders', function (Blueprint $table) {
            $table->unsignedBigInteger('workspace_id')->nullable()->after('id')->index();
            $table->index(['workspace_id', 'is_shared']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('core_media_folders', function (Blueprint $table) {
            $table->dropIndex(['workspace_id', 'is_shared']);
            $table->dropColumn(['workspace_id']);
        });

        Schema::table('core_media', function (Blueprint $table) {
            $table->dropIndex(['workspace_id', 'is_shared']);
            $table->dropColumn('workspace_id');
        });
    }
};
