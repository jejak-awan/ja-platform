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
        // Question Banks / Topics
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Questions
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained()->onDelete('cascade');
            $table->text('content'); // Question text (HTML support)
            $table->enum('type', ['multiple_choice', 'essay', 'true_false'])->default('multiple_choice');
            $table->integer('level')->default(1); // Difficulty level
            $table->json('options')->nullable(); // For multiple choice: {A: text, B: text, ...}
            $table->string('answer')->nullable(); // Correct option key or keyword
            $table->string('media_path')->nullable(); // Image/Audio attachment
            $table->timestamps();
        });

        // Exams
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->integer('duration')->nullable(); // in minutes
            $table->integer('total_questions')->default(0);
            $table->integer('passing_grade')->default(70);
            $table->boolean('is_randomized')->default(false);
            $table->timestamps();
        });

        // Exam Target Study Groups
        Schema::create('exam_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('study_group_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Exam Results
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->decimal('score', 5, 2)->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->enum('status', ['ongoing', 'completed', 'cancelled'])->default('ongoing');
            $table->json('answers_snapshot')->nullable(); // Stores student answers
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_results');
        Schema::dropIfExists('exam_targets');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_banks');
    }
};
