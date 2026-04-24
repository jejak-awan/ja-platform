<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hostel_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('gender', ['male', 'female', 'mixed'])->default('mixed');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained('hostel_blocks')->onDelete('cascade');
            $table->string('room_number');
            $table->integer('capacity')->default(1);
            $table->json('features')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hostel_beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('hostel_rooms')->onDelete('cascade');
            $table->string('bed_number');
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        Schema::create('hostel_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('bed_id')->constrained('hostel_beds')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hostel_allocations');
        Schema::dropIfExists('hostel_beds');
        Schema::dropIfExists('hostel_rooms');
        Schema::dropIfExists('hostel_blocks');
    }
};
