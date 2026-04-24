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
        Schema::create('sch_cbt_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('sch_lms_exams')->onDelete('cascade');
            $table->foreignId('study_group_id')->constrained('sch_acad_study_groups')->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->boolean('is_active')->default(true);
            $table->string('token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_cbt_sessions');
    }
};
