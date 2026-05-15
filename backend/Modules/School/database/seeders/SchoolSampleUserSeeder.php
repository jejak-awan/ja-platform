<?php

namespace Modules\School\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\System\Models\User;

class SchoolSampleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make(env('DEFAULT_USER_PASSWORD', 'password'));

        $users = [
            [
                'name' => 'Kepala Sekolah (Drs. Mulyana)',
                'email' => 'kepsek@cijulang.sch.id',
                'role' => 'admin-unit',
            ],
            [
                'name' => 'Admin Kurikulum (Siti Aminah)',
                'email' => 'kurikulum@cijulang.sch.id',
                'role' => 'operator-unit',
            ],
            [
                'name' => 'Admin Kesiswaan (Budi Santoso)',
                'email' => 'kesiswaan@cijulang.sch.id',
                'role' => 'operator-unit',
            ],
            [
                'name' => 'Guru (Ani Maryani)',
                'email' => 'guru@cijulang.sch.id',
                'role' => 'guru',
            ],
            [
                'name' => 'Siswa (Ahmad Faisal)',
                'email' => 'siswa@cijulang.sch.id',
                'role' => 'siswa',
            ],
            [
                'name' => 'Admin OSIS (Siti Rahma)',
                'email' => 'osis@cijulang.sch.id',
                'role' => 'operator-unit',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $password,
                    'email_verified_at' => now(),
                ]
            );
            
            if (!$user->hasRole($userData['role'])) {
                $user->assignRole($userData['role']);
            }
        }

        $this->command->info('School sample users seeded successfully!');
    }
}
