<?php

namespace Modules\School\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\School\Models\AcademicYear;
use Modules\School\Models\School;
use Modules\School\Models\SchoolLevel;
use Modules\School\Models\Semester;
use Modules\School\Models\Student;
use Modules\School\Models\Violation;
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

        $this->school = School::create(['name' => 'Test School', 'address' => 'Test Address']);
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

        $level = SchoolLevel::create(['school_id' => $this->school->id, 'level' => 'SMK', 'name' => 'High School']);
        $this->student = Student::create([
            'school_id' => $this->school->id,
            'school_level_id' => $level->id,
            'full_name' => 'John Doe',
            'gender' => 'L',
        ]);
    }

    /** @test */
    public function it_can_record_attendance()
    {
        $response = $this->actingAsAdmin()->postJson('/api/v1/admin/operations/attendance', [
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'date' => '2023-10-01',
            'status' => 'H', // Hadir
            'notes' => 'On time',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'status' => 'H',
        ]);
    }

    /** @test */
    public function it_can_record_violation_and_calculate_points()
    {
        // Record first violation
        $this->actingAsAdmin()->postJson('/api/v1/admin/operations/violations', [
            'student_id' => $this->student->id,
            'category' => 'Late',
            'points' => 5,
            'date' => '2023-10-01',
            'description' => 'Arrived 10 mins late',
        ])->assertStatus(201);

        // Record second violation
        $this->actingAsAdmin()->postJson('/api/v1/admin/operations/violations', [
            'student_id' => $this->student->id,
            'category' => 'Dress Code',
            'points' => 10,
            'date' => '2023-10-02',
            'description' => 'Wrong uniform',
        ])->assertStatus(201);

        // Verify total points
        $response = $this->actingAsAdmin()->getJson("/api/v1/admin/operations/violations/points/{$this->student->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.total_points', 15);
    }
}
