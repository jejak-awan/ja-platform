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
            $table->unsignedBigInteger('school_unit_id')->nullable()->after('id')->index();
            $table->index(['school_unit_id', 'is_shared']);
        });

        Schema::table('media_folders', function (Blueprint $table) {
            $table->unsignedBigInteger('school_unit_id')->nullable()->after('id')->index();
            $table->index(['school_unit_id', 'is_shared']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_folders', function (Blueprint $table) {
            $table->dropIndex(['school_unit_id', 'is_shared']);
            $table->dropColumn(['school_unit_id']);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['school_unit_id', 'is_shared']);
            $table->dropColumn('school_unit_id');
        });
    }
};
