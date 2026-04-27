<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Webhook;

class WebhookFactory extends Factory
{
    protected $model = Webhook::class;

    public function definition(): array
    {
        $events = [
            'content.created',
            'content.updated',
            'content.deleted',
            'content.published',
            'user.registered',
            'user.updated',
            'media.uploaded',
            'comment.created',
        ];

        return [
            'name' => $this->faker->words(3, true).' Webhook',
            'url' => $this->faker->url(),
            'event' => $this->faker->randomElement($events),
            'method' => 'POST',
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => 'JA-Platform-Webhook/1.0',
            ],
            'payload_template' => [
                'event' => '{{event}}',
                'data' => '{{data}}',
                'timestamp' => '{{timestamp}}',
            ],
            'is_active' => $this->faker->boolean(80),
            'timeout' => $this->faker->randomElement([10, 30, 60]),
            'retry_count' => 0,
            'max_retries' => $this->faker->randomElement([0, 1, 3, 5]),
            'last_triggered_at' => null,
            'success_count' => 0,
            'failure_count' => 0,
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

    public function withSuccesses(int $count = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'success_count' => $count,
            'last_triggered_at' => now()->subMinutes($this->faker->numberBetween(1, 60)),
        ]);
    }

    public function withFailures(int $count = 5): static
    {
        return $this->state(fn (array $attributes) => [
            'failure_count' => $count,
            'last_triggered_at' => now()->subMinutes($this->faker->numberBetween(1, 60)),
        ]);
    }
}
