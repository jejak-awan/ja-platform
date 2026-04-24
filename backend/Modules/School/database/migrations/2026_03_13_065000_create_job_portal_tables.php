<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->string('position');
            $table->text('description');
            $table->json('requirements')->nullable();
            $table->string('location')->nullable();
            $table->string('salary_range')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->date('deadline')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained('job_vacancies')->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('resume_path')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'accepted', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_vacancies');
    }
};
