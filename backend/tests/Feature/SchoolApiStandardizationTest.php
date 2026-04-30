<?php

namespace Tests\Feature;

use Tests\TestCase;

class SchoolApiStandardizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }
    /**
     * Test standardized response format for schools list.
     */
    public function test_school_index_structure(): void
    {
        $response = $this->actingAsAdmin()->getJson('/api/v1/admin/school');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message',
            ])
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Test Academic overview structure.
     */
    public function test_academic_overview_structure(): void
    {
        $response = $this->actingAsAdmin()->getJson('/api/v1/admin/academic/overview');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'years_count',
                    'subjects_count',
                    'study_groups_count',
                ],
                'message',
            ]);
    }

    // Removed test_lms_overview_structure

    /**
     * Test Operations attendance overview structure.
     */
    public function test_operations_attendance_overview_structure(): void
    {
        $response = $this->actingAsAdmin()->getJson('/api/v1/admin/operations/attendance/overview');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message',
            ]);
    }
}
