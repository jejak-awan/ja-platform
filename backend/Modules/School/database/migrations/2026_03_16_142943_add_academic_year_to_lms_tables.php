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
        Schema::table('sch_lms_courses', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable()->after('school_id')->constrained('sch_acad_years')->onDelete('set null');
        });

        Schema::table('sch_lms_enrollments', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable()->after('student_id')->constrained('sch_acad_years')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sch_lms_enrollments', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });

        Schema::table('sch_lms_courses', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
    }
};
