<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Categories
        Schema::create('cms_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('image')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['workspace_id', 'slug']);
        });

        // 2. Tags
        Schema::create('cms_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->index();
            $table->string('type')->default('content');
            $table->timestamps();

            $table->unique(['workspace_id', 'slug', 'type']);
        });

        // 3. Contents (Posts, Pages, etc.)
        Schema::create('cms_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id')->nullable()->index();
            $table->unsignedBigInteger('author_id')->index();
            $table->string('title');
            $table->string('slug')->index();
            $table->text('excerpt')->nullable();
            $table->text('intro')->nullable();
            $table->longText('body')->nullable();
            $table->string('type')->default('post')->index(); // post, page, product
            $table->string('status')->default('draft')->index(); // draft, published, scheduled
            $table->string('featured_image')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['workspace_id', 'slug', 'type']);
        });

        // 4. Content-Category Pivot
        Schema::create('cms_content_category', function (Blueprint $table) {
            $table->foreignId('content_id')->constrained('cms_contents')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('cms_categories')->onDelete('cascade');
            $table->primary(['content_id', 'category_id']);
        });

        // 5. Content-Tag Pivot
        Schema::create('cms_content_tag', function (Blueprint $table) {
            $table->foreignId('content_id')->constrained('cms_contents')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('cms_tags')->onDelete('cascade');
            $table->primary(['content_id', 'tag_id']);
        });

        // 6. Themes
        Schema::create('cms_themes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('frontend');
            $table->string('path');
            $table->string('version')->nullable();
            $table->text('description')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // 7. Menus
        Schema::create('cms_menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id')->nullable()->index();
            $table->string('name');
            $table->string('location')->index();
            $table->json('items')->nullable();
            $table->timestamps();
        });

        // 8. Redirects
        Schema::create('cms_redirects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id')->nullable()->index();
            $table->string('from_url')->index();
            $table->string('to_url');
            $table->integer('status_code')->default(301);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_redirects');
        Schema::dropIfExists('cms_menus');
        Schema::dropIfExists('cms_themes');
        Schema::dropIfExists('cms_content_tag');
        Schema::dropIfExists('cms_content_category');
        Schema::dropIfExists('cms_contents');
        Schema::dropIfExists('cms_tags');
        Schema::dropIfExists('cms_categories');
    }
};
