<?php

namespace Modules\Cms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Cms\Models\Comment;
use Modules\Cms\Models\Content;
use Modules\System\Models\User;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'user_id' => User::factory(),
            'parent_id' => null,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'body' => fake()->paragraph(),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected', 'spam']),
        ];
    }

    /**
     * Indicate that the comment is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the comment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the comment is a reply.
     */
    public function reply(?Comment $parent = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'parent_id' => $parent?->id ?? Comment::factory()->create()->id,
        ]);
    }
}
