<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create grades table for per-subject per-semester student scoring.
     */
    public function up(): void
    {
        Schema::create('sch_acad_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->foreignId('school_level_id')->nullable()->constrained('sch_ins_levels')->onDelete('set null');
            $table->foreignId('student_id')->constrained('sch_std_students')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('sch_acad_subjects')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('sch_acad_years')->onDelete('cascade');
            $table->foreignId('semester_id')->nullable()->constrained('sch_acad_semesters')->onDelete('set null');
            $table->foreignId('study_group_id')->nullable()->constrained('sch_acad_study_groups')->onDelete('set null');
            $table->foreignId('staff_id')->nullable()->constrained('sch_hr_staff')->onDelete('set null');

            // Knowledge Score (Pengetahuan)
            $table->decimal('daily_score', 5, 2)->nullable()->comment('Nilai Harian');
            $table->decimal('mid_score', 5, 2)->nullable()->comment('Nilai UTS/PTS');
            $table->decimal('final_score', 5, 2)->nullable()->comment('Nilai UAS/PAS');
            $table->decimal('knowledge_score', 5, 2)->nullable()->comment('Nilai Akhir Pengetahuan');

            // Skill Score (Keterampilan)
            $table->decimal('practice_score', 5, 2)->nullable()->comment('Nilai Praktik');
            $table->decimal('project_score', 5, 2)->nullable()->comment('Nilai Proyek');
            $table->decimal('portfolio_score', 5, 2)->nullable()->comment('Nilai Portofolio');
            $table->decimal('skill_score', 5, 2)->nullable()->comment('Nilai Akhir Keterampilan');

            // Final
            $table->decimal('final_grade', 5, 2)->nullable()->comment('Nilai Rapor');
            $table->string('grade_letter', 2)->nullable()->comment('A/B/C/D/E');
            $table->string('predicate')->nullable()->comment('Sangat Baik/Baik/Cukup/Kurang');
            $table->text('description')->nullable()->comment('Deskripsi capaian');

            $table->timestamps();

            // Indexes for common queries
            $table->index(['student_id', 'academic_year_id', 'semester_id'], 'idx_grades_student_period');
            $table->index(['subject_id', 'academic_year_id'], 'idx_grades_subject_year');
            $table->index(['school_id', 'academic_year_id'], 'idx_grades_school_year');

            // Unique constraint: one grade per student per subject per semester
            $table->unique(
                ['student_id', 'subject_id', 'academic_year_id', 'semester_id'],
                'uniq_grade_student_subject_semester'
            );
        });

        // Class history: track student class changes across years
        Schema::create('sch_acad_class_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('sch_std_students')->onDelete('cascade');
            $table->foreignId('study_group_id')->constrained('sch_acad_study_groups')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('sch_acad_years')->onDelete('cascade');
            $table->enum('status', ['active', 'promoted', 'retained', 'transferred', 'graduated', 'dropped_out'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['student_id', 'academic_year_id'],
                'uniq_class_history_student_year'
            );
            $table->index(['study_group_id', 'academic_year_id'], 'idx_class_history_group_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_acad_class_histories');
        Schema::dropIfExists('sch_acad_grades');
    }
};
