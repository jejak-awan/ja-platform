<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\AnalyticsEvent;

class AnalyticsEventFactory extends Factory
{
    protected $model = AnalyticsEvent::class;

    public function definition(): array
    {
        $eventTypes = ['page_view', 'click', 'form_submit', 'download', 'search', 'video_play'];
        $eventType = $this->faker->randomElement($eventTypes);

        return [
            'session_id' => $this->faker->uuid(),
            'user_id' => null,
            'event_type' => $eventType,
            'event_name' => $this->getEventName($eventType),
            'event_data' => json_encode([
                'url' => $this->faker->url(),
                'timestamp' => now()->timestamp,
            ]),
            'ip_address' => $this->faker->ipv4(),
            'occurred_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    private function getEventName(string $eventType): string
    {
        return match ($eventType) {
            'page_view' => 'Page View: '.$this->faker->words(3, true),
            'click' => 'Click: '.$this->faker->randomElement(['Button', 'Link', 'Menu']),
            'form_submit' => 'Form Submit: '.$this->faker->randomElement(['Contact', 'Newsletter', 'Registration']),
            'download' => 'Download: '.$this->faker->randomElement(['PDF', 'Image', 'Document']),
            'search' => 'Search: '.$this->faker->words(2, true),
            'video_play' => 'Video Play: '.$this->faker->words(3, true),
            default => 'Event',
        };
    }
}
