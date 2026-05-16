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
        // 1. Enrollments (Pendaftaran)
        Schema::create('sch_adm_enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('school_id')->onDelete('cascade');
            $table->uuid('workspace_id')->onDelete('cascade');
            $table->string('registration_number')->unique();
            $table->string('full_name');
            $table->enum('gender', ['L', 'P']);
            $table->string('prev_school')->nullable(); // Asal Sekolah
            $table->string('status')->default('pending'); // pending, verified, rejected, accepted
            $table->json('form_data')->nullable(); // Dynamic form response
            $table->timestamps();
        });

        // 2. Documents
        Schema::create('sch_adm_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('enrollment_id')->onDelete('cascade');
            $table->string('document_type'); // KK, Ijazah, Akta
            $table->string('file_path');
            $table->string('verification_status')->default('pending');
            $table->timestamps();
        });

        // 3. Verifications
        Schema::create('sch_adm_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('enrollment_id')->onDelete('cascade');
            $table->uuid('verifier_id'); // User ID
            $table->text('note')->nullable();
            $table->enum('decision', ['accepted', 'rejected']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_adm_verifications');
        Schema::dropIfExists('sch_adm_documents');
        Schema::dropIfExists('sch_adm_enrollments');
    }
};
