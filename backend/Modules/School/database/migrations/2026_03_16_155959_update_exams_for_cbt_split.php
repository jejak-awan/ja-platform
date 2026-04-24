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
        Schema::table('sch_lms_exams', function (Blueprint $table) {
            $table->enum('category', ['quiz', 'pts', 'uas', 'tryout'])->default('quiz')->after('subject_id');
            $table->enum('type', ['self_paced', 'scheduled'])->default('self_paced')->after('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sch_lms_exams', function (Blueprint $table) {
            $table->dropColumn(['category', 'type']);
        });
    }
};
