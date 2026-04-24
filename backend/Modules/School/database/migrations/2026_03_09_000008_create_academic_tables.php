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
        // Subjects
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('code')->unique()->nullable();
            $table->string('name');
            $table->string('group')->nullable(); // A, B, C
            $table->integer('kkm')->default(70);
            $table->timestamps();
        });

        // Study Groups (Rombongan Belajar)
        Schema::create('study_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_level_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('staff')->onDelete('set null');
            $table->string('name'); // e.g., X RPL 1
            $table->timestamps();
        });

        // Study Group Members (Students in Rombel)
        Schema::create('study_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_group_members');
        Schema::dropIfExists('study_groups');
        Schema::dropIfExists('subjects');
    }
};
