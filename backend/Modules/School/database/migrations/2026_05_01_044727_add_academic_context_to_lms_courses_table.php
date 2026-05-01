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
            $table->foreignId('academic_year_id')->nullable()->constrained('sch_acad_years')->onDelete('set null');
            $table->foreignId('semester_id')->nullable()->constrained('sch_acad_semesters')->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained('sch_acad_departments')->onDelete('set null')->comment('Major / Jurusan');
            $table->foreignId('grade_id')->nullable()->constrained('sch_acad_grades')->onDelete('set null')->comment('Level / Tingkatan');
            
            // Add visibility for multi-school/yayasan context if needed
            $table->boolean('is_global')->default(false)->comment('If true, visible to all schools in the same organization');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sch_lms_courses', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['semester_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['grade_id']);
            
            $table->dropColumn([
                'academic_year_id',
                'semester_id',
                'department_id',
                'grade_id',
                'is_global'
            ]);
        });
    }
};
