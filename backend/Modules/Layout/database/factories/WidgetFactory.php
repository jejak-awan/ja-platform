<?php

declare(strict_types=1);

namespace Modules\Layout\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Layout\Models\Widget;

class WidgetFactory extends Factory
{
    protected $model = Widget::class;

    public function definition(): array
    {
        return [
            'workspace_id' => null,
            'name' => fake()->words(2, true),
            'type' => 'text',
            'location' => 'sidebar',
            'content' => ['text' => fake()->paragraph()],
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
