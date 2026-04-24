<?php

namespace Modules\School\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\School\Models\Institution\School;

class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        $types = ['negeri', 'swasta'];
        $accreditations = ['A', 'B', 'C', 'TT'];
        $kurikulums = ['Kurikulum Merdeka', 'Kurikulum 2013', 'KTSP'];

        return [
            'name' => 'SMK ' . $this->faker->company(),
            'type' => $this->faker->randomElement($types),
            'npsn' => $this->faker->unique()->numerify('########'),
            'nss' => $this->faker->numerify('####################'),
            'accreditation' => $this->faker->randomElement($accreditations),
            'kurikulum' => $this->faker->randomElement($kurikulums),
            'status_kepemilikan' => $this->faker->randomElement(['Yayasan', 'Pemerintah Daerah']),
            'sk_pendirian' => $this->faker->numerify('SK-###/####'),
            'tgl_sk_pendirian' => $this->faker->date(),
            'sk_operasional' => $this->faker->numerify('OP-###/####'),
            'tgl_sk_operasional' => $this->faker->date(),
            'address' => $this->faker->address(),
            'rt' => $this->faker->numerify('0##'),
            'rw' => $this->faker->numerify('0##'),
            'desa_kelurahan' => $this->faker->citySuffix(),
            'kecamatan' => $this->faker->city(),
            'kabupaten_kota' => 'Kab. ' . $this->faker->city(),
            'provinsi' => 'Jawa Barat',
            'kode_pos' => $this->faker->numerify('#####'),
            'lat' => $this->faker->latitude(-8, -6),
            'long' => $this->faker->longitude(106, 108),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'website' => $this->faker->url(),
            'npwp' => $this->faker->numerify('##.###.###.#-###.###'),
            'principal_name' => $this->faker->name(),
            'foundation_name' => 'Yayasan ' . $this->faker->company(),
            'vision' => 'Menjadi sekolah unggul dan berkarakter',
            'mission' => 'Menyelenggarakan pendidikan berkualitas',
        ];
    }

    public function negeri(): static
    {
        return $this->state(fn() => ['type' => 'negeri', 'status_kepemilikan' => 'Pemerintah Daerah']);
    }

    public function swasta(): static
    {
        return $this->state(fn() => ['type' => 'swasta', 'status_kepemilikan' => 'Yayasan']);
    }
}
