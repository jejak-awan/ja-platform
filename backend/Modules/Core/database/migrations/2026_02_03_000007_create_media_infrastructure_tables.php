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
        // 1. Tags Table (Moved from CMS to Core for global use)
        Schema::create('core_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('core_users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Media Folders (Moved from CMS to Core)
        Schema::create('core_media_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->foreignId('parent_id')->nullable()->constrained('core_media_folders')->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->foreignId('author_id')->nullable()->constrained('core_users')->nullOnDelete();
            $table->boolean('is_shared')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index('parent_id');
            $table->index('slug');
        });

        // 3. Media Table (Moved from CMS to Core)
        Schema::create('core_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->nullable()->constrained('core_media_folders')->onDelete('set null');
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->unsignedBigInteger('size')->default(0);
            $table->json('manipulations')->nullable();
            $table->json('custom_properties')->nullable();
            $table->json('responsive_images')->nullable();
            $table->unsignedBigInteger('order_column')->nullable();
            $table->string('alt')->nullable();
            $table->text('description')->nullable();
            $table->string('caption')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('core_users')->onDelete('set null');
            $table->boolean('is_shared')->default(false)->index();
            $table->string('module')->nullable()->index(); // Added for module scoping
            $table->timestamps();
            $table->softDeletes();

            $table->index('mime_type');
            $table->index('created_at');
        });

        // 4. Media Tag Pivot
        Schema::create('core_media_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('core_media')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('core_tags')->onDelete('cascade');
            $table->unique(['media_id', 'tag_id']);
            $table->timestamps();
        });

        // 5. Media Usage (Tracking where media is used)
        Schema::create('core_media_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('core_media')->onDelete('cascade');
            $table->morphs('model');
            $table->string('field_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core_media_usages');
        Schema::dropIfExists('core_media_tag');
        Schema::dropIfExists('core_media');
        Schema::dropIfExists('core_media_folders');
        Schema::dropIfExists('core_tags');
    }
};
