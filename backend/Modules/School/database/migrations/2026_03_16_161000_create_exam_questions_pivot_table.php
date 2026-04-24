<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sch_lms_exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('sch_lms_exams')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('sch_lms_questions')->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->decimal('points', 5, 2)->default(1.00);
            $table->timestamps();
            
            $table->unique(['exam_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sch_lms_exam_questions');
    }
};
