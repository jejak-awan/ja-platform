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
        // For PostgreSQL, changing enum can be complex.
        // We often need to change it to string first or use raw SQL.
        Schema::table('schools', function (Blueprint $table) {
            $table->string('type')->default('swasta')->change();
            if (! Schema::hasColumn('schools', 'is_multi_level')) {
                $table->boolean('is_multi_level')->default(false)->after('type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('is_multi_level');
        });
    }
};
