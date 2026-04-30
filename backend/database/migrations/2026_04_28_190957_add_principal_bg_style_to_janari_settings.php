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
        if (\DB::table('themes')->where('slug', 'janari')->exists()) {
            \DB::table('themes')
                ->where('slug', 'janari')
                ->whereNotNull('settings')
                ->update([
                    'settings' => \DB::raw("jsonb_set(settings::jsonb, '{principal_background_style}', '\"mesh\"'::jsonb)::json")
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::table('themes')
            ->where('slug', 'janari')
            ->whereNotNull('settings')
            ->update([
                'settings' => \DB::raw("(settings::jsonb - 'principal_background_style')::json")
            ]);
    }
};
