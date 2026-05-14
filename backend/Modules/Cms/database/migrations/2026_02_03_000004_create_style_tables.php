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
        // 1. Themes Table
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('frontend')->index();
            $table->string('path')->nullable();
            $table->string('parent_theme')->nullable()->index();
            $table->string('version')->nullable();
            $table->string('author')->nullable();
            $table->string('author_url')->nullable();
            $table->string('license')->nullable();
            $table->string('requires_cms_version')->nullable();
            $table->string('update_url')->nullable();
            $table->text('description')->nullable();
            $table->string('preview_image')->nullable();
            $table->text('custom_css')->nullable();
            $table->json('settings')->nullable();
            $table->json('dependencies')->nullable();
            $table->json('supports')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('auto_update')->default(false);
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->timestamp('last_updated_at')->nullable();
            $table->softDeletes();

            $table->index(['type', 'is_active']);
        });

        // 2. Menus Table
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('location')->nullable()->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Menu Items Table
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->onDelete('cascade');
            $table->string('title');
            $table->string('url')->nullable();
            $table->string('type')->default('link'); // link, page, category, custom
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('target_type')->nullable();
            $table->string('icon')->nullable();
            $table->string('css_class')->nullable();
            $table->text('description')->nullable();
            $table->string('badge')->nullable();
            $table->string('badge_color')->default('primary');
            $table->string('image')->nullable();
            $table->string('image_size')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('open_in_new_tab')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('hide_label')->default(false);
            $table->string('heading')->nullable();
            $table->boolean('show_heading_line')->default(false);
            $table->string('mega_menu_layout')->nullable();
            $table->integer('mega_menu_column')->default(4);
            $table->boolean('mega_menu_show_dividers')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['menu_id', 'parent_id']);
            $table->index('sort_order');
        });

        // 4. Widgets Table
        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');
            $table->string('location')->nullable()->index();
            $table->text('content')->nullable();
            $table->json('settings')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['location', 'sort_order']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widgets');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('themes');
    }
};
