<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // PostgreSQL specific: Drop the check constraint created by the enum
        // If the table was created with $table->enum(), Laravel/PostgreSQL
        // creates a check constraint with the name {table}_{column}_check
        if (config('database.default') === 'pgsql') {
            DB::statement('ALTER TABLE schools DROP CONSTRAINT IF EXISTS schools_type_check');
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->string('type')->default('swasta')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            // Re-adding as enum is hard in pgsql via change(), let's just keep it string in reverse
            $table->string('type')->default('swasta')->change();
        });
    }
};
