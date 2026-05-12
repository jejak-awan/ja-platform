<?php

namespace Modules\School\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\School\Models\Osis\OsisProgram;
use Modules\School\Models\Osis\OsisMember;
use Modules\School\Models\Osis\OsisSuggestion;
use Modules\School\Models\Student\Student;

class OsisSeeder extends Seeder
{
    public function run(): void
    {
        $school = \Illuminate\Support\Facades\DB::table('sch_ins_schools')->where('npsn', '10000001')->first();
        if (!$school) { return; }
        $schoolId = $school->id;
        $students = Student::where('school_id', $schoolId)->get();

        if ($students->isEmpty()) {
            return;
        }

        // 1. Seed Programs
        $programs = [
            [
                'school_id' => $schoolId,
                'name' => 'LDKS (Latihan Dasar Kepemimpinan Siswa)',
                'description' => 'Pelatihan kepemimpinan untuk pengurus OSIS baru.',
                'start_date' => now()->addMonths(1),
                'status' => 'planned',
                'budget' => 5000000,
            ],
            [
                'school_id' => $schoolId,
                'name' => 'PORSENI 2024',
                'description' => 'Pekan Olahraga dan Seni antar kelas.',
                'start_date' => now()->addMonths(3),
                'status' => 'planned',
                'budget' => 15000000,
            ],
        ];

        foreach ($programs as $program) {
            OsisProgram::updateOrCreate(['name' => $program['name'], 'school_id' => $schoolId], $program);
        }

        // 2. Seed Members
        $positions = ['Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara'];
        foreach ($positions as $index => $pos) {
            if (isset($students[$index])) {
                OsisMember::updateOrCreate(
                    ['school_id' => $schoolId, 'student_id' => $students[$index]->id],
                    ['position' => $pos, 'period' => '2023/2024']
                );
            }
        }

        // 3. Seed Suggestions
        $suggestions = [
            [
                'school_id' => $schoolId,
                'title' => 'Fasilitas Kantin',
                'content' => 'Mohon kebersihan kantin lebih diperhatikan lagi.',
                'status' => 'pending',
            ],
            [
                'school_id' => $schoolId,
                'title' => 'Ekskul Robotik',
                'content' => 'Apakah bisa diadakan ekskul robotik di sekolah kita?',
                'status' => 'reviewed',
            ],
        ];

        foreach ($suggestions as $suggestion) {
            OsisSuggestion::create($suggestion);
        }
    }
}
