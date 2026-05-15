<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Media Folders
        Schema::create('srv_media_folders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('parent_id')->nullable()->index();
            $table->unsignedBigInteger('workspace_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::table('srv_media_folders', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('srv_media_folders')->onDelete('cascade');
        });

        // 2. Media Files
        Schema::create('srv_media_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('folder_id')->nullable()->index();
            $table->unsignedBigInteger('workspace_id')->nullable()->index();
            $table->unsignedBigInteger('author_id')->nullable()->index();
            $table->string('filename');
            $table->string('original_name');
            $table->string('mime_type')->index();
            $table->unsignedBigInteger('size');
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('extension', 10);
            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('folder_id')->references('id')->on('srv_media_folders')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('srv_media_files');
        Schema::dropIfExists('srv_media_folders');
    }
};
