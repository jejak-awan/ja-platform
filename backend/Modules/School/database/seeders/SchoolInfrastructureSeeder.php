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
            ['id' => 15],
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
 
        // 2. Create Units (Levels) - Only 1 unit for Negeri
        $units = [
            ['id' => 15, 'name' => 'JA-Platform Edu Unit', 'type' => 'smk', 'level' => 'SMK'],
        ];
 
        foreach ($units as $unit) {
            DB::table('sch_ins_levels')->updateOrInsert(
                ['id' => $unit['id']],
                array_merge($unit, [
                    'school_id' => 15,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // 3. Cleanup existing extra units if any
        DB::table('sch_ins_levels')
            ->where('school_id', 15)
            ->whereNotIn('id', [15])
            ->delete();
    }
}
