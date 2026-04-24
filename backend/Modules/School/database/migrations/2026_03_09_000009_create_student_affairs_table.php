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
        // Attendance
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['H', 'I', 'S', 'A']); // Hadir, Izin, Sakit, Alpa
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });

        // Violations
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('category')->nullable();
            $table->integer('points')->default(0);
            $table->date('date');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Achievements
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('level')->nullable(); // Sekolah, Kab, Prov, Nasional, Int
            $table->string('type')->nullable(); // Akademik, Non-Akademik
            $table->date('date');
            $table->string('evidence_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('violations');
        Schema::dropIfExists('attendances');
    }
};
