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
        // 1. Staff (Employees / Teachers)
        Schema::create('sch_hr_staff', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('school_id')->onDelete('cascade');
            $table->uuid('workspace_id')->nullable()->onDelete('set null');
            $table->uuid('user_id')->nullable()->onDelete('set null');
            
            // Basic Info
            $table->string('nuptk', 16)->unique()->nullable();
            $table->string('nik', 16)->nullable();
            $table->string('full_name');
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->string('religion')->nullable();
            
            // Employment details
            $table->string('employment_status')->nullable(); // PNS, PPPK, GTY, Honorer
            $table->string('ptk_type')->nullable(); // Guru Mapel, Guru Kelas, Tendik
            $table->string('sk_pengangkatan')->nullable();
            $table->date('tmt_pengangkatan')->nullable();
            $table->string('sk_penugasan')->nullable();
            $table->date('tmt_penugasan')->nullable();
            
            // Education
            $table->string('last_education')->nullable();
            $table->string('major')->nullable();
            $table->boolean('certification_status')->default(false);
            
            // Flex data
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Staff Attendance
        Schema::create('sch_hr_attendances', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('staff_id')->onDelete('cascade');
            $table->date('date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->string('status')->default('present'); // present, sick, leave, alpha
            $table->text('note')->nullable();
            $table->string('location_lat')->nullable();
            $table->string('location_lng')->nullable();
            $table->timestamps();
        });

        // 3. Salary Structures
        Schema::create('sch_hr_salary_structures', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('school_id')->onDelete('cascade');
            $table->string('name'); // e.g., Guru GTY Gol III
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->json('allowances')->nullable(); // Tunjangan (JSONB)
            $table->json('deductions')->nullable(); // Potongan (JSONB)
            $table->timestamps();
        });

        // 4. Payrolls
        Schema::create('sch_hr_payrolls', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('staff_id')->onDelete('cascade');
            $table->string('month', 2);
            $table->string('year', 4);
            $table->decimal('total_allowances', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);
            $table->string('status')->default('pending'); // pending, paid
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // 5. Staff Shifts
        Schema::create('sch_hr_shifts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('school_id')->onDelete('cascade');
            $table->string('name');
            $table->time('clock_in');
            $table->time('clock_out');
            $table->json('working_days')->nullable(); // [1,2,3,4,5]
            $table->timestamps();
        });

        // 6. Leave Requests
        Schema::create('sch_hr_leaves', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('school_id')->onDelete('cascade');
            $table->uuid('staff_id')->onDelete('cascade');
            $table->string('type');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
        });

        // 7. Job Vacancies
        Schema::create('sch_hr_job_vacancies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('school_id')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('type')->default('full-time'); // full-time, part-time, contract
            $table->date('deadline')->nullable();
            $table->string('status')->default('open'); // open, closed
            $table->timestamps();
            $table->softDeletes();
        });

        // 8. Job Applications
        Schema::create('sch_hr_job_applications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('vacancy_id')->onDelete('cascade');
            $table->string('applicant_name');
            $table->string('applicant_email');
            $table->string('applicant_phone')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('status')->default('pending'); // pending, reviewed, interviewed, rejected, hired
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_hr_job_applications');
        Schema::dropIfExists('sch_hr_job_vacancies');
        Schema::dropIfExists('sch_hr_leaves');
        Schema::dropIfExists('sch_hr_shifts');
        Schema::dropIfExists('sch_hr_payrolls');
        Schema::dropIfExists('sch_hr_salary_structures');
        Schema::dropIfExists('sch_hr_attendances');
        Schema::dropIfExists('sch_hr_staff');
    }
};
