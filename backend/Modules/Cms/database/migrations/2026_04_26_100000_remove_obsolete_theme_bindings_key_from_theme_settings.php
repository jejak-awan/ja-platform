<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const CANONICAL_KEY = 'theme_data_bindings';

    private const OBSOLETE_KEY = '_advanced_bindings';

    public function up(): void
    {
        DB::table('themes')->orderBy('id')->chunk(50, function ($themes): void {
            foreach ($themes as $theme) {
                $settings = json_decode($theme->settings, true);
                if (! is_array($settings) || ! array_key_exists(self::OBSOLETE_KEY, $settings)) {
                    continue;
                }

                $legacy = $settings[self::OBSOLETE_KEY];
                $canonical = $settings[self::CANONICAL_KEY] ?? null;
                if (! is_array($canonical) && is_array($legacy)) {
                    $settings[self::CANONICAL_KEY] = $legacy;
                }

                unset($settings[self::OBSOLETE_KEY]);
                
                DB::table('themes')->where('id', $theme->id)->update([
                    'settings' => json_encode($settings)
                ]);
            }
        });
    }

    public function down(): void
    {
        // Non-reversible.
    }
};
