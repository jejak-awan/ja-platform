<?php

namespace Modules\School\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolInfrastructureSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default School (Foundation)
        DB::table('sch_ins_schools')->updateOrInsert(
            ['npsn' => '10000001'],
            [
                'name' => 'JA-Platform Edu Unit',
                'npsn' => '10000001',
                'type' => 'negeri',
                'status_kepemilikan' => 'negeri',
                'is_multi_unit' => false,
                'address' => 'Jl. Pendidikan No. 1, Kota Digital',
                'phone' => '021-12345678',
                'email' => 'admin@sekolahk2.id',
                'website' => 'https://sekolahk2.id',
                'vision' => 'Mewujudkan Pendidikan Digital yang Terintegrasi dan Inovatif.',
                'mission' => "1. Menyediakan infrastruktur pendidikan modern.\n2. Digitalisasi administrasi sekolah.\n3. Optimalisasi proses belajar mengajar.",
                'history' => 'Unit pendidikan ini dikembangkan menggunakan JA-Platform untuk standarisasi manajemen institusi.',
                'principal_name' => 'Administrator',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Get the actual school ID (don't assume 15)
        $school = DB::table('sch_ins_schools')->where('npsn', '10000001')->first();
        if (! $school) {
            $this->command->error('Failed to create default school!');
            return;
        }
        $schoolId = $school->id;

        // 2. Create Unit (Level)
        DB::table('sch_ins_levels')->updateOrInsert(
            ['school_id' => $schoolId, 'level' => 'SMK'],
            [
                'name' => 'JA-Platform Edu Unit',
                'type' => 'smk',
                'level' => 'SMK',
                'school_id' => $schoolId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Get the actual unit ID
        $unit = DB::table('sch_ins_levels')
            ->where('school_id', $schoolId)
            ->where('level', 'SMK')
            ->first();

        if ($unit) {
            $this->command->info("School seeded: ID={$schoolId}, Unit ID={$unit->id}");
        }

        // 3. Fix PostgreSQL sequences after explicit ID inserts
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('sch_ins_schools', 'id'), COALESCE((SELECT MAX(id) FROM sch_ins_schools), 1))");
            DB::statement("SELECT setval(pg_get_serial_sequence('sch_ins_levels', 'id'), COALESCE((SELECT MAX(id) FROM sch_ins_levels), 1))");
        }
    }
}
