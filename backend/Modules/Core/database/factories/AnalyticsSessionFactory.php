<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\AnalyticsSession;

class AnalyticsSessionFactory extends Factory
{
    protected $model = AnalyticsSession::class;

    public function definition(): array
    {
        $startedAt = $this->faker->dateTimeBetween('-30 days', 'now');
        $duration = $this->faker->numberBetween(60, 3600); // 1 minute to 1 hour
        $endedAt = (clone $startedAt)->modify("+{$duration} seconds");

        return [
            'session_id' => $this->faker->uuid(),
            'user_id' => null,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'device_type' => $this->faker->randomElement(['desktop', 'mobile', 'tablet']),
            'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera']),
            'os' => $this->faker->randomElement(['Windows', 'macOS', 'Linux', 'iOS', 'Android']),
            'country' => $this->faker->country(),
            'city' => $this->faker->city(),
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'duration' => $duration,
            'page_views' => $this->faker->numberBetween(1, 20),
        ];
    }
}
