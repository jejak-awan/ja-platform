<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('school_level_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->string('category'); // e.g., Sarpras, HR, Operational
            $table->decimal('planned_amount', 15, 2);
            $table->decimal('actual_amount', 15, 2)->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('school_level_id')->references('id')->on('school_levels')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
