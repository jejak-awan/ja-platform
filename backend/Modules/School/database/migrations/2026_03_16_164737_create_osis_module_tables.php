<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function run(): void
    {
        // OSIS Work Programs (Proker)
        Schema::create('sch_osis_programs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('school_id');
            $blueprint->string('name');
            $blueprint->text('description')->nullable();
            $blueprint->date('planned_date')->nullable();
            $blueprint->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $blueprint->decimal('estimated_budget', 15, 2)->default(0);
            $blueprint->timestamps();

            $blueprint->foreign('school_id')->references('id')->on('sch_ins_schools')->onDelete('cascade');
        });

        // OSIS Members & Structure
        Schema::create('sch_osis_members', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('school_id');
            $blueprint->unsignedBigInteger('student_id');
            $blueprint->string('position'); // e.g., Ketua, Sekretaris, Bendahara, Sekbid 1
            $blueprint->string('period'); // e.g., 2023/2024
            $blueprint->timestamps();

            $blueprint->foreign('school_id')->references('id')->on('sch_ins_schools')->onDelete('cascade');
            $blueprint->foreign('student_id')->references('id')->on('sch_std_students')->onDelete('cascade');
        });

        // OSIS Finances (Simplified tracking)
        Schema::create('sch_osis_finances', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('school_id');
            $blueprint->unsignedBigInteger('program_id')->nullable();
            $blueprint->enum('type', ['income', 'expense']);
            $blueprint->decimal('amount', 15, 2);
            $blueprint->string('description');
            $blueprint->date('transaction_date');
            $blueprint->timestamps();

            $blueprint->foreign('school_id')->references('id')->on('sch_ins_schools')->onDelete('cascade');
            $blueprint->foreign('program_id')->references('id')->on('sch_osis_programs')->onDelete('set null');
        });

        // Student Suggestion Box
        Schema::create('sch_osis_suggestions', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('school_id');
            $blueprint->unsignedBigInteger('student_id')->nullable(); // Optional anonymization
            $blueprint->string('subject');
            $blueprint->text('content');
            $blueprint->enum('status', ['pending', 'reviewed', 'actioned', 'rejected'])->default('pending');
            $blueprint->text('response')->nullable();
            $blueprint->timestamps();

            $blueprint->foreign('school_id')->references('id')->on('sch_ins_schools')->onDelete('cascade');
            $blueprint->foreign('student_id')->references('id')->on('sch_std_students')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_osis_suggestions');
        Schema::dropIfExists('sch_osis_finances');
        Schema::dropIfExists('sch_osis_members');
        Schema::dropIfExists('sch_osis_programs');
    }
};
