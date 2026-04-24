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
        // 1. Courses Table
        Schema::create('sch_lms_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('sch_acad_subjects')->onDelete('set null');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Sections Table (Chapters in a course)
        Schema::create('sch_lms_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('sch_lms_courses')->onDelete('cascade');
            $table->string('title');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Lessons Table
        Schema::create('sch_lms_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sch_lms_sections')->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
            $table->enum('type', ['video', 'text', 'quiz', 'assignment', 'file'])->default('text');
            $table->longText('content')->nullable(); // Markdown or HTML
            $table->string('video_url')->nullable();
            $table->string('file_path')->nullable();
            $table->foreignId('exam_id')->nullable()->constrained('sch_lms_exams')->onDelete('set null');
            $table->integer('duration')->nullable(); // in minutes
            $table->integer('sort_order')->default(0);
            $table->boolean('is_preview')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Enrollments Table
        Schema::create('sch_lms_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('sch_lms_courses')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('sch_std_students')->onDelete('cascade');
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('progress', 5, 2)->default(0);
            $table->string('status', 20)->default('active'); // active, completed, dropped
            $table->timestamps();

            $table->unique(['course_id', 'student_id']);
        });

        // 5. Lesson Progress Tracking
        Schema::create('sch_lms_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('sch_lms_enrollments')->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained('sch_lms_lessons')->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['enrollment_id', 'lesson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_lms_progress');
        Schema::dropIfExists('sch_lms_enrollments');
        Schema::dropIfExists('sch_lms_lessons');
        Schema::dropIfExists('sch_lms_sections');
        Schema::dropIfExists('sch_lms_courses');
    }
};
