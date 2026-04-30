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
        // 1. Topic Quiz Table (The container)
        Schema::create('sch_lms_topic_quizzes', function (Blueprint $table) {
            $table->id();
            $table->text('value')->nullable(); // Description/Instructions
            $table->integer('max_attempts')->default(0); // 0 = unlimited
            $table->integer('max_time')->nullable(); // minutes
            $table->integer('pass_score')->default(60); // percentage
            $table->timestamps();
        });

        // 2. Quiz Questions Table
        Schema::create('sch_lms_quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('sch_lms_topic_quizzes')->onDelete('cascade');
            $table->enum('type', ['multiple_choice', 'true_false', 'short_answer'])->default('multiple_choice');
            $table->text('value'); // The question content
            $table->integer('score')->default(1);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 3. Quiz Options Table (for multiple choice/true false)
        Schema::create('sch_lms_quiz_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('sch_lms_quiz_questions')->onDelete('cascade');
            $table->text('value');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        // 4. Quiz Attempts Table
        Schema::create('sch_lms_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('sch_lms_topic_quizzes')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('sch_std_students')->onDelete('cascade');
            $table->integer('score')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            $table->json('answers')->nullable(); // Detailed answers given
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_lms_quiz_attempts');
        Schema::dropIfExists('sch_lms_quiz_options');
        Schema::dropIfExists('sch_lms_quiz_questions');
        Schema::dropIfExists('sch_lms_topic_quizzes');
    }
};
