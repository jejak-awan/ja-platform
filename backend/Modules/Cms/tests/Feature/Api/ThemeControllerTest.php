<?php

namespace Modules\Cms\Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Cms\Models\Theme;
use Modules\Core\Models\User;
use Tests\TestCase;

class ThemeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage themes', 'guard_name' => 'web']);
        $this->admin->givePermissionTo($permission);
        $this->actingAs($this->admin);
    }

    public function test_index()
    {
        Theme::factory()->count(3)->create(['type' => 'frontend']);

        $response = $this->getJson('/api/v1/admin/cms/themes?type=frontend');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_show()
    {
        $theme = Theme::factory()->create();

        $response = $this->getJson("/api/v1/admin/cms/themes/{$theme->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('data.slug', $theme->slug);
    }

    public function test_update()
    {
        $theme = Theme::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/v1/admin/cms/themes/{$theme->slug}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('New Name', $theme->fresh()->name);
    }

    public function test_destroy()
    {
        $theme = Theme::factory()->create(['is_active' => false]);

        $response = $this->deleteJson("/api/v1/admin/cms/themes/{$theme->slug}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('themes', ['id' => $theme->id]);
    }

    public function test_cannot_destroy_active_theme()
    {
        $theme = Theme::factory()->create(['is_active' => true]);

        $response = $this->deleteJson("/api/v1/admin/cms/themes/{$theme->slug}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['theme']);
    }

    public function test_activate_and_deactivate()
    {
        $theme = Theme::factory()->create(['is_active' => false, 'type' => 'frontend']);

        $response = $this->postJson("/api/v1/admin/cms/themes/{$theme->slug}/activate");
        $response->assertStatus(200);
        $this->assertTrue($theme->fresh()->is_active);

        $response = $this->postJson("/api/v1/admin/cms/themes/{$theme->slug}/deactivate");
        $response->assertStatus(200);
        $this->assertFalse($theme->fresh()->is_active);
    }

    public function test_get_active()
    {
        $theme = Theme::factory()->create(['is_active' => true, 'type' => 'frontend']);

        $response = $this->getJson('/api/v1/ja/themes/active?type=frontend');

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $theme->id);
    }

    public function test_update_settings()
    {
        $theme = Theme::factory()->create(['settings' => ['a' => 1]]);

        $response = $this->putJson("/api/v1/admin/cms/themes/{$theme->slug}/settings", [
            'settings' => ['b' => 2],
        ]);

        $response->assertStatus(200);
        $this->assertEquals(2, $theme->fresh()->settings['b']);
        $this->assertEquals(1, $theme->fresh()->settings['a']);
    }

    public function test_update_custom_css()
    {
        $theme = Theme::factory()->create();

        $response = $this->putJson("/api/v1/admin/cms/themes/{$theme->slug}/custom-css", [
            'custom_css' => 'body { color: red; }',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('body { color: red; }', $theme->fresh()->custom_css);
    }

    public function test_validate_theme()
    {
        $theme = Theme::factory()->create();

        $mockService = \Mockery::mock(\Modules\Cms\Services\ThemeService::class);
        $mockService->shouldReceive('validateTheme')->once()->andReturn([]);
        $this->app->instance(\Modules\Cms\Services\ThemeService::class, $mockService);

        $response = $this->postJson("/api/v1/admin/cms/themes/{$theme->slug}/validate");

        $response->assertStatus(200)
            ->assertJsonPath('data.valid', true);
    }

    public function test_scan_themes()
    {
        $response = $this->postJson('/api/v1/admin/cms/themes/scan');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['themes', 'count']]);
    }

    public function test_get_setting()
    {
        $theme = Theme::factory()->create(['settings' => ['key' => 'value']]);

        $response = $this->getJson("/api/v1/admin/cms/themes/{$theme->slug}/setting?key=key");

        $response->assertStatus(200)
            ->assertJsonPath('data.value', 'value');
    }

    public function test_locations()
    {
        $theme = Theme::factory()->create(['is_active' => true, 'type' => 'frontend']);

        $response = $this->getJson('/api/v1/admin/cms/themes/active/locations?type=frontend');

        $response->assertStatus(200);
    }

    public function test_get_components_config_composables()
    {
        $theme = Theme::factory()->create(['slug' => 'test-theme']);

        $response = $this->getJson("/api/v1/admin/cms/themes/{$theme->slug}/components");
        $response->assertStatus(200);

        $response = $this->getJson("/api/v1/admin/cms/themes/{$theme->slug}/config");
        $response->assertStatus(200);

        $response = $this->getJson("/api/v1/admin/cms/themes/{$theme->slug}/composables");
        $response->assertStatus(200);
    }
}
