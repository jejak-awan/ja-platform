<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Cms\Models\Theme;

return new class extends Migration
{
    private const CANONICAL_KEY = 'theme_data_bindings';

    private const OBSOLETE_KEY = '_advanced_bindings';

    public function up(): void
    {
        Theme::query()->chunkById(50, function ($themes): void {
            foreach ($themes as $theme) {
                $settings = $theme->settings;
                if (! is_array($settings) || ! array_key_exists(self::OBSOLETE_KEY, $settings)) {
                    continue;
                }

                $legacy = $settings[self::OBSOLETE_KEY];
                $canonical = $settings[self::CANONICAL_KEY] ?? null;
                if (! is_array($canonical) && is_array($legacy)) {
                    $settings[self::CANONICAL_KEY] = $legacy;
                }

                unset($settings[self::OBSOLETE_KEY]);
                $theme->settings = $settings;
                $theme->saveQuietly();
            }
        });
    }

    public function down(): void
    {
        // Non-reversible.
    }
};
