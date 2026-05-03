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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->string('registration_number')->unique();
            $table->string('full_name');
            $table->string('gender')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nisn')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('previous_school')->nullable();
            $table->enum('status', ['draft', 'applied', 'verified', 'exam', 'admitted', 'rejected'])->default('draft');
            $table->foreignId('admitted_student_id')->nullable()->constrained('students')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('enrollment_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('file_path')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_documents');
        Schema::dropIfExists('enrollments');
    }
};
