<?php

namespace Modules\School\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\School\Models\HR\Staff;
use Modules\System\Models\Role;
use Modules\System\Models\User;

class StaffSampleSeeder extends Seeder
{
    public function run(): void
    {
        $school = DB::table('sch_ins_schools')->where('npsn', '10000001')->first();
        $unit = $school ? DB::table('sch_ins_levels')->where('school_id', $school->id)->first() : null;
        if (! $school || ! $unit) {
            return;
        }
        $schoolId = $school->id;
        $unitId = $unit->id;

        $staffData = [
            [
                'name' => 'Budi Setiawan',
                'email' => 'budi.teacher@example.com',
                'nuptk' => '1234567890123456',
                'nik' => '3210987654321098',
                'gender' => 'L',
                'employment_status' => 'GTY/PTY',
                'ptk_type' => 'Guru Mapel',
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti.teacher@example.com',
                'nuptk' => '2345678901234567',
                'nik' => '3210876543210987',
                'gender' => 'P',
                'employment_status' => 'Guru Honor Sekolah',
                'ptk_type' => 'Guru Kelas',
            ],
            [
                'name' => 'Agus Ramdhan',
                'email' => 'agus.staff@example.com',
                'nuptk' => '3456789012345678',
                'nik' => '3210765432109876',
                'gender' => 'L',
                'employment_status' => 'Tenaga Honor Sekolah',
                'ptk_type' => 'Tenaga Administrasi Sekolah',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.teacher@example.com',
                'nuptk' => '4567890123456789',
                'nik' => '3210654321098765',
                'gender' => 'P',
                'employment_status' => 'GTT Provinsi',
                'ptk_type' => 'Guru Mapel',
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'eko.teacher@example.com',
                'nuptk' => '5678901234567890',
                'nik' => '3210543210987654',
                'gender' => 'L',
                'employment_status' => 'PNS',
                'ptk_type' => 'Guru Mapel',
            ],
        ];

        foreach ($staffData as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make(env('DEFAULT_USER_PASSWORD', 'password')),
                    'email_verified_at' => now(),
                ]
            );

            // Assign 'guru' role if not already assigned
            if (! $user->hasRole('guru')) {
                $user->assignRole('guru');
            }

            Staff::withoutGlobalScopes()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'school_id' => $schoolId,
                    'workspace_id' => $unitId,
                    'full_name' => $data['name'],
                    'nuptk' => $data['nuptk'],
                    'nik' => $data['nik'],
                    'gender' => $data['gender'],
                    'religion' => 'Islam',
                    'employment_status' => $data['employment_status'],
                    'ptk_type' => $data['ptk_type'],
                    'certification_status' => ($data['employment_status'] === 'PNS'),
                ]
            );
        }
    }
}
