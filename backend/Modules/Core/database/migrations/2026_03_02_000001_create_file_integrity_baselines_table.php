<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_integrity_baselines', function (Blueprint $table) {
            $table->id();
            $table->string('file_path')->unique();
            $table->string('hash', 64); // SHA-256
            $table->unsignedBigInteger('file_size')->default(0);
            $table->enum('status', ['ok', 'modified', 'missing'])->default('ok')->index();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_integrity_baselines');
    }
};
