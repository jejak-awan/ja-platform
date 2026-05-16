<?php

namespace Modules\School\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\School\Models\Academic\Department;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SchoolModuleCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    #[Test]
    public function it_can_perform_academic_year_crud(): void
    {
        $school = School::factory()->create();

        // Create
        $response = $this->actingAsAdmin()->postJson('/api/v1/manage/school/academic/years', [
            'school_id' => $school->id,
            'year' => '2023/2024',
            'is_active' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.year', '2023/2024');

        $yearId = $response->json('data.id');

        // Update
        $response = $this->actingAsAdmin()->putJson("/api/v1/manage/school/academic/years/{$yearId}", [
            'year' => '2023/2024 UPDATED',
            'is_active' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.year', '2023/2024 UPDATED');

        // Delete
        $response = $this->actingAsAdmin()->deleteJson("/api/v1/manage/school/academic/years/{$yearId}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('sch_acad_years', ['id' => $yearId]);
    }

    #[Test]
    public function it_can_perform_subject_crud(): void
    {
        $school = School::factory()->create();

        // Create
        $response = $this->actingAsAdmin()->postJson('/api/v1/manage/school/academic/subjects', [
            'school_id' => $school->id,
            'code' => 'MATH101',
            'name' => 'Mathematics',
            'group' => 'A',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $subjectId = $response->json('data.id');

        // Update
        $response = $this->actingAsAdmin()->putJson("/api/v1/manage/school/academic/subjects/{$subjectId}", [
            'code' => 'MATH101-U',
            'name' => 'Mathematics Updated',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.code', 'MATH101-U');

        // Delete
        $response = $this->actingAsAdmin()->deleteJson("/api/v1/manage/school/academic/subjects/{$subjectId}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('sch_acad_subjects', ['id' => $subjectId]);
    }

    #[Test]
    public function it_can_perform_student_crud(): void
    {
        $school = School::factory()->create();
        $level = SchoolUnit::factory()->forSchool($school)->smk()->create();
        $dept = Department::create(['workspace_id' => $level->id, 'name' => 'Science', 'code' => 'SCI']);

        // Create
        $response = $this->actingAsAdmin()->postJson('/api/v1/manage/school/students', [
            'school_id' => $school->id,
            'level_id' => $level->id,
            'department_id' => $dept->id,
            'full_name' => 'John Doe',
            'nis' => '12345',
            'gender' => 'L',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $studentId = $response->json('data.id');

        // Update
        $response = $this->actingAsAdmin()->putJson("/api/v1/manage/school/students/{$studentId}", [
            'full_name' => 'John Doe Updated',
            'gender' => 'L',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.full_name', 'John Doe Updated');

        // Delete
        $response = $this->actingAsAdmin()->deleteJson("/api/v1/manage/school/students/{$studentId}");
        $response->assertStatus(200);

        $this->assertSoftDeleted('sch_std_students', ['id' => $studentId]);
    }
}
