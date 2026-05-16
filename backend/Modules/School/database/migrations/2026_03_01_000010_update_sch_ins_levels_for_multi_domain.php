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
        // 1. Update School Units (sch_ins_levels)
        Schema::table('sch_ins_levels', function (Blueprint $table): void {
            $table->string('domain')->nullable()->unique()->after('npsn');
            $table->string('subdomain')->nullable()->unique()->after('domain');
            $table->string('kurikulum')->nullable()->after('subdomain');
            $table->boolean('is_active')->default(true)->after('settings');
        });

        // 2. Cleanup School (sch_ins_schools) - Optional data migration
        // We keep 'kurikulum' in sch_ins_schools for now to avoid data loss, 
        // but we'll prioritize the one in sch_ins_levels.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sch_ins_levels', function (Blueprint $table): void {
            $table->dropColumn(['domain', 'subdomain', 'kurikulum', 'is_active']);
        });
    }
};
