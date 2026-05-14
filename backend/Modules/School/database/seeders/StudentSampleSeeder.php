<?php

namespace Modules\School\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Models\User;
use Modules\School\Models\Student\Student;
use Modules\School\Models\Academic\StudyGroup;
use Modules\School\Models\Academic\Department;

class StudentSampleSeeder extends Seeder
{
    public function run(): void
    {
        $school = \Illuminate\Support\Facades\DB::table('sch_ins_schools')->where('npsn', '10000001')->first();
        $unit = $school ? \Illuminate\Support\Facades\DB::table('sch_ins_levels')->where('school_id', $school->id)->first() : null;
        if (!$school || !$unit) { return; }
        $schoolId = $school->id;
        $levelId = $unit->id;

        $studyGroups = StudyGroup::where('school_id', $schoolId)->get();

        $firstNames = ['Ahmad', 'Budi', 'Candra', 'Dedi', 'Eko', 'Fajar', 'Guntur', 'Hadi', 'Iwan', 'Joko', 'Kurniawan', 'Lutfi', 'Mulyadi', 'Nazar', 'Oman', 'Putra', 'Qomar', 'Rizky', 'Suryo', 'Taufik'];
        $lastNames = ['Saputra', 'Hidayat', 'Pratama', 'Kusuma', 'Santoso', 'Wijaya', 'Ramadhan', 'Setiawan', 'Nugroho', 'Subagyo'];
        
        $femaleFirstNames = ['Ani', 'Budiati', 'Citra', 'Dewi', 'Endang', 'Fitri', 'Gita', 'Hana', 'Indah', 'Juli', 'Kartika', 'Lestari', 'Maya', 'Novi', 'Oktavia', 'Putri', 'Qonita', 'Rina', 'Siti', 'Tanti'];
        
        $studentCount = 0;

        foreach ($studyGroups as $group) {
            for ($i = 1; $i <= 5; $i++) {
                $studentCount++;
                $isFemale = ($studentCount % 2 == 0);
                $firstName = $isFemale ? $femaleFirstNames[array_rand($femaleFirstNames)] : $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $fullName = $firstName . ' ' . $lastName;
                
                $nisn = '00' . (10000000 + $studentCount);
                $nis = '2324' . str_pad($studentCount, 3, '0', STR_PAD_LEFT);
                $email = strtolower($firstName . '.' . $lastName . $studentCount . '@example.com');

                // 1. Create User
                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $fullName,
                        'password' => Hash::make(env('DEFAULT_USER_PASSWORD', 'password')),
                        'email_verified_at' => now(),
                    ]
                );

                if (!$user->hasRole('siswa')) {
                    $user->assignRole('siswa');
                }

                // 2. Create Student
                $student = Student::withoutGlobalScopes()->updateOrCreate(
                    ['nisn' => $nisn],
                    [
                        'school_id' => $schoolId,
                        'workspace_id' => $levelId,
                        'user_id' => $user->id,
                        'status' => 'active',
                        'nis' => $nis,
                        'full_name' => $fullName,
                        'gender' => $isFemale ? 'P' : 'L',
                        'religion' => 'Islam',
                        'place_of_birth' => 'Ciamis',
                        'date_of_birth' => '2008-01-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                        'address' => 'Jl. CIjulang No. ' . $studentCount,
                    ]
                );

                // 3. Attach to Study Group if not already attached
                if (!$group->students()->where('sch_std_students.id', $student->id)->exists()) {
                    $group->students()->attach($student->id);
                }
            }
        }
    }
}
