<?php

namespace Modules\School\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\School\Models\Department;
use Modules\School\Models\School;
use Modules\School\Models\SchoolLevel;
use Tests\TestCase;

class SchoolModuleCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    /** @test */
    public function it_can_perform_academic_year_crud()
    {
        $school = School::create(['name' => 'Test School', 'address' => 'Test Address']);

        // Create
        $response = $this->actingAsAdmin()->postJson('/api/v1/admin/academic/years', [
            'school_id' => $school->id,
            'year' => '2023/2024',
            'is_active' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.year', '2023/2024');

        $yearId = $response->json('data.id');

        // Update
        $response = $this->actingAsAdmin()->putJson("/api/v1/admin/academic/years/{$yearId}", [
            'year' => '2023/2024 UPDATED',
            'is_active' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.year', '2023/2024 UPDATED');

        // Delete
        $response = $this->actingAsAdmin()->deleteJson("/api/v1/admin/academic/years/{$yearId}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('academic_years', ['id' => $yearId]);
    }

    /** @test */
    public function it_can_perform_subject_crud()
    {
        $school = School::create(['name' => 'Test School', 'address' => 'Test Address']);

        // Create
        $response = $this->actingAsAdmin()->postJson('/api/v1/admin/academic/subjects', [
            'school_id' => $school->id,
            'code' => 'MATH101',
            'name' => 'Mathematics',
            'group' => 'A',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $subjectId = $response->json('data.id');

        // Update
        $response = $this->actingAsAdmin()->putJson("/api/v1/admin/academic/subjects/{$subjectId}", [
            'code' => 'MATH101-U',
            'name' => 'Mathematics Updated',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.code', 'MATH101-U');

        // Delete
        $response = $this->actingAsAdmin()->deleteJson("/api/v1/admin/academic/subjects/{$subjectId}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('subjects', ['id' => $subjectId]);
    }

    /** @test */
    public function it_can_perform_student_crud()
    {
        $school = School::create(['name' => 'Test School', 'address' => 'Test Address']);
        $level = SchoolLevel::create(['school_id' => $school->id, 'level' => 'SMK', 'name' => 'High School']);
        $dept = Department::create(['school_level_id' => $level->id, 'name' => 'Science']);

        // Create
        $response = $this->actingAsAdmin()->postJson('/api/v1/admin/students', [
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
        $response = $this->actingAsAdmin()->putJson("/api/v1/admin/students/{$studentId}", [
            'full_name' => 'John Doe Updated',
            'gender' => 'L',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.full_name', 'John Doe Updated');

        // Delete
        $response = $this->actingAsAdmin()->deleteJson("/api/v1/admin/students/{$studentId}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('students', ['id' => $studentId]);
    }
}
