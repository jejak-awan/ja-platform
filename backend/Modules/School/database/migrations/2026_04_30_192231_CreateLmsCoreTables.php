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
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('video_url')->nullable(); // Intro video
            $table->decimal('base_price', 15, 2)->default(0);
            $table->decimal('discount_price', 15, 2)->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->json('metadata')->nullable();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Lessons Table
        Schema::create('sch_lms_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('sch_lms_courses')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('sch_lms_lessons')->onDelete('cascade');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Topics Table (The polymorphic hub)
        Schema::create('sch_lms_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('sch_lms_lessons')->onDelete('cascade');
            $table->string('title');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('preview')->default(false); // Can be viewed without enrollment
            
            // Polymorphic relation to specific content
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
            $table->string('value'); // URL or path
            $table->string('poster_path')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('duration')->nullable(); // in seconds
            $table->timestamps();
        });

        Schema::create('sch_lms_topic_pdfs', function (Blueprint $table) {
            $table->id();
            $table->string('value'); // Path to PDF
            $table->timestamps();
        });

        // 5. Enrollments & Progress
        Schema::create('sch_lms_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('sch_lms_courses')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('sch_std_students')->onDelete('cascade');
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('expired_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['course_id', 'student_id']);
        });

        Schema::create('sch_lms_topic_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('sch_lms_topics')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('sch_std_students')->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable(); // e.g. video watch time, quiz score
            $table->timestamps();

            $table->unique(['topic_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_lms_topic_progress');
        Schema::dropIfExists('sch_lms_enrollments');
        Schema::dropIfExists('sch_lms_topic_pdfs');
        Schema::dropIfExists('sch_lms_topic_videos');
        Schema::dropIfExists('sch_lms_topic_richtexts');
        Schema::dropIfExists('sch_lms_topics');
        Schema::dropIfExists('sch_lms_lessons');
        Schema::dropIfExists('sch_lms_courses');
    }
};
