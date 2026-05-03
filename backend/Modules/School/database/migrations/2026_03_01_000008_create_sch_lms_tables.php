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
        // 1. Courses
        Schema::create('sch_lms_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('status')->default('draft'); // draft, published, private
            $table->string('level')->default('beginner');
            $table->jsonb('metadata')->nullable();
            $table->unsignedBigInteger('author_id'); // User ID
            
            // Academic Context
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('semester_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('grade_id')->nullable();
            $table->boolean('is_global')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Sections & Lessons
        Schema::create('sch_lms_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('sch_lms_courses')->onDelete('cascade');
            $table->string('title');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 5. Lessons (Materi / Pertemuan)
        Schema::create('sch_lms_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('sch_lms_courses')->onDelete('cascade'); // Added back for easier query
            $table->foreignId('section_id')->constrained('sch_lms_sections')->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 3. Topics (Content)
        Schema::create('sch_lms_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('sch_lms_lessons')->onDelete('cascade');
            $table->string('title');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('preview')->default(false);
            $table->string('topicable_type');
            $table->unsignedBigInteger('topicable_id');
            $table->index(['topicable_type', 'topicable_id']);
            $table->timestamps();
        });

        // 4. Specific Content Tables
        Schema::create('sch_lms_topic_richtexts', function (Blueprint $table) {
            $table->id();
            $table->longText('value');
            $table->timestamps();
        });

        Schema::create('sch_lms_topic_videos', function (Blueprint $table) {
            $table->id();
            $table->string('value');
            $table->string('poster_path')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('duration')->nullable();
            $table->timestamps();
        });

        Schema::create('sch_lms_topic_pdfs', function (Blueprint $table) {
            $table->id();
            $table->string('value');
            $table->timestamps();
        });

        // 5. Enrollments & Progress
        Schema::create('sch_lms_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('sch_lms_courses')->onDelete('cascade');
            $table->unsignedBigInteger('student_id'); // Student ID
            $table->timestamp('enrolled_at')->useCurrent();
            $table->float('progress')->default(0); // 0-100
            $table->timestamps();
        });

        Schema::create('sch_lms_topic_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('sch_lms_topics')->onDelete('cascade');
            $table->unsignedBigInteger('student_id');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });

        // 5. Quizzes
        Schema::create('sch_lms_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('sch_lms_topics')->onDelete('cascade');
            $table->string('title');
            $table->integer('time_limit')->nullable(); // in minutes
            $table->integer('passing_score')->default(70);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_lms_quizzes');
        Schema::dropIfExists('sch_lms_topic_progress');
        Schema::dropIfExists('sch_lms_enrollments');
        Schema::dropIfExists('sch_lms_topic_pdfs');
        Schema::dropIfExists('sch_lms_topic_videos');
        Schema::dropIfExists('sch_lms_topic_richtexts');
        Schema::dropIfExists('sch_lms_topics');
        Schema::dropIfExists('sch_lms_lessons');
        Schema::dropIfExists('sch_lms_sections');
        Schema::dropIfExists('sch_lms_courses');
    }
};
