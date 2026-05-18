<?php

namespace Modules\School\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\School\Models\Academic\AcademicYear;
use Modules\School\Models\Academic\Department;
use Modules\School\Models\Academic\Semester;
use Modules\School\Models\Academic\StudyGroup;

class AcademicInfrastructureSeeder extends Seeder
{
    public function run(): void
    {
        $school = DB::table('sch_ins_schools')->where('npsn', '10000001')->first();
        $unit = $school ? DB::table('sch_ins_levels')->where('school_id', $school->id)->first() : null;
        if (! $school || ! $unit) {
            $this->command->warn('No school/unit found. Skipping AcademicInfrastructureSeeder.');

            return;
        }
        $schoolId = $school->id;
        $levelId = $unit->id;

        // 1. Create Academic Year
        $year = AcademicYear::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $schoolId, 'year' => '2023/2024'],
            ['is_active' => true, 'workspace_id' => $levelId]
        );

        // 2. Create Semesters
        Semester::withoutGlobalScopes()->updateOrCreate(
            ['academic_year_id' => $year->id, 'type' => 'ganjil', 'workspace_id' => $levelId],
            ['is_active' => true]
        );
        Semester::withoutGlobalScopes()->updateOrCreate(
            ['academic_year_id' => $year->id, 'type' => 'genap', 'workspace_id' => $levelId],
            ['is_active' => false]
        );

        // 3. Create Departments (SMK Majors)
        $deptTKJ = Department::withoutGlobalScopes()->updateOrCreate(
            ['workspace_id' => $levelId, 'code' => 'TKJ'],
            ['name' => 'Teknik Komputer & Jaringan', 'description' => 'Jurusan Teknik Komputer dan Jaringan']
        );
        $deptRPL = Department::withoutGlobalScopes()->updateOrCreate(
            ['workspace_id' => $levelId, 'code' => 'RPL'],
            ['name' => 'Rekayasa Perangkat Lunak', 'description' => 'Jurusan Rekayasa Perangkat Lunak']
        );
        $deptAKL = Department::withoutGlobalScopes()->updateOrCreate(
            ['workspace_id' => $levelId, 'code' => 'AKL'],
            ['name' => 'Akuntansi & Keuangan Lembaga', 'description' => 'Jurusan Akuntansi']
        );

        // 4. Create Study Groups (Classes)
        $classes = [
            ['name' => 'X TKJ 1', 'dept' => $deptTKJ, 'teacher_id' => null],
            ['name' => 'X RPL 1', 'dept' => $deptRPL, 'teacher_id' => null],
            ['name' => 'XI TKJ 1', 'dept' => $deptTKJ, 'teacher_id' => null],
            ['name' => 'XI RPL 1', 'dept' => $deptRPL, 'teacher_id' => null],
            ['name' => 'XII TKJ 1', 'dept' => $deptTKJ, 'teacher_id' => null],
            ['name' => 'XII AKL 1', 'dept' => $deptAKL, 'teacher_id' => null],
        ];

        foreach ($classes as $class) {
            StudyGroup::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $schoolId,
                    'academic_year_id' => $year->id,
                    'name' => $class['name'],
                ],
                [
                    'workspace_id' => $levelId,
                    'department_id' => $class['dept']->id,
                    'homeroom_teacher_id' => $class['teacher_id'],
                ]
            );
        }
    }
}
