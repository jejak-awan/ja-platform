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
            'contents',
            'categories',
            'menus',
            'forms',
            'form_fields',
            'form_submissions',
            'form_analytics',
            'redirects',
            'newsletter_subscribers',
            'widgets',
            'comments',
            'content_revisions',
            'menu_items',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Keep CMS independent from School module schema: no FK constraint here.
                $table->unsignedBigInteger('workspace_id')->nullable()->after('id')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'contents',
            'categories',
            'menus',
            'forms',
            'form_fields',
            'form_submissions',
            'form_analytics',
            'redirects',
            'newsletter_subscribers',
            'widgets',
            'comments',
            'content_revisions',
            'menu_items',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('workspace_id');
            });
        }
    }
};
