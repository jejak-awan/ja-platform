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
        // 1. Activity Logs (Custom)
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('action')->index();
            $table->nullableMorphs('model');
            $table->text('description')->nullable();
            $table->json('changes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        // 2. Security Logs
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('event_type')->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 3. Analytics Visits
        Schema::create('analytics_visits', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->string('url')->index();
            $table->string('method')->default('GET');
            $table->integer('status_code')->default(200);
            $table->integer('duration')->nullable();
            $table->timestamp('visited_at')->index();
            $table->timestamps();
        });

        // 4. Analytics Events
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('event_type');
            $table->string('event_name');
            $table->string('event_category')->nullable();
            $table->json('event_data')->nullable();
            $table->string('url')->nullable();
            $table->foreignId('content_id')->nullable()->constrained('contents')->onDelete('set null');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });

        // 5. Analytics Sessions
        Schema::create('analytics_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->integer('page_views')->default(0);
            $table->integer('duration')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['started_at', 'ended_at']);
        });

        // 6. Login History
        Schema::create('login_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('login_at')->index();
            $table->timestamp('logout_at')->nullable();
            $table->integer('session_duration')->nullable();
            $table->enum('status', ['success', 'failed', 'blocked'])->default('success')->index();
            $table->string('failure_reason')->nullable();
            $table->timestamps();
        });

        // 7. CSP Reports
        Schema::create('csp_reports', function (Blueprint $table) {
            $table->id();
            $table->string('document_uri')->index();
            $table->string('violated_directive')->index();
            $table->text('blocked_uri')->nullable();
            $table->string('source_file')->nullable();
            $table->integer('line_number')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip_address', 45)->index();
            $table->json('raw_report')->nullable();
            $table->enum('status', ['new', 'reviewed', 'false_positive'])->default('new')->index();
            $table->timestamps();
            $table->index('created_at');
        });

        // 8. Slow Queries
        Schema::create('slow_queries', function (Blueprint $table) {
            $table->id();
            $table->text('query');
            $table->json('bindings')->nullable();
            $table->integer('duration'); // milliseconds
            $table->string('route')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('created_at');
        });

        // 9. Dependency Vulnerabilities
        Schema::create('dependency_vulnerabilities', function (Blueprint $table) {
            $table->id();
            $table->string('package_name')->index();
            $table->string('version');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->index();
            $table->string('cve')->nullable()->index();
            $table->string('fixed_in')->nullable();
            $table->enum('status', ['new', 'acknowledged', 'patched', 'ignored'])->default('new')->index();
            $table->enum('source', ['composer', 'npm'])->index();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['package_name', 'version', 'cve']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dependency_vulnerabilities');
        Schema::dropIfExists('slow_queries');
        Schema::dropIfExists('csp_reports');
        Schema::dropIfExists('login_history');
        Schema::dropIfExists('analytics_sessions');
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('analytics_visits');
        Schema::dropIfExists('security_logs');
        Schema::dropIfExists('activity_logs');
    }
};
