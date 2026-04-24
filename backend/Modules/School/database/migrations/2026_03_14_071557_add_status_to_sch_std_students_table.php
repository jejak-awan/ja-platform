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
        Schema::table('sch_std_students', function (Blueprint $table) {
            $table->enum('status', ['active', 'graduated', 'dropped_out', 'transferred'])->default('active')->after('religion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sch_std_students', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
