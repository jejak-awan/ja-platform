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
        Schema::table('school_units', function (Blueprint $table) {
            if (! Schema::hasColumn('school_units', 'type')) {
                $table->string('type')->default('smk')->after('school_id'); // sd, smp, sma, smk
            }
            if (! Schema::hasColumn('school_units', 'settings')) {
                $table->json('settings')->nullable()->after('name');
            }
            if (! Schema::hasColumn('school_units', 'npsn')) {
                $table->string('npsn', 8)->nullable()->after('type');
            }
            if (! Schema::hasColumn('school_units', 'accreditation')) {
                $table->string('accreditation')->nullable()->after('npsn');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_units', function (Blueprint $table) {
            $table->dropColumn(['type', 'settings', 'npsn', 'accreditation']);
        });
    }
};
