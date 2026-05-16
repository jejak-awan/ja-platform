<?php

declare(strict_types=1);

namespace Modules\Layout\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Layout\Models\MenuItem;
use Modules\Layout\Models\Menu;

class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'title' => fake()->words(2, true),
            'url' => '/' . fake()->slug(),
            'type' => 'custom',
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
