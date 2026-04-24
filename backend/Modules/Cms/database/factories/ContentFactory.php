<?php

namespace Modules\Cms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Cms\Models\Category;
use Modules\Cms\Models\Content;
use Modules\Core\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Cms\Models\Content>
 */
class ContentFactory extends Factory
{
    protected $model = Content::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.uniqid(),
            'excerpt' => fake()->paragraph(),
            'body' => fake()->paragraphs(5, true),
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
            'type' => fake()->randomElement(['post', 'page']),
            'author_id' => User::factory(),
            'category_id' => Category::factory(),
            'published_at' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
            'views' => fake()->numberBetween(0, 1000),
            'meta' => [],
            'meta_title' => fake()->optional()->sentence(),
            'meta_description' => fake()->optional()->paragraph(),
            'meta_keywords' => fake()->optional()->words(5, true),
        ];
    }

    /**
     * Indicate that the content is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the content is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the content is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }

    /**
     * Indicate that the content is a page.
     */
    public function page(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'page',
        ]);
    }
}
