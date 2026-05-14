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
        // 1. Forms Table
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('success_message')->nullable();
            $table->text('redirect_url')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('submission_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->integer('start_count')->default(0);
            $table->foreignId('author_id')->nullable()->constrained('core_users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Form Fields (Legacy support)
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');
            $table->string('name');
            $table->string('label');
            $table->string('type');
            $table->text('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->json('options')->nullable();
            $table->json('validation_rules')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('form_id');
            $table->index('sort_order');
        });

        // 3. Form Submissions
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('core_users')->onDelete('set null');
            $table->json('data');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->enum('status', ['new', 'read', 'archived'])->default('new')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['form_id', 'created_at']);
        });

        // 4. Form Analytics
        Schema::create('form_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');
            $table->date('date')->index();
            $table->integer('views')->default(0);
            $table->integer('starts')->default(0);
            $table->integer('submissions')->default(0);
            $table->timestamps();

            $table->unique(['form_id', 'date']);
        });

        // 5. Redirects Table
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_url')->unique();
            $table->string('to_url');
            $table->enum('type', ['301', '302', '307', '308'])->default('301');
            $table->boolean('is_active')->default(true)->index();
            $table->integer('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Newsletter Subscribers
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->enum('status', ['subscribed', 'unsubscribed', 'pending'])->default('subscribed')->index();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('source')->nullable();
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
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('redirects');
        Schema::dropIfExists('form_analytics');
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('forms');
    }
};
