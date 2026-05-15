<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Analytics Sessions
        Schema::create('srv_analytics_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('country')->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamps();
        });

        // 2. Analytics Events
        Schema::create('srv_analytics_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id')->index();
            $table->string('event_name')->index();
            $table->string('url')->nullable();
            $table->string('referrer')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('srv_analytics_sessions')->onDelete('cascade');
        });

        // 3. Slow Queries
        Schema::create('srv_analytics_slow_queries', function (Blueprint $table) {
            $table->id();
            $table->text('sql');
            $table->float('time');
            $table->string('connection')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('srv_analytics_slow_queries');
        Schema::dropIfExists('srv_analytics_events');
        Schema::dropIfExists('srv_analytics_sessions');
    }
};
