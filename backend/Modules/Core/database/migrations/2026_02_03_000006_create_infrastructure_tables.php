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
        // 1. Settings Table
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index(['group', 'key']);
        });

        // 2. Redis Settings
        Schema::create('redis_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->string('group')->default('connection');
            $table->text('description')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();
        });

        // 3. Backups Table
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('type')->default('database'); // database, files, full
            $table->string('disk');
            $table->string('path');
            $table->unsignedBigInteger('size')->nullable();
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('password')->nullable();
            $table->timestamps();
        });

        // 4. Webhooks Table
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('event')->index();
            $table->string('method')->default('POST');
            $table->json('headers')->nullable();
            $table->json('payload_template')->nullable();
            $table->integer('timeout')->default(30);
            $table->integer('retry_count')->default(0);
            $table->integer('max_retries')->default(3);
            $table->boolean('is_active')->default(true)->index();
            $table->integer('success_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
        });

        // 5. Plugins Table
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('version')->nullable();
            $table->text('description')->nullable();
            $table->string('author')->nullable();
            $table->string('author_url')->nullable();
            $table->string('plugin_url')->nullable();
            $table->string('main_file');
            $table->boolean('is_active')->default(false);
            $table->json('settings')->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();
        });

        // 6. Custom Fields Infrastructure
        Schema::create('field_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('applies_to')->nullable();
            $table->json('conditions')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_group_id')->constrained('field_groups')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->index();
            $table->string('label');
            $table->string('type');
            $table->text('description')->nullable();
            $table->text('default_value')->nullable();
            $table->json('options')->nullable();
            $table->json('validation_rules')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_searchable')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('content_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
            $table->foreignId('custom_field_id')->constrained('custom_fields')->onDelete('cascade');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->index(['content_id', 'custom_field_id']);
        });

        // 7. Search Infrastructure
        Schema::create('search_indexes', function (Blueprint $table) {
            $table->id();
            $table->morphs('searchable');
            $table->string('title');
            $table->text('content');
            $table->text('excerpt')->nullable();
            $table->json('meta')->nullable();
            $table->string('url')->nullable();
            $table->string('type')->nullable();
            $table->integer('relevance_score')->default(0);
            $table->timestamps();
            $table->index('type');

            if (DB::getDriverName() === 'mysql') {
                $table->fullText(['title', 'content']);
            }
        });

        Schema::create('search_queries', function (Blueprint $table) {
            $table->id();
            $table->string('query')->index();
            $table->integer('results_count')->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('filters')->nullable();
            $table->timestamp('searched_at')->useCurrent();
            $table->timestamps();
        });

        // 8. Communications
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subject');
            $table->text('body');
            $table->text('text_body')->nullable();
            $table->json('variables')->nullable();
            $table->string('category')->default('general')->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->string('action_url')->nullable();
            $table->string('action_text')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'is_read']);
        });

        // 9. Content Templates
        Schema::create('content_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('type')->default('post');
            $table->text('title_template')->nullable();
            $table->text('body_template');
            $table->text('excerpt_template')->nullable();
            $table->json('default_fields')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('type');
        });

        // 10. System Maintenance
        Schema::create('scheduled_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('command');
            $table->string('schedule');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->text('output')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();
        });

        Schema::create('ip_lists', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');
            $table->enum('type', ['whitelist', 'blocklist'])->index();
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['ip_address', 'type']);
        });

        Schema::create('deleted_files', function (Blueprint $table) {
            $table->id();
            $table->string('original_path');
            $table->string('trash_path');
            $table->string('disk')->default('public');
            $table->string('name');
            $table->enum('type', ['file', 'folder'])->default('file');
            $table->bigInteger('size')->nullable();
            $table->string('extension')->nullable();
            $table->string('mime_type')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deleted_files');
        Schema::dropIfExists('ip_lists');
        Schema::dropIfExists('scheduled_tasks');
        Schema::dropIfExists('content_templates');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('search_queries');
        Schema::dropIfExists('search_indexes');
        Schema::dropIfExists('content_custom_fields');
        Schema::dropIfExists('custom_fields');
        Schema::dropIfExists('field_groups');
        Schema::dropIfExists('plugins');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('backups');
        Schema::dropIfExists('redis_settings');
        Schema::dropIfExists('settings');
    }
};
