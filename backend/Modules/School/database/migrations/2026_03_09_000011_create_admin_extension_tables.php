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
        // Library
        Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('isbn')->nullable();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('publisher')->nullable();
            $table->year('publish_year')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('location')->nullable(); // Shelf location
            $table->timestamps();
        });

        // UKS / Health Center
        Schema::create('uks_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->morphs('patient'); // Supports student or staff
            $table->string('complaint');
            $table->text('treatment')->nullable();
            $table->string('medicine_given')->nullable();
            $table->timestamps();
        });

        // Guest Book
        Schema::create('guest_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('institution')->nullable();
            $table->string('purpose');
            $table->string('phone')->nullable();
            $table->dateTime('visit_time');
            $table->timestamps();
        });

        // Alumni
        Schema::create('alumni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->year('graduation_year');
            $table->string('current_activity')->nullable(); // College, Work, etc.
            $table->string('institution_name')->nullable(); // Company or University
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni');
        Schema::dropIfExists('guest_logs');
        Schema::dropIfExists('uks_visits');
        Schema::dropIfExists('library_books');
    }
};
