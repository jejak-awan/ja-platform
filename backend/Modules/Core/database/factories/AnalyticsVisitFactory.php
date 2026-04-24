<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\AnalyticsVisit;

class AnalyticsVisitFactory extends Factory
{
    protected $model = AnalyticsVisit::class;

    public function configure()
    {
        return $this->afterCreating(function (AnalyticsVisit $visit) {
            \Modules\Core\Models\AnalyticsSession::factory()->create([
                'session_id' => $visit->session_id,
                'started_at' => $visit->visited_at,
                'ended_at' => $visit->visited_at->copy()->addMinutes(5),
            ]);
        });
    }

    public function definition(): array
    {
        return [
            'session_id' => $this->faker->uuid(),
            'user_id' => null,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'referer' => $this->faker->boolean(70) ? $this->faker->url() : null,
            'url' => $this->faker->url(),
            'method' => 'GET',
            'status_code' => 200,
            // device/location info now in sessions table
            'visited_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
