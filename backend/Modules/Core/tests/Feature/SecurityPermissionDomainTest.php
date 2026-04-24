<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Models\User;
use Spatie\Permission\Models\Permission;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class SecurityPermissionDomainTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_manage_settings_only_cannot_access_strict_security_domains(): void
    {
        $user = $this->createUser();
        $user->givePermissionTo('manage settings');
        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/v1/admin/core/security/blocklist')->assertStatus(403);
        $this->getJson('/api/v1/admin/core/security/file-integrity')->assertStatus(403);
        $this->getJson('/api/v1/admin/core/security/maintenance')->assertStatus(403);
        $this->getJson('/api/v1/admin/core/security/kpi')->assertStatus(403);
    }

    public function test_manage_security_logs_can_access_logs_domain(): void
    {
        $user = $this->createUser();
        $user->givePermissionTo('manage security logs');
        $this->actingAs($user, 'sanctum');

        $journal = $this->getJson('/api/v1/admin/core/security/journal');
        TestHelpers::assertApiSuccess($journal);

        $alerts = $this->getJson('/api/v1/admin/core/security/alerts');
        TestHelpers::assertApiSuccess($alerts);

        $kpi = $this->getJson('/api/v1/admin/core/security/kpi');
        TestHelpers::assertApiSuccess($kpi);
    }

    public function test_manage_security_ip_lists_can_access_ip_list_domain(): void
    {
        $user = $this->createUser();
        $user->givePermissionTo('manage security ip-lists');
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/admin/core/security/blocklist');
        TestHelpers::assertApiSuccess($response);
    }

    public function test_manage_security_integrity_can_access_integrity_domain(): void
    {
        $user = $this->createUser();
        $user->givePermissionTo('manage security integrity');
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/admin/core/security/file-integrity');
        TestHelpers::assertApiSuccess($response);
    }

    public function test_manage_security_maintenance_can_access_maintenance_domain(): void
    {
        $user = $this->createUser();
        $user->givePermissionTo('manage security maintenance');
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/admin/core/security/maintenance');
        TestHelpers::assertApiSuccess($response);
    }

    public function test_manage_security_operations_can_access_all_domains(): void
    {
        $user = $this->createUser();
        $user->givePermissionTo('manage security operations');
        $this->actingAs($user, 'sanctum');

        TestHelpers::assertApiSuccess($this->getJson('/api/v1/admin/core/security/journal'));
        TestHelpers::assertApiSuccess($this->getJson('/api/v1/admin/core/security/kpi'));
        TestHelpers::assertApiSuccess($this->getJson('/api/v1/admin/core/security/blocklist'));
        TestHelpers::assertApiSuccess($this->getJson('/api/v1/admin/core/security/file-integrity'));
        TestHelpers::assertApiSuccess($this->getJson('/api/v1/admin/core/security/maintenance'));
    }

    public function test_user_without_security_permissions_is_denied(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/v1/admin/core/security/journal')->assertStatus(403);
        $this->getJson('/api/v1/admin/core/security/blocklist')->assertStatus(403);
    }
}
