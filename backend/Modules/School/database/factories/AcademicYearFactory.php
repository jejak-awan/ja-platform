<?php

namespace Modules\School\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\School\Models\Academic\AcademicYear;
use Modules\School\Models\Institution\School;

class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        $startYear = $this->faker->numberBetween(2024, 2027);
        $endYear = $startYear + 1;

        return [
            'school_id' => School::factory(),
            'year' => "{$startYear}/{$endYear}",
            'start_date' => "{$startYear}-07-15",
            'end_date' => "{$endYear}-06-30",
            'is_active' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['is_active' => true]);
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn (): array => ['school_id' => $school->id]);
    }
}
