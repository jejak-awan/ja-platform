<?php

namespace Modules\School\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\School\Models\Institution\SchoolLevel;
use Modules\School\Models\Institution\School;

class SchoolLevelFactory extends Factory
{
    protected $model = SchoolLevel::class;

    public function definition(): array
    {
        $levels = [
            ['level' => 'sd', 'name' => 'SD'],
            ['level' => 'smp', 'name' => 'SMP'],
            ['level' => 'sma', 'name' => 'SMA'],
            ['level' => 'smk', 'name' => 'SMK'],
        ];

        $selected = $this->faker->randomElement($levels);

        return [
            'school_id' => School::factory(),
            'level' => $selected['level'],
            'name' => $selected['name'] . ' ' . $this->faker->company(),
        ];
    }

    public function smk(): static
    {
        return $this->state(fn() => ['level' => 'smk', 'name' => 'SMK ' . $this->faker->company()]);
    }

    public function sma(): static
    {
        return $this->state(fn() => ['level' => 'sma', 'name' => 'SMA ' . $this->faker->company()]);
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn() => ['school_id' => $school->id]);
    }
}
