<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\IpList;

class IpListFactory extends Factory
{
    protected $model = IpList::class;

    public function definition(): array
    {
        return [
            'ip_address' => $this->faker->ipv4(),
            'type' => $this->faker->randomElement(['blocklist', 'whitelist']),
            'reason' => $this->faker->sentence(),
            'created_by' => null,
        ];
    }

    public function blocklist(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'blocklist',
        ]);
    }

    public function whitelist(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'whitelist',
        ]);
    }
}
