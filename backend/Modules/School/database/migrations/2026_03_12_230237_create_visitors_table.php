<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('school_unit_id');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('institution')->nullable();
            $table->string('purpose');
            $table->string('target_person')->nullable(); // Who they want to meet
            $table->timestamp('check_in');
            $table->timestamp('check_out')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('id_card_photo_path')->nullable();
            $table->string('status')->default('checked_in'); // checked_in, checked_out
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('school_unit_id')->references('id')->on('school_units')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
