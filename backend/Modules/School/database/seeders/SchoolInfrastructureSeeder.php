<?php

namespace Modules\School\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolInfrastructureSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default School (SMK Negeri 1 Cijulang)
        DB::table('sch_ins_schools')->updateOrInsert(
            ['id' => 15],
            [
                'name' => 'SMK Negeri 1 Cijulang',
                'npsn' => '20252525',
                'type' => 'negeri',
                'status_kepemilikan' => 'negeri',
                'is_multi_unit' => false,
                'address' => 'Jl. Ciwaru No. 1, Cijulang, Pangandaran',
                'phone' => '0265-123456',
                'email' => 'info@smkn1cijulang.sch.id',
                'website' => 'https://smkn1cijulang.sch.id',
                'vision' => 'Terwujudnya Lulusan yang Unggul, Berkarakter, dan Berdaya Saing Global.',
                'mission' => "1. Menyelenggarakan pendidikan vokasi yang berkualitas.\n2. Menanamkan nilai-nilai karakter bangsa.\n3. Menjalin kerja sama dengan dunia usaha dan dunia industri.",
                'history' => 'SMK Negeri 1 Cijulang didirikan untuk memenuhi kebutuhan tenaga kerja terampil di wilayah Pangandaran.',
                'principal_name' => 'Dr. H. Ahmad Fauzi, M.Pd.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
 
        // 2. Create Units (Levels) - Only 1 unit for Negeri
        $units = [
            ['id' => 15, 'name' => 'SMK Negeri 1 Cijulang', 'type' => 'smk', 'level' => 'SMK'],
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
