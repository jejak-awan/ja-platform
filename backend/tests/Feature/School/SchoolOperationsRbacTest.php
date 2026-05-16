<?php

namespace Tests\Feature\School;

use Modules\System\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Verifies operations routes are split by middleware: attendance vs student affairs vs visitors.
 */
class SchoolOperationsRbacTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->seed(\Modules\School\Database\Seeders\SchoolRoleSeeder::class);
    }

    public function test_admin_sarpras_cannot_access_operations_attendance_or_affairs_or_visitors(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin-sarpras');

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/manage/school/operations/attendance/overview')->assertForbidden();
        $this->actingAs($user, 'sanctum')->getJson('/api/v1/manage/school/operations/violations')->assertForbidden();
        $this->actingAs($user, 'sanctum')->getJson('/api/v1/manage/school/operations/visitors')->assertForbidden();
    }

    public function test_admin_kesiswaan_can_access_attendance_and_affairs_but_not_visitors(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin-kesiswaan');

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/manage/school/operations/attendance/overview')->assertOk();
        $this->actingAs($user, 'sanctum')->getJson('/api/v1/manage/school/operations/violations')->assertOk();
        $this->actingAs($user, 'sanctum')->getJson('/api/v1/manage/school/operations/visitors')->assertForbidden();
    }

    public function test_user_with_only_visitor_permissions_can_list_visitors_not_attendance(): void
    {
        $user = User::factory()->create();
        $role = Role::findOrCreate('test-resepsionis', 'web');
        $role->syncPermissions(['view visitors', 'manage visitors']);
        $user->assignRole('test-resepsionis');

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/manage/school/operations/visitors')->assertOk();
        $this->actingAs($user, 'sanctum')->getJson('/api/v1/manage/school/operations/attendance')->assertForbidden();
    }

    /**
     * Attendance middleware allows view students — admin-kurikulum has it, so curriculum staff may hit these routes by design.
     * Users with only LMS/academic curriculum perms (no view students / attendance) must be denied at the gate.
     */
    public function test_curriculum_only_user_without_student_scope_cannot_access_attendance_overview(): void
    {
        $user = User::factory()->create();
        $role = Role::findOrCreate('test-curriculum-only', 'web');
        $role->syncPermissions(['view academic', 'manage academic']);
        $user->assignRole('test-curriculum-only');

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/manage/school/operations/attendance/overview')->assertForbidden();
    }
}

