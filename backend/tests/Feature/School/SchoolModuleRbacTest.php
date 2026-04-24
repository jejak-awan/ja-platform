<?php

namespace Tests\Feature\School;

use Modules\Core\Models\User;
use Modules\School\Models\Institution\School;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cross-checks School admin route groups: sarpras, finance, LMS, etc.
 */
class SchoolModuleRbacTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->seed(\Modules\School\Database\Seeders\SchoolRoleSeeder::class);
    }

    public function test_admin_sarpras_can_access_sarpras_routes_not_finance(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin-sarpras');

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/admin/sarpras/overview')->assertOk();
        $this->actingAs($user, 'sanctum')->getJson('/api/v1/admin/finance/summary')->assertForbidden();
    }

    public function test_admin_kesiswaan_cannot_access_sarpras_or_finance(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin-kesiswaan');

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/admin/sarpras/overview')->assertForbidden();
        $this->actingAs($user, 'sanctum')->getJson('/api/v1/admin/finance/summary')->assertForbidden();
    }

    public function test_admin_kurikulum_can_access_lms_overview_not_finance(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin-kurikulum');

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/admin/lms/overview')->assertOk();
        $this->actingAs($user, 'sanctum')->getJson('/api/v1/admin/finance/summary')->assertForbidden();
    }

    public function test_finance_is_isolated_to_school_finance_permissions(): void
    {
        School::factory()->create();

        $user = User::factory()->create();
        $role = Role::findOrCreate('test-finance-only', 'web');
        $role->syncPermissions(['view school finance', 'manage school finance']);
        $user->assignRole('test-finance-only');

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/admin/finance/summary')->assertOk();
        $this->actingAs($user, 'sanctum')->getJson('/api/v1/admin/lms/overview')->assertForbidden();
    }
}
