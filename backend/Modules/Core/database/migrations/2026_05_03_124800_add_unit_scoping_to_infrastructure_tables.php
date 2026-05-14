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
        $tables = [
            'core_backups',
            'core_webhooks',
            'core_plugins',
            'core_field_groups',
            'core_custom_fields',
            'core_content_custom_fields',
            'core_search_indexes',
            'core_search_queries',
            'core_email_templates',
            'core_notifications',
            'core_content_templates',
            'core_scheduled_tasks',
            'core_ip_lists',
            'core_deleted_files',
            'core_tags',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'workspace_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('workspace_id')->nullable()->after('id')->index();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'core_backups',
            'core_webhooks',
            'core_plugins',
            'core_field_groups',
            'core_custom_fields',
            'core_content_custom_fields',
            'core_search_indexes',
            'core_search_queries',
            'core_email_templates',
            'core_notifications',
            'core_content_templates',
            'core_scheduled_tasks',
            'core_ip_lists',
            'core_deleted_files',
            'core_tags',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'workspace_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('workspace_id');
                });
            }
        }
    }
};
