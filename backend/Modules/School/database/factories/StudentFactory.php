<?php

namespace Modules\School\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\School\Models\Student\Student;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $gender = $this->faker->randomElement(['L', 'P']);
        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];

        return [
            'school_id' => School::factory(),
            'school_unit_id' => null,
            'department_id' => null,
            'user_id' => null,
            'status' => 'active',
            'nisn' => $this->faker->unique()->numerify('##########'),
            'nis' => $this->faker->numerify('####'),
            'nik' => $this->faker->numerify('################'),
            'full_name' => $gender === 'L' ? $this->faker->name('male') : $this->faker->name('female'),
            'place_of_birth' => $this->faker->city(),
            'date_of_birth' => $this->faker->dateTimeBetween('-18 years', '-15 years')->format('Y-m-d'),
            'gender' => $gender,
            'religion' => $this->faker->randomElement($religions),
            'address' => $this->faker->address(),
            'rt' => $this->faker->numerify('0##'),
            'rw' => $this->faker->numerify('0##'),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'father_name' => $this->faker->name('male'),
            'father_nik' => $this->faker->numerify('################'),
            'father_occupation' => $this->faker->jobTitle(),
            'mother_name' => $this->faker->name('female'),
            'mother_nik' => $this->faker->numerify('################'),
            'mother_occupation' => $this->faker->jobTitle(),
            'metadata' => [],
        ];
    }

    public function active(): static
    {
        return $this->state(fn() => ['status' => 'active']);
    }

    public function graduated(): static
    {
        return $this->state(fn() => ['status' => 'graduated']);
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn() => ['school_id' => $school->id]);
    }

    public function forLevel(SchoolUnit $level): static
    {
        return $this->state(fn() => [
            'school_unit_id' => $level->id,
            'school_id' => $level->school_id,
        ]);
    }
}
