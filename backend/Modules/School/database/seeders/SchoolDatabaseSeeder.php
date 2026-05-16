<?php

declare(strict_types=1);

namespace Modules\School\Database\Seeders;

use Illuminate\Database\Seeder;

class SchoolDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            SchoolInfrastructureSeeder::class,
            SchoolRoleSeeder::class,
            StaffSampleSeeder::class,
            SchoolSampleUserSeeder::class,
            AcademicInfrastructureSeeder::class,
            SubjectSeeder::class,
            StudentSampleSeeder::class,
            LmsSeeder::class,
            OsisSeeder::class,
            GraduationSeeder::class,
        ]);
    }
}
