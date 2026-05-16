<?php

namespace Modules\Cms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Layout\Models\UrlRewrite;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Layout\Models\UrlRewrite>
 */
class RedirectFactory extends Factory
{
    protected $model = Redirect::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'from_url' => '/'.fake()->slug(),
            'to_url' => '/'.fake()->slug(),
            'type' => fake()->randomElement([301, 302]),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the redirect is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the redirect is permanent (301).
     */
    public function permanent(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 301,
        ]);
    }

    /**
     * Indicate that the redirect is temporary (302).
     */
    public function temporary(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 302,
        ]);
    }
}
