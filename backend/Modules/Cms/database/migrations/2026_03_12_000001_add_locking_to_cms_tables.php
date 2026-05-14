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
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('locked_by')->nullable()->constrained('core_users')->onDelete('set null');
            $table->timestamp('locked_at')->nullable();
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->foreignId('locked_by')->nullable()->constrained('core_users')->onDelete('set null');
            $table->timestamp('locked_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('locked_by');
            $table->dropColumn('locked_at');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('locked_by');
            $table->dropColumn('locked_at');
        });
    }
};
