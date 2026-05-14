<?php

namespace Modules\School\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\School\Models\Academic\Subject;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $school = \Illuminate\Support\Facades\DB::table('sch_ins_schools')->where('npsn', '10000001')->first();
        $unit = $school ? \Illuminate\Support\Facades\DB::table('sch_ins_levels')->where('school_id', $school->id)->first() : null;
        if (!$school || !$unit) { $this->command->warn('No school/unit found. Skipping SubjectSeeder.'); return; }
        $schoolId = $school->id;
        $unitId = $unit->id;

        $subjects = [
            ['code' => 'PAI', 'name' => 'Pendidikan Agama Islam', 'group' => 'A', 'kkm' => 75],
            ['code' => 'PPKN', 'name' => 'Pendidikan Pancasila & Kewarganegaraan', 'group' => 'A', 'kkm' => 75],
            ['code' => 'BINDO', 'name' => 'Bahasa Indonesia', 'group' => 'A', 'kkm' => 75],
            ['code' => 'MTK', 'name' => 'Matematika (Umum)', 'group' => 'A', 'kkm' => 70],
            ['code' => 'SEJ', 'name' => 'Sejarah Indonesia', 'group' => 'A', 'kkm' => 75],
            ['code' => 'BING', 'name' => 'Bahasa Inggris', 'group' => 'A', 'kkm' => 70],
            ['code' => 'PJK', 'name' => 'Pendidikan Jasmani, Olahraga & Kesehatan', 'group' => 'B', 'kkm' => 80],
            ['code' => 'SBK', 'name' => 'Seni Budaya', 'group' => 'B', 'kkm' => 80],
            ['code' => 'SUNDA', 'name' => 'Bahasa Sunda', 'group' => 'B', 'kkm' => 80],
            ['code' => 'PPLG-CORE', 'name' => 'Dasar-dasar Pengembangan Perangkat Lunak', 'group' => 'C', 'kkm' => 75],
            ['code' => 'WEB', 'name' => 'Pemrograman Web', 'group' => 'C', 'kkm' => 75],
            ['code' => 'MOBILE', 'name' => 'Pemrograman Perangkat Bergerak', 'group' => 'C', 'kkm' => 75],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['school_id' => $schoolId, 'workspace_id' => $unitId, 'code' => $subject['code']],
                $subject
            );
        }

        $this->command->info('Default academic subjects seeded successfully!');
    }
}
