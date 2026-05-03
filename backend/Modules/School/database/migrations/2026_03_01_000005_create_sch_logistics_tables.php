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
        // 1. Facilities (Land, Buildings, Rooms)
        Schema::create('sch_log_land', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->string('name');
            $table->decimal('area_m2', 10, 2);
            $table->string('certificate_number')->nullable();
            $table->timestamps();
        });

        Schema::create('sch_log_buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_asset_id')->constrained('sch_log_land')->onDelete('cascade');
            $table->string('name');
            $table->string('area')->nullable();
            $table->integer('floor_count')->default(1);
            $table->string('year_built')->nullable();
            $table->string('condition')->default('good');
            $table->timestamps();
        });

        Schema::create('sch_log_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('sch_log_buildings')->onDelete('cascade');
            $table->string('name');
            $table->string('type')->nullable(); // Class, Lab, Office
            $table->integer('capacity')->nullable();
            $table->timestamps();
        });

        // 2. Assets Base
        Schema::create('sch_log_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('sch_log_rooms')->onDelete('set null');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category');
            $table->string('condition')->default('good'); // good, damaged, repair
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->timestamps();
        });

        // 3. Inventory System
        Schema::create('sch_log_inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sch_log_inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('sch_log_inventory_categories')->onDelete('cascade');
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->string('unit')->default('pcs');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sch_log_inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('sch_log_inventory_items')->onDelete('cascade');
            $table->foreignId('student_id')->nullable()->constrained('sch_std_students')->onDelete('set null');
            $table->string('type'); // in, out, adjustment
            $table->integer('quantity');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Hostel (Asrama)
        Schema::create('sch_log_hostel_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->string('name');
            $table->enum('gender_type', ['L', 'P']);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sch_log_hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained('sch_log_hostel_blocks')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sch_log_hostel_beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('sch_log_hostel_rooms')->onDelete('cascade');
            $table->string('name');
            $table->string('status')->default('available'); // available, occupied, maintenance
            $table->timestamps();
        });

        Schema::create('sch_log_hostel_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bed_id')->constrained('sch_log_hostel_beds')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('sch_std_students')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Transport
        Schema::create('sch_log_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->string('plate_number')->unique();
            $table->string('model');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sch_log_transport_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('start_location');
            $table->string('end_location');
            $table->json('stops')->nullable();
            $table->decimal('fee', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sch_log_transport_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('sch_std_students')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('sch_log_vehicles')->onDelete('cascade');
            $table->foreignId('route_id')->constrained('sch_log_transport_routes')->onDelete('cascade');
            $table->string('pickup_point')->nullable();
            $table->string('status')->default('pending'); // pending, active, cancelled
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Maintenance
        Schema::create('sch_log_maintenance_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('sch_ins_schools')->onDelete('cascade');
            $table->foreignId('school_unit_id')->nullable()->constrained('sch_ins_levels')->onDelete('cascade');
            $table->foreignId('school_asset_id')->nullable()->constrained('sch_log_assets')->onDelete('cascade');
            $table->unsignedBigInteger('reported_by');
            $table->date('date_reported');
            $table->text('issue_description');
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->string('status')->default('open'); // open, in_progress, resolved, closed
            $table->text('resolution_notes')->nullable();
            $table->date('date_resolved')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_log_maintenance_tickets');
        Schema::dropIfExists('sch_log_transport_registrations');
        Schema::dropIfExists('sch_log_transport_routes');
        Schema::dropIfExists('sch_log_vehicles');
        Schema::dropIfExists('sch_log_hostel_allocations');
        Schema::dropIfExists('sch_log_hostel_beds');
        Schema::dropIfExists('sch_log_hostel_rooms');
        Schema::dropIfExists('sch_log_hostel_blocks');
        Schema::dropIfExists('sch_log_inventory_transactions');
        Schema::dropIfExists('sch_log_inventory_items');
        Schema::dropIfExists('sch_log_inventory_categories');
        Schema::dropIfExists('sch_log_rooms');
        Schema::dropIfExists('sch_log_buildings');
        Schema::dropIfExists('sch_log_land');
        Schema::dropIfExists('sch_log_assets');
    }
};
