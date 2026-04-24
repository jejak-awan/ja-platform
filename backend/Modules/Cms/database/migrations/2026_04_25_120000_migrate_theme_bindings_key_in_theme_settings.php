<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Cms\Models\Theme;

return new class extends Migration
{
    private const CANONICAL_KEY = 'theme_data_bindings';

    /** @var list<string> */
    private const OBSOLETE_BINDING_KEYS = ['_advanced_bindings'];

    public function up(): void
    {
        Theme::query()->chunkById(50, function ($themes): void {
            foreach ($themes as $theme) {
                $settings = $theme->settings;
                if (! is_array($settings)) {
                    continue;
                }
                $hasCanonical = isset($settings[self::CANONICAL_KEY]) && is_array($settings[self::CANONICAL_KEY]);
                $movedPayload = null;
                foreach (self::OBSOLETE_BINDING_KEYS as $obsoleteKey) {
                    if (isset($settings[$obsoleteKey]) && is_array($settings[$obsoleteKey])) {
                        $movedPayload = $settings[$obsoleteKey];
                        unset($settings[$obsoleteKey]);
                        break;
                    }
                }
                if ($movedPayload === null) {
                    continue;
                }
                if (! $hasCanonical) {
                    $settings[self::CANONICAL_KEY] = $movedPayload;
                }
                $theme->settings = $settings;
                $theme->saveQuietly();
            }
        });
    }

    public function down(): void
    {
        // Non-reversible: storage uses `theme_data_bindings` only.
    }
};
