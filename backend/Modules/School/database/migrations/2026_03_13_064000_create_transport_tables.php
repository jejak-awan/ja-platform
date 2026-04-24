<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('plate_number')->unique();
            $table->string('model');
            $table->integer('capacity');
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vehicle_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('start_location');
            $table->string('end_location');
            $table->json('stops')->nullable();
            $table->decimal('fee', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('transport_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('route_id')->constrained('vehicle_routes')->onDelete('cascade');
            $table->string('pickup_point')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transport_registrations');
        Schema::dropIfExists('vehicle_routes');
        Schema::dropIfExists('vehicles');
    }
};
