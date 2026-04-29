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
        Schema::create('sch_grad_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('sch_std_students')->onDelete('cascade');
            $table->string('status')->default('active'); // graduated, not_graduated, deferred
            $table->year('graduation_year');
            $table->json('grades')->nullable(); // Subject-based grades
            $table->string('certificate_number')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'graduation_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_grad_results');
    }
};
