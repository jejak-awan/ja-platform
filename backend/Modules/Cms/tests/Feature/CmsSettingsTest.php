<?php

namespace Modules\Cms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\User;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class CmsSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->admin = $this->createAdminUser();
    }

    /**
     * Test admin can get CMS settings.
     */
    public function test_admin_can_get_cms_settings(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/cms/settings');

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
        ]);
    }

    /**
     * Test admin can update CMS settings.
     */
    public function test_admin_can_update_cms_settings(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'Test Site'],
            ['key' => 'site_description', 'value' => 'Test Description'],
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/cms/settings/bulk-update', [
                'settings' => $settings,
            ]);

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test unauthorized user cannot access settings.
     */
    public function test_unauthorized_cannot_access_settings(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/admin/cms/settings');

        $response->assertStatus(403);
    }
}
