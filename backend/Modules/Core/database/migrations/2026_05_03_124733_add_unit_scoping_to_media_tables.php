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
            $table->foreignId('school_unit_id')->nullable()->after('id')->constrained('sch_ins_levels')->onDelete('cascade');
            $table->index(['school_unit_id', 'is_shared']);
        });

        Schema::table('media_folders', function (Blueprint $table) {
            $table->foreignId('school_unit_id')->nullable()->after('id')->constrained('sch_ins_levels')->onDelete('cascade');
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
            $table->dropForeign(['school_unit_id']);
            $table->dropColumn(['school_unit_id']);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['school_unit_id', 'is_shared']);
            $table->dropForeign(['school_unit_id']);
            $table->dropColumn('school_unit_id');
        });
    }
};
