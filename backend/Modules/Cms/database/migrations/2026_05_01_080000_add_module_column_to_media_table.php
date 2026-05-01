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
        Schema::table('media', function (Blueprint $table) {
            if (!Schema::hasColumn('media', 'module')) {
                $table->string('module')->default('cms')->after('id')->index();
            }
        });

        // Update existing records to 'cms'
        \Illuminate\Support\Facades\DB::table('media')->update(['module' => 'cms']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            if (Schema::hasColumn('media', 'module')) {
                $table->dropColumn('module');
            }
        });
    }
};
