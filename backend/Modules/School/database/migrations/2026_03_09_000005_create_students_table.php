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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_level_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');

            // Basic Info
            $table->string('nisn', 10)->unique()->nullable();
            $table->string('nis', 20)->nullable();
            $table->string('nik', 16)->nullable();
            $table->string('full_name');
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->string('religion')->nullable();

            // Address Detail
            $table->text('address')->nullable();
            $table->string('rt', 3)->nullable();
            $table->string('rw', 3)->nullable();
            $table->string('dusun')->nullable();
            $table->string('desa_kelurahan')->nullable();
            $table->string('kecamatan')->nullable();

            // Contact
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Parent Data - Father
            $table->string('father_name')->nullable();
            $table->string('father_nik', 16)->nullable();
            $table->year('father_birth_year')->nullable();
            $table->string('father_education')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('father_income')->nullable();

            // Parent Data - Mother
            $table->string('mother_name')->nullable();
            $table->string('mother_nik', 16)->nullable();
            $table->year('mother_birth_year')->nullable();
            $table->string('mother_education')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('mother_income')->nullable();

            // Guardian Data
            $table->string('guardian_name')->nullable();
            $table->string('guardian_nik', 16)->nullable();
            $table->year('guardian_birth_year')->nullable();
            $table->string('guardian_education')->nullable();
            $table->string('guardian_occupation')->nullable();
            $table->string('guardian_income')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
