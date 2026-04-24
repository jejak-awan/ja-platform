<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Setting;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->word(),
            'value' => $this->faker->word(),
            'type' => 'string',
            'group' => 'general',
            'description' => $this->faker->sentence(),
            'is_public' => $this->faker->boolean(),
        ];
    }
}
