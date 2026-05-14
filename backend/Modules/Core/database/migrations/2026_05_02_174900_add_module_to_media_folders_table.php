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
        Schema::table('core_media_folders', function (Blueprint $table) {
            $table->string('module')->nullable()->default('cms')->index()->after('is_shared');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('core_media_folders', function (Blueprint $table) {
            $table->dropColumn('module');
        });
    }
};
