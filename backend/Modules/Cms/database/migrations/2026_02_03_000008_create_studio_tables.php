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
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('author_id')->nullable()->constrained('core_users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Contents Table (Posts, Pages, Custom)
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
            $table->foreignId('author_id')->constrained('core_users')->onDelete('cascade');
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
            $table->foreignId('locked_by')->nullable()->constrained('core_users')->onDelete('set null');
            $table->timestamp('locked_at')->nullable();

            // Timestamps
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Content Tag Pivot (Uses tags table from Core)
        Schema::create('content_tag', function (Blueprint $table) {
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('core_tags')->onDelete('cascade');
            $table->primary(['content_id', 'tag_id']);
        });

        // 4. Content Revisions
        Schema::create('content_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
            $table->foreignId('author_id')->constrained('core_users')->onDelete('cascade');
            $table->string('title');
            $table->longText('body')->nullable();
            $table->json('meta')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        // 5. Comments Table
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('core_users')->onDelete('cascade');
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
        Schema::dropIfExists('content_revisions');
        Schema::dropIfExists('content_tag');
        Schema::dropIfExists('contents');
        Schema::dropIfExists('categories');
    }
};
