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

    public function test_admin_sarpras_can_access_sarpras_routes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin-sarpras');

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/admin/sarpras/overview')->assertOk();
    }

    public function test_admin_kesiswaan_cannot_access_sarpras(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin-kesiswaan');

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/admin/sarpras/overview')->assertForbidden();
    }
}
