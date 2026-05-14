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
        // 1. Visitors & Guest Logs
        Schema::create('sch_ops_visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->foreignId('workspace_id')->constrained('sch_ins_levels')->onDelete('cascade');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('organization')->nullable();
            $table->text('purpose')->nullable();
            $table->string('target_person')->nullable();
            $table->timestamp('check_in')->useCurrent();
            $table->timestamp('check_out')->nullable();
            $table->string('status')->default('checked_in'); // checked_in, checked_out
            $table->string('photo_path')->nullable();
            $table->string('id_card_photo_path')->nullable();
            $table->timestamps();
        });

        // 2. Health (UKS)
        Schema::create('sch_ops_uks_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id'); // Can be Student or Staff
            $table->string('patient_type'); // Morph
            $table->date('date');
            $table->text('complaint');
            $table->text('treatment');
            $table->string('status')->default('closed');
            $table->timestamps();
        });

        // 3. Library
        Schema::create('sch_ops_library_books', function (Blueprint $table) {
            $table->id();
            $table->string('isbn')->nullable();
            $table->string('title');
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->string('call_number')->nullable();
            $table->integer('stock')->default(0);
            $table->timestamps();
        });

        Schema::create('sch_ops_library_circulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('sch_ops_library_books')->onDelete('cascade');
            $table->unsignedBigInteger('member_id'); // Morph
            $table->string('member_type');
            $table->date('borrow_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->string('status')->default('borrowed'); // borrowed, returned, lost
            $table->timestamps();
        });

        // 4. OSIS (Student Organization)
        Schema::create('sch_ops_osis_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->foreignId('workspace_id')->constrained('sch_ins_levels')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('date');
            $table->decimal('budget', 15, 2)->default(0);
            $table->string('status')->default('planned');
            $table->timestamps();
        });

        // 5. Graduation Settings
        Schema::create('sch_grad_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('graduation_year')->unique();
            $table->boolean('is_open')->default(false);
            $table->timestamp('announcement_date')->nullable();
            $table->json('subjects')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
        });

        // 6. Graduation Results
        Schema::create('sch_grad_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('sch_std_students')->onDelete('cascade');
            $table->string('status');
            $table->integer('graduation_year');
            $table->json('grades')->nullable();
            $table->string('certificate_number')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        // 7. Document Templates
        Schema::create('sch_ops_document_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // skl, id_card, report
            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_ops_document_templates');
        Schema::dropIfExists('sch_grad_results');
        Schema::dropIfExists('sch_grad_settings');
        Schema::dropIfExists('sch_ops_osis_activities');
        Schema::dropIfExists('sch_ops_library_circulations');
        Schema::dropIfExists('sch_ops_library_books');
        Schema::dropIfExists('sch_ops_uks_visits');
        Schema::dropIfExists('sch_ops_visitors');
    }
};
