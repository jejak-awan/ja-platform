<?php

namespace Modules\Cms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Language;

class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        $codes = ['en', 'id', 'es', 'fr', 'de', 'zh', 'ja', 'ko', 'ar', 'ru'];
        $code = $this->faker->unique()->randomElement($codes);

        $names = [
            'en' => ['English', 'English'],
            'id' => ['Indonesian', 'Bahasa Indonesia'],
            'es' => ['Spanish', 'Español'],
            'fr' => ['French', 'Français'],
            'de' => ['German', 'Deutsch'],
            'zh' => ['Chinese', '中文'],
            'ja' => ['Japanese', '日本語'],
            'ko' => ['Korean', '한국어'],
            'ar' => ['Arabic', 'العربية'],
            'ru' => ['Russian', 'Русский'],
        ];

        [$name, $nativeName] = $names[$code] ?? ['Unknown', 'Unknown'];

        return [
            'code' => $code,
            'name' => $name,
            'native_name' => $nativeName,
            'flag' => $this->getFlag($code),
            'is_active' => true,
            'is_default' => false,
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    private function getFlag(string $code): string
    {
        return match ($code) {
            'en' => '🇬🇧',
            'id' => '🇮🇩',
            'es' => '🇪🇸',
            'fr' => '🇫🇷',
            'de' => '🇩🇪',
            'zh' => '🇨🇳',
            'ja' => '🇯🇵',
            'ko' => '🇰🇷',
            'ar' => '🇸🇦',
            'ru' => '🇷🇺',
            default => '🏳️',
        };
    }
}
