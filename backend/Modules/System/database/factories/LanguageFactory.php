<?php

namespace Modules\System\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\System\Models\Language;

class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->languageCode(),
            'name' => fake()->word(),
            'native_name' => fake()->word(),
            'flag' => null,
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
