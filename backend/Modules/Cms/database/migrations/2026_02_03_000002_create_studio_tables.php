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
        // 1. Categories Table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Tags Table
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Contents Table (Posts, Pages, Custom)
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type')->default('post')->index();
            $table->string('status')->default('draft')->index();
            $table->longText('body')->nullable();
            $table->text('excerpt')->nullable();
            $table->json('meta')->nullable();

            // Stats
            $table->integer('views')->default(0);
            $table->integer('share_count')->default(0);
            $table->integer('edit_count')->default(0);

            // Relationships
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');

            // Flags & Attributes
            $table->boolean('is_featured')->default(false)->index();
            $table->enum('comment_status', ['open', 'closed'])->default('open');

            // SEO Fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->string('featured_image')->nullable();

            // Locks
            $table->foreignId('locked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('locked_at')->nullable();

            // Timestamps
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Content Tag Pivot
        Schema::create('content_tag', function (Blueprint $table) {
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->primary(['content_id', 'tag_id']);
        });

        // 5. Content Revisions
        Schema::create('content_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->longText('body')->nullable();
            $table->json('meta')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        // 6. Media Folders
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->foreignId('parent_id')->nullable()->constrained('media_folders')->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_shared')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index('parent_id');
            $table->index('slug');
        });

        // 7. Media Table
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->nullable()->constrained('media_folders')->onDelete('set null');
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
            $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_shared')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index('mime_type');
            $table->index('created_at');
        });

        // 8. Media Tag Pivot
        Schema::create('media_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->unique(['media_id', 'tag_id']);
            $table->timestamps();
        });

        // 9. Media Usage (Tracking where media is used)
        Schema::create('media_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->onDelete('cascade');
            $table->morphs('model');
            $table->string('field_name')->nullable();
            $table->timestamps();
        });

        // 10. Comments Table
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->text('body');
            $table->string('status')->default('pending')->index(); // pending, approved, spam, trash
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('media_usage');
        Schema::dropIfExists('media_tag');
        Schema::dropIfExists('media');
        Schema::dropIfExists('media_folders');
        Schema::dropIfExists('content_revisions');
        Schema::dropIfExists('content_tag');
        Schema::dropIfExists('contents');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('categories');
    }
};
