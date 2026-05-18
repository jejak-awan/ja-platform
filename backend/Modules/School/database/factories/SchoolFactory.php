<?php

namespace Modules\School\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\School\Models\Institution\School;

class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        return [
            'name' => 'SMK '.$this->faker->company(),
            'type' => $this->faker->randomElement(['negeri', 'swasta']),
            'npsn' => $this->faker->unique()->numerify('##########'),
            'status_kepemilikan' => $this->faker->randomElement(['Yayasan', 'Pemerintah Daerah']),
            'is_multi_unit' => $this->faker->boolean(30),
            'is_multi_branch' => $this->faker->boolean(10),

            // Contact
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'website' => $this->faker->url(),
            'principal_name' => $this->faker->name(),
            'principal_nip' => $this->faker->optional()->numerify('##################'),

            // Legality
            'sk_pendirian' => $this->faker->numerify('SK-###/####'),
            'tgl_sk_pendirian' => $this->faker->optional()->date(),
            'sk_operasional' => $this->faker->numerify('OP-###/####'),
            'tgl_sk_operasional' => $this->faker->optional()->date(),

            // Profile
            'vision' => 'Menjadi sekolah unggul dan berkarakter',
            'mission' => 'Menyelenggarakan pendidikan berkualitas',
        ];
    }

    public function negeri(): static
    {
        return $this->state(fn (): array => ['type' => 'negeri', 'status_kepemilikan' => 'Pemerintah Daerah']);
    }

    public function swasta(): static
    {
        return $this->state(fn (): array => ['type' => 'swasta', 'status_kepemilikan' => 'Yayasan']);
    }
}
