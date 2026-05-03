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
        Schema::create('maintenance_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_asset_id')->constrained('school_assets')->onDelete('cascade');
            $table->foreignId('reported_by')->constrained('staff')->onDelete('cascade');
            $table->date('date_reported');
            $table->text('issue_description');
            $table->string('priority')->default('Medium'); // Low, Medium, High
            $table->string('status')->default('Open'); // Open, In Progress, Resolved
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
        Schema::dropIfExists('maintenance_tickets');
    }
};
