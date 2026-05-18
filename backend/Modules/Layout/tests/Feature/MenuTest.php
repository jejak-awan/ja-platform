<?php

namespace Modules\Layout\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Layout\Models\Menu;
use Modules\System\Models\User;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->admin = $this->createAdminUser();
    }

    public function test_can_list_menus(): void
    {
        Menu::create(['name' => 'Main Menu', 'slug' => 'main', 'location' => 'header']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/layout/menus');

        $response->assertStatus(200)
            ->assertJsonPath('data.data.0.name', 'Main Menu');
    }

    public function test_can_create_menu(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/layout/menus', [
                'name' => 'Footer Menu',
                'location' => 'footer',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('lay_menus', ['name' => 'Footer Menu']);
    }

    public function test_can_add_menu_item(): void
    {
        $menu = Menu::create(['name' => 'Main Menu', 'slug' => 'main', 'location' => 'header']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/manage/layout/menus/{$menu->id}/items", [
                'title' => 'Home',
                'url' => '/',
                'type' => 'link',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('lay_menu_items', [
            'menu_id' => $menu->id,
            'title' => 'Home',
        ]);
    }

    public function test_can_get_menu_by_location(): void
    {
        $menu = Menu::create(['name' => 'Main Menu', 'slug' => 'main', 'location' => 'header', 'is_active' => true]);
        $menu->items()->create(['title' => 'Home', 'url' => '/', 'type' => 'link']);

        $response = $this->getJson('/api/v1/public/layout/menus/location/header');

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Main Menu')
            ->assertJsonCount(1, 'data.parent_items');
    }
}
