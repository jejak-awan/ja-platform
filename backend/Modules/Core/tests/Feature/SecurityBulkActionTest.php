<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\User;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SecurityBulkActionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => 'manage security ip-lists', 'guard_name' => 'web']);
        $this->admin->givePermissionTo($permission);
    }

    public function test_bulk_block_requires_ip_addresses_array(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/core/security/bulk-block', [
                'ips' => ['1.2.3.4', '5.6.7.8'], // Incorrect key
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ip_addresses']);
    }

    public function test_bulk_block_succeeds_with_correct_payload(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/core/security/bulk-block', [
                'ip_addresses' => ['1.1.1.1', '2.2.2.2'],
                'reason' => 'Bulk blocking test',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'blocked' => 2,
                    'skipped' => 0,
                ],
            ]);
    }

    public function test_bulk_unblock_succeeds_with_correct_payload(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/core/security/bulk-unblock', [
                'ip_addresses' => ['1.1.1.1', '2.2.2.2'],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_remove_from_whitelist_succeeds_with_direct_payload(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/core/security/remove-whitelist', [
                'ip_address' => '1.2.3.4',
            ]);

        $response->assertStatus(200);
    }

    public function test_bulk_remove_whitelist_succeeds_with_correct_payload(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/core/security/bulk-remove-whitelist', [
                'ip_addresses' => ['1.2.3.4', '5.6.7.8'],
            ]);

        $response->assertStatus(200);
    }
}
