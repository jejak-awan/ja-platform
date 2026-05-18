<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sch_acad_attendances', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('student_id')->cascadeOnDelete();
            $table->uuid('academic_year_id')->cascadeOnDelete();
            $table->uuid('semester_id')->cascadeOnDelete();
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
