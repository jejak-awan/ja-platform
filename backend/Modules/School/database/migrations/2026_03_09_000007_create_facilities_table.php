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
        // Land Assets
        Schema::create('land_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('area', 10, 2)->nullable(); // in m2
            $table->string('ownership_status')->nullable();
            $table->string('certificate_info')->nullable();
            $table->timestamps();
        });

        // Buildings
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_asset_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('area', 10, 2)->nullable();
            $table->integer('floor_count')->default(1);
            $table->year('year_built')->nullable();
            $table->string('condition')->nullable(); // Baik, Rusak Ringan, Rusak Berat
            $table->timestamps();
        });

        // Rooms
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->onDelete('cascade');
            $table->string('type')->nullable(); // Kelas, Lab, Perpustakaan, etc.
            $table->string('name');
            $table->integer('capacity')->nullable();
            $table->string('condition')->nullable();
            $table->timestamps();
        });

        // Assets/Items
        Schema::create('school_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('category')->nullable(); // Electronics, Furniture, etc.
            $table->integer('quantity')->default(1);
            $table->string('condition')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_assets');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('buildings');
        Schema::dropIfExists('land_assets');
    }
};
