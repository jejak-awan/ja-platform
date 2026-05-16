<?php

declare(strict_types=1);

namespace Modules\Layout\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Layout\Models\Menu;

class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        return [
            'workspace_id' => null,
            'name' => fake()->words(2, true),
            'location' => fake()->slug(),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
