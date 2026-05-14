<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sch_acad_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('sch_ins_levels')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('sch_std_students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('sch_acad_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('sch_acad_semesters')->cascadeOnDelete();
            $table->date('date');
            $table->string('status'); // hadir, izin, sakit, alfa, etc.
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'date']);
            $table->index(['academic_year_id', 'semester_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sch_acad_attendances');
    }
};

