<?php

namespace Modules\School\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\School\Models\HR\Staff;
use Modules\School\Models\Institution\School;

class StaffFactory extends Factory
{
    protected $model = Staff::class;

    public function definition(): array
    {
        $gender = $this->faker->randomElement(['L', 'P']);
        $ptkTypes = ['Guru Kelas', 'Guru Mapel', 'Guru BK', 'Kepala Sekolah', 'Tenaga Administrasi', 'Laboran'];
        $employmentStatuses = ['PNS', 'PPPK', 'GTY', 'GTT', 'Honorer'];
        $educations = ['S1', 'S2', 'S3', 'D3', 'D4'];

        return [
            'school_id' => School::factory(),
            'user_id' => null,
            'nuptk' => $this->faker->unique()->numerify('################'),
            'nik' => $this->faker->numerify('################'),
            'full_name' => $gender === 'L' ? $this->faker->name('male') : $this->faker->name('female'),
            'place_of_birth' => $this->faker->city(),
            'date_of_birth' => $this->faker->dateTimeBetween('-55 years', '-25 years')->format('Y-m-d'),
            'gender' => $gender,
            'religion' => $this->faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']),
            'employment_status' => $this->faker->randomElement($employmentStatuses),
            'ptk_type' => $this->faker->randomElement($ptkTypes),
            'sk_pengangkatan' => $this->faker->numerify('SK/###/####'),
            'tmt_pengangkatan' => $this->faker->date(),
            'last_education' => $this->faker->randomElement($educations),
            'major' => $this->faker->randomElement(['Pendidikan Teknik Informatika', 'Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Fisika']),
            'certification_status' => $this->faker->boolean(60),
        ];
    }

    public function guru(): static
    {
        return $this->state(fn() => ['ptk_type' => 'Guru Mapel']);
    }

    public function pns(): static
    {
        return $this->state(fn() => ['employment_status' => 'PNS']);
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn() => ['school_id' => $school->id]);
    }
}
