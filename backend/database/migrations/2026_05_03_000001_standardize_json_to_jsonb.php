<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Convert JSON to JSONB for better Postgres performance
        $tablesToConvert = [
            'users' => ['preferences'],
            'two_factor_auth' => ['backup_codes'],
            'webhooks' => ['headers', 'payload_template'],
            'plugins' => ['settings'],
            'field_groups' => ['conditions'],
            'custom_fields' => ['options', 'validation_rules'],
            'search_indexes' => ['meta'],
            'search_queries' => ['filters'],
            'email_templates' => ['variables'],
            'notifications' => ['data'],
            'content_templates' => ['default_fields', 'meta'],
            'scheduled_tasks' => ['options'],
            'contents' => ['meta'],
            'content_revisions' => ['meta'],
        ];

        foreach ($tablesToConvert as $table => $columns) {
            if (Schema::hasTable($table)) {
                foreach ($columns as $column) {
                    // In Postgres, we need to use a raw statement to change type to jsonb
                    DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE JSONB USING {$column}::JSONB");
                }
            }
        }

        // 2. Add flexibility to core tables if needed
        // (Currently handled in specific migrations or already exists)

        // 3. Ensure softDeletes on critical core tables
        $criticalTables = ['users', 'roles', 'permissions'];
        foreach ($criticalTables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting JSONB to JSON is technically possible but usually not needed in Postgres
        // as JSONB is a superset. We'll leave it as is to avoid data loss during downgrades.
    }
};
