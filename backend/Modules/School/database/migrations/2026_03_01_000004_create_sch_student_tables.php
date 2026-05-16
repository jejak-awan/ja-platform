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
        // 1. Students
        Schema::create('sch_std_students', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('school_id')->onDelete('cascade');
            $table->uuid('workspace_id')->nullable()->onDelete('set null');
            $table->uuid('user_id')->nullable()->onDelete('set null');
            
            // Identity
            $table->string('nis', 20)->unique()->nullable();
            $table->string('nisn', 10)->unique()->nullable();
            $table->string('full_name');
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->string('religion')->nullable();
            
            // Address & Contact
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            
            // Parental Info
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('guardian_name')->nullable();
            
            // Status
            $table->string('status')->default('active'); // active, graduated, dropped_out, moved
            $table->date('entry_date')->nullable();
            
            // Flex data
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Achievements
        Schema::create('sch_std_achievements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('student_id')->onDelete('cascade');
            $table->string('title');
            $table->enum('level', ['school', 'district', 'province', 'national', 'international']);
            $table->string('rank')->nullable(); // e.g., Juara 1
            $table->date('date')->nullable();
            $table->string('certificate_path')->nullable();
            $table->timestamps();
        });

        // 3. Violations (Points system)
        Schema::create('sch_std_violations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('student_id')->onDelete('cascade');
            $table->string('category');
            $table->text('description');
            $table->integer('points')->default(0);
            $table->date('date');
            $table->uuid('reported_by_id')->nullable(); // Staff ID
            $table->timestamps();
        });

        // 5. Alumni (Basic)
        Schema::create('sch_std_alumni', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('student_id')->onDelete('cascade');
            $table->string('graduation_year');
            $table->string('current_activity')->nullable();
            $table->string('institution_name')->nullable();
            $table->timestamps();
        });

        // 6. Tracer Study (Alumni Tracking)
        Schema::create('sch_std_tracer_studies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('alumni_id')->onDelete('cascade');
            $table->string('employment_status'); // working, studying, entrepreneur, searching
            $table->string('company_name')->nullable();
            $table->string('position')->nullable();
            $table->string('university_name')->nullable();
            $table->string('major')->nullable();
            $table->string('salary_range')->nullable();
            $table->date('start_date')->nullable();
            $table->boolean('is_relevant_to_major')->default(true);
            $table->timestamps();
        });

        // 7. Counseling Records (BK)
        Schema::create('sch_std_counseling_records', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('school_id')->onDelete('cascade');
            $table->uuid('workspace_id')->onDelete('cascade');
            $table->uuid('student_id')->onDelete('cascade');
            $table->uuid('staff_id');
            $table->date('date');
            $table->string('type'); // academic, behavior, personal
            $table->text('problem');
            $table->text('solution')->nullable();
            $table->string('status')->default('ongoing'); // ongoing, resolved
            $table->timestamps();
        });

        // 8. Graduation Results
        Schema::create('sch_std_graduation_results', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('student_id')->onDelete('cascade');
            $table->string('graduation_year');
            $table->string('certificate_number')->nullable();
            $table->decimal('average_grade', 5, 2)->nullable();
            $table->string('status')->default('passed'); // passed, failed
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 9. Study Group Members (Pivot)
        Schema::create('sch_acad_study_group_members', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('study_group_id')->onDelete('cascade');
            $table->uuid('student_id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_acad_study_group_members');
        Schema::dropIfExists('sch_std_alumni');
        Schema::dropIfExists('sch_std_graduation_results');
        Schema::dropIfExists('sch_std_tracer_studies');
        Schema::dropIfExists('sch_std_counseling_records');
        Schema::dropIfExists('sch_std_violations');
        Schema::dropIfExists('sch_std_achievements');
        Schema::dropIfExists('sch_std_students');
    }
};
