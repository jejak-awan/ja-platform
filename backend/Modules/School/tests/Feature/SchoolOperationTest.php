<?php

namespace Modules\School\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\School\Models\Academic\AcademicYear;
use Modules\School\Models\Academic\Semester;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;
use Modules\School\Models\Student\Student;
use Modules\School\Models\Student\Violation;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SchoolOperationTest extends TestCase
{
    use RefreshDatabase;

    protected $school;

    protected $academicYear;

    protected $semester;

    protected $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();

        $this->school = School::factory()->create();
        $this->academicYear = AcademicYear::create([
            'school_id' => $this->school->id,
            'year' => '2023/2024',
            'is_active' => true,
        ]);
        $this->semester = Semester::create([
            'academic_year_id' => $this->academicYear->id,
            'type' => 'ganjil',
            'is_active' => true,
        ]);

        $level = SchoolUnit::factory()->forSchool($this->school)->smk()->create();
        $this->student = Student::factory()->forSchool($this->school)->forLevel($level)->create([
            'full_name' => 'John Doe',
            'gender' => 'L',
        ]);
    }

    #[Test]
    public function it_can_record_attendance()
    {
        $response = $this->actingAsAdmin()->postJson('/api/v1/manage/school/operations/attendance', [
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'date' => '2023-10-01',
            'status' => 'H', // Hadir
            'notes' => 'On time',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('sch_acad_attendances', [
            'student_id' => $this->student->id,
            'status' => 'H',
        ]);
    }

    #[Test]
    public function it_can_record_violation_and_calculate_points()
    {
        // Record first violation
        $this->actingAsAdmin()->postJson('/api/v1/manage/school/operations/violations', [
            'student_id' => $this->student->id,
            'category' => 'Late',
            'points' => 5,
            'date' => '2023-10-01',
            'description' => 'Arrived 10 mins late',
        ])->assertStatus(201);

        // Record second violation
        $this->actingAsAdmin()->postJson('/api/v1/manage/school/operations/violations', [
            'student_id' => $this->student->id,
            'category' => 'Dress Code',
            'points' => 10,
            'date' => '2023-10-02',
            'description' => 'Wrong uniform',
        ])->assertStatus(201);

        // Verify total points
        $response = $this->actingAsAdmin()->getJson("/api/v1/manage/school/operations/violations/points/{$this->student->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.total_points', 15);
    }
}
