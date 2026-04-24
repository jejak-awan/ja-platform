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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');

            // Basic Info
            $table->string('nuptk', 16)->unique()->nullable();
            $table->string('nik', 16)->nullable();
            $table->string('full_name');
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->string('religion')->nullable();

            // Employment
            $table->string('employment_status')->nullable(); // PNS, PPPK, GTY/PTY, Honorer
            $table->string('ptk_type')->nullable(); // Guru Mapel, Guru Kelas, Tendik
            $table->string('sk_pengangkatan')->nullable();
            $table->date('tmt_pengangkatan')->nullable();
            $table->string('sk_penugasan')->nullable();
            $table->date('tmt_penugasan')->nullable();

            // Education
            $table->string('last_education')->nullable();
            $table->string('major')->nullable();
            $table->boolean('certification_status')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
