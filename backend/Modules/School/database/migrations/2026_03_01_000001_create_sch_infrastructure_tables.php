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
        // 1. Schools (Institutional Identity)
        Schema::create('sch_ins_schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('npsn', 10)->unique()->nullable();
            $table->enum('type', ['negeri', 'swasta'])->default('negeri');
            $table->string('status_kepemilikan')->nullable();
            $table->boolean('is_multi_unit')->default(false);
            $table->boolean('is_multi_branch')->default(false);
            
            // Contact & Legality
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('principal_name')->nullable();
            $table->string('principal_nip')->nullable();
            
            // Advanced Details (Legality)
            $table->string('sk_pendirian')->nullable();
            $table->date('tgl_sk_pendirian')->nullable();
            $table->string('sk_operasional')->nullable();
            $table->date('tgl_sk_operasional')->nullable();
            
            // Branding & Profile
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('org_structure_path')->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->text('history')->nullable();
            
            // Foundation Info (For Swasta)
            $table->string('nama_yayasan')->nullable();
            $table->string('akta_pendirian_yayasan')->nullable();
            $table->date('tgl_akta_pendirian_yayasan')->nullable();
            $table->string('sk_kemenkumham')->nullable();
            $table->date('tgl_sk_kemenkumham')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. School Units (Jenjang / Levels)
        Schema::create('sch_ins_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->string('level'); // e.g., SMK, SMA, SMP
            $table->string('name'); // e.g., SMK Negeri 1 Cijulang
            $table->string('type')->nullable(); // Vocational, General, etc.
            $table->string('npsn', 10)->nullable();
            $table->string('accreditation')->nullable();
            $table->jsonb('settings')->nullable();
            $table->timestamps();
        });

        // 3. Departments (Jurusan)
        Schema::create('sch_acad_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_unit_id')->constrained('sch_ins_levels')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('head_of_department')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_acad_departments');
        Schema::dropIfExists('sch_ins_levels');
        Schema::dropIfExists('sch_ins_schools');
    }
};
