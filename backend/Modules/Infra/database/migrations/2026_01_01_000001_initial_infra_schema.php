<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Backups Registry
        Schema::create('infra_backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('disk');
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->string('type')->default('full'); // full, database, files
            $table->string('status')->default('success');
            $table->text('error')->nullable();
            $table->timestamps();
        });

        // 2. Deleted Files (Recycle Bin)
        Schema::create('infra_deleted_files', function (Blueprint $table) {
            $table->id();
            $table->string('original_name');
            $table->string('original_path');
            $table->string('disk');
            $table->unsignedBigInteger('size');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // 3. Webhooks Registry
        Schema::create('infra_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('event')->index();
            $table->string('secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infra_webhooks');
        Schema::dropIfExists('infra_deleted_files');
        Schema::dropIfExists('infra_backups');
    }
};
