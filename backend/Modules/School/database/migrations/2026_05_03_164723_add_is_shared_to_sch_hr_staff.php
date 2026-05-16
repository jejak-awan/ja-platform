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
        Schema::table('sch_hr_staff', function (Blueprint $table): void {
            $table->boolean('is_shared')->default(false)->after('workspace_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sch_hr_staff', function (Blueprint $table): void {
            $table->dropColumn('is_shared');
        });
    }
};
