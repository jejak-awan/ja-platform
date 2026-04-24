<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\ActivityLog;
use Modules\Core\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Core\Models\ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['created', 'updated', 'deleted', 'viewed', 'login', 'logout']),
            'model_type' => fake()->randomElement(['Modules\\Cms\\Models\\Content', 'Modules\\Core\\Models\\User', 'Modules\\Cms\\Models\\Category']),
            'model_id' => fake()->numberBetween(1, 100),
            'description' => fake()->sentence(),
            'changes' => [],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
