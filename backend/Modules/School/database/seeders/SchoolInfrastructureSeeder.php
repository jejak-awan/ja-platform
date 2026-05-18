<?php

namespace Modules\School\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;

class SchoolInfrastructureSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default School (Foundation)
        School::updateOrCreate(
            ['npsn' => '10000001'],
            [
                'name' => 'JA-Platform Edu Unit',
                'type' => 'negeri',
                'status_kepemilikan' => 'negeri',
                'is_multi_unit' => false,
                'address' => 'Jl. Pendidikan No. 1, Kota Digital',
                'phone' => '021-12345678',
                'email' => 'admin@sekolahk2.id',
                'website' => 'https://sekolahk2.id',
                'vision' => 'Mewujudkan Pendidikan Digital yang Terintegrasi dan Inovatif.',
                'mission' => "1. Menyediakan infrastruktur pendidikan modern.\n2. Digitalisasi administrasi sekolah.\n3. Optimalisasi proses belajar mengajar.",
                'principal_name' => 'Administrator',
            ]
        );

        // Get the actual school ID (don't assume 15)
        $school = School::where('npsn', '10000001')->first();
        if (! $school) {
            $this->command->error('Failed to create default school!');

            return;
        }
        $schoolId = $school->id;

        // 2. Create Unit (Level)
        SchoolUnit::updateOrCreate(
            ['school_id' => $schoolId, 'level' => 'SMK'],
            [
                'name' => 'JA-Platform Edu Unit',
                'type' => 'smk',
                'level' => 'SMK',
            ]
        );

        // Get the actual unit ID
        $unit = SchoolUnit::where('school_id', $schoolId)
            ->where('level', 'SMK')
            ->first();

        if ($unit) {
            $this->command->info("School seeded: ID={$schoolId}, Unit ID={$unit->id}");
        }

        // 3. Fix PostgreSQL sequences after explicit ID inserts
        // Since we are now using UUIDs, we don't need sequence fixes.
    }
}
