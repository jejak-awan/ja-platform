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
        // 1. Academic Years
        Schema::create('sch_acad_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->foreignId('school_unit_id')->nullable()->constrained('sch_ins_levels')->onDelete('set null');
            $table->string('year'); // e.g., 2023/2024
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // 2. Semesters
        Schema::create('sch_acad_semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('sch_acad_years')->onDelete('cascade');
            $table->foreignId('school_unit_id')->nullable()->constrained('sch_ins_levels')->onDelete('set null');
            $table->enum('type', ['ganjil', 'genap']);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // 3. Subjects (Mata Pelajaran)
        Schema::create('sch_acad_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->foreignId('school_unit_id')->nullable()->constrained('sch_ins_levels')->onDelete('set null');
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('group')->nullable(); // A, B, C
            $table->integer('kkm')->default(70);
            $table->timestamps();
        });

        // 4. Study Groups (Rombongan Belajar / Kelas)
        Schema::create('sch_acad_study_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->foreignId('school_unit_id')->constrained('sch_ins_levels')->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained('sch_acad_departments')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('sch_acad_years')->onDelete('cascade');
            $table->string('name'); // e.g., X RPL 1
            $table->unsignedBigInteger('homeroom_teacher_id')->nullable(); // Will link to HR staff
            $table->timestamps();
        });

        // 5. Grades (Penilaian)
        Schema::create('sch_acad_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->foreignId('school_unit_id')->constrained('sch_ins_levels')->onDelete('cascade');
            $table->unsignedBigInteger('student_id'); // Will link to Students
            $table->foreignId('subject_id')->constrained('sch_acad_subjects')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('sch_acad_years')->onDelete('cascade');
            $table->foreignId('semester_id')->constrained('sch_acad_semesters')->onDelete('cascade');
            $table->foreignId('study_group_id')->constrained('sch_acad_study_groups')->onDelete('cascade');
            $table->unsignedBigInteger('staff_id'); // Teacher who gave the grade
            
            // Scores
            $table->float('daily_score')->nullable();
            $table->float('mid_score')->nullable();
            $table->float('final_score')->nullable();
            $table->float('knowledge_score')->nullable();
            $table->float('skill_score')->nullable();
            $table->float('final_grade')->nullable();
            
            $table->string('grade_letter', 2)->nullable();
            $table->string('predicate')->nullable();
            $table->text('description')->nullable();
            
            $table->timestamps();
        });

        // 6. Schedules
        Schema::create('sch_acad_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->foreignId('school_unit_id')->constrained('sch_ins_levels')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('sch_acad_years')->onDelete('cascade');
            $table->foreignId('semester_id')->constrained('sch_acad_semesters')->onDelete('cascade');
            $table->foreignId('study_group_id')->constrained('sch_acad_study_groups')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('sch_acad_subjects')->onDelete('cascade');
            $table->unsignedBigInteger('staff_id'); // Link to Staff
            $table->unsignedBigInteger('room_id')->nullable(); // Link to sch_log_rooms
            $table->string('day'); // Monday, Tuesday, ...
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. Teaching Journals
        Schema::create('sch_acad_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('sch_acad_schedules')->onDelete('cascade');
            $table->date('date');
            $table->text('topic');
            $table->text('activities');
            $table->text('notes')->nullable();
            $table->string('evidence_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_acad_journals');
        Schema::dropIfExists('sch_acad_schedules');
        Schema::dropIfExists('sch_acad_grades');
        Schema::dropIfExists('sch_acad_study_groups');
        Schema::dropIfExists('sch_acad_subjects');
        Schema::dropIfExists('sch_acad_semesters');
        Schema::dropIfExists('sch_acad_years');
    }
};
