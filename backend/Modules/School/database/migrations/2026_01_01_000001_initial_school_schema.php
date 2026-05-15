<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Institution
        Schema::create('sch_ins_schools', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id')->nullable()->index();
            $table->string('name');
            $table->string('npsn')->unique()->nullable();
            $table->string('address')->nullable();
            $table->string('logo')->nullable();
            $table->json('settings')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('sch_ins_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->index();
            $table->string('level');
            $table->string('name');
            $table->string('type');
            $table->string('npsn')->nullable();
            $table->string('domain')->nullable()->index();
            $table->string('subdomain')->nullable()->index();
            $table->string('kurikulum')->nullable();
            $table->string('accreditation')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('sch_ins_schools')->onDelete('cascade');
        });

        // 2. Academic
        Schema::create('sch_acad_years', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->index();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('sch_ins_schools')->onDelete('cascade');
        });

        Schema::create('sch_acad_semesters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->index();
            $table->unsignedBigInteger('academic_year_id')->index();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('sch_ins_schools')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('sch_acad_years')->onDelete('cascade');
        });

        Schema::create('sch_acad_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->index();
            $table->string('code')->nullable();
            $table->string('name');
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('sch_ins_schools')->onDelete('cascade');
        });

        Schema::create('sch_acad_study_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->index();
            $table->unsignedBigInteger('academic_year_id')->index();
            $table->string('name');
            $table->string('level');
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('sch_ins_schools')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('sch_acad_years')->onDelete('cascade');
        });

        // 3. HR
        Schema::create('sch_hr_staff', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->index();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('nip')->unique()->nullable();
            $table->string('role_type')->default('teacher'); // teacher, staff, etc
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('sch_ins_schools')->onDelete('cascade');
        });

        // 4. Students
        Schema::create('sch_std_students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->index();
            $table->unsignedBigInteger('user_id')->unique();
            $table->unsignedBigInteger('study_group_id')->nullable();
            $table->string('nisn')->unique()->nullable();
            $table->string('nis')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('sch_ins_schools')->onDelete('cascade');
            $table->foreign('study_group_id')->references('id')->on('sch_acad_study_groups')->onDelete('set null');
        });

        // 5. Attendance
        Schema::create('sch_acad_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->index();
            $table->date('date');
            $table->string('status'); // present, sick, leave, absent
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('sch_std_students')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sch_acad_attendances');
        Schema::dropIfExists('sch_std_students');
        Schema::dropIfExists('sch_hr_staff');
        Schema::dropIfExists('sch_acad_study_groups');
        Schema::dropIfExists('sch_acad_subjects');
        Schema::dropIfExists('sch_acad_years');
        Schema::dropIfExists('sch_ins_levels');
        Schema::dropIfExists('sch_ins_schools');
    }
};
