<?php
/**
 * Antigravity: Consolidated OSIS Tables Migration (Level 9)
 */

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
        // 1. OSIS Work Programs (Proker)
        Schema::create('sch_osis_programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id')->nullable()->index();
            $table->uuid('school_id')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('budget', 15, 2)->default(0);
            $table->string('status')->default('planned'); // planned, active, completed, cancelled
            $table->timestamps();
        });

        // 2. OSIS Members & Structure
        Schema::create('sch_osis_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id')->nullable()->index();
            $table->uuid('school_id')->onDelete('cascade');
            $table->uuid('student_id')->onDelete('cascade');
            $table->string('position'); // President, Secretary, etc.
            $table->string('period'); // e.g. 2023/2024
            $table->timestamps();
        });

        // 3. OSIS Finances
        Schema::create('sch_osis_finances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id')->nullable()->index();
            $table->uuid('school_id')->onDelete('cascade');
            $table->uuid('program_id')->nullable()->onDelete('set null');
            $table->enum('type', ['income', 'expense']);
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->date('transaction_date');
            $table->timestamps();
        });

        // 4. Student Suggestion Box
        Schema::create('sch_osis_suggestions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id')->nullable()->index();
            $table->uuid('school_id')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->string('status')->default('pending'); // pending, reviewed, implemented
            $table->timestamps();
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
