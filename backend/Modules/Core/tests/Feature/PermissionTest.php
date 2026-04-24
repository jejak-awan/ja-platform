<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Cms\Models\Category;
use Modules\Cms\Models\Content;
use Modules\Cms\Models\Media;
use Modules\Core\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }
    // use RefreshDatabase;

    /**
     * Test admin can access all protected endpoints.
     */
    public function test_admin_can_access_all_protected_endpoints(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        // Test content management
        $response = $this->getJson('/api/v1/admin/cms/contents');
        TestHelpers::assertApiSuccess($response);

        // Test media management
        $response = $this->getJson('/api/v1/admin/cms/media');
        TestHelpers::assertApiSuccess($response);

        // Test categories
        $response = $this->getJson('/api/v1/admin/cms/categories');
        TestHelpers::assertApiSuccess($response);

        // Test users
        $response = $this->getJson('/api/v1/admin/core/users');
        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test user without permission cannot create content.
     */
    public function test_user_without_permission_cannot_create_content(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $contentData = TestHelpers::getContentData();

        $response = $this->postJson('/api/v1/admin/cms/contents', $contentData);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Unauthorized',
        ]);
    }

    /**
     * Test user with create content permission can create content.
     */
    public function test_user_with_create_content_permission_can_create_content(): void
    {
        $user = $this->createUser();
        $permission = Permission::firstOrCreate(['name' => 'create content', 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        $this->actingAs($user, 'sanctum');

        $contentData = TestHelpers::getContentData();

        $response = $this->postJson('/api/v1/admin/cms/contents', $contentData);

        TestHelpers::assertApiSuccess($response, 201);
    }

    /**
     * Test user without permission cannot update content.
     */
    public function test_user_without_permission_cannot_update_content(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $content = Content::factory()->create();

        $response = $this->putJson("/api/v1/admin/cms/contents/{$content->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test user with edit content permission can update content.
     */
    public function test_user_with_edit_content_permission_can_update_content(): void
    {
        $user = $this->createUser();
        $permission = Permission::firstOrCreate(['name' => 'edit content', 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        $this->actingAs($user, 'sanctum');

        // User can only edit their own content without 'manage content' permission
        $content = Content::factory()->create(['author_id' => $user->id, 'status' => 'draft']);

        $response = $this->putJson("/api/v1/admin/cms/contents/{$content->id}", array_merge(
            $content->toArray(),
            ['title' => 'Updated Title']
        ));

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test user without permission cannot delete content.
     */
    public function test_user_without_permission_cannot_delete_content(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $content = Content::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/cms/contents/{$content->id}");

        $response->assertStatus(403);
    }

    /**
     * Test user with delete content permission can delete content.
     */
    public function test_user_with_delete_content_permission_can_delete_content(): void
    {
        $user = $this->createUser();
        $permission = Permission::firstOrCreate(['name' => 'delete content', 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        $this->actingAs($user, 'sanctum');

        $content = Content::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/cms/contents/{$content->id}");

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test user without permission cannot manage media.
     */
    public function test_user_without_permission_cannot_manage_media(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $media = Media::factory()->create([
            'author_id' => $user->id,
        ]);

        // Test update
        $response = $this->putJson("/api/v1/admin/cms/media/{$media->id}", [
            'name' => 'Updated Name',
        ]);
        $response->assertStatus(403);

        // Test delete
        $response = $this->deleteJson("/api/v1/admin/cms/media/{$media->id}");
        $response->assertStatus(403);
    }

    /**
     * Test user with edit/delete media permissions can manage media CRUD actions.
     */
    public function test_user_with_manage_media_permission_can_manage_media(): void
    {
        $user = $this->createUser();
        Permission::firstOrCreate(['name' => 'edit media', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete media', 'guard_name' => 'web']);
        $user->givePermissionTo(['edit media', 'delete media']);
        $this->actingAs($user, 'sanctum');

        $media = Media::factory()->create([
            'author_id' => $user->id,
        ]);

        // Test update
        $response = $this->putJson("/api/v1/admin/cms/media/{$media->id}", [
            'name' => 'Updated Name',
        ]);
        TestHelpers::assertApiSuccess($response);

        // Test delete
        $media2 = Media::factory()->create([
            'author_id' => $user->id,
        ]);
        $response = $this->deleteJson("/api/v1/admin/cms/media/{$media2->id}");
        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test user without permission cannot manage categories.
     */
    public function test_user_without_permission_cannot_manage_categories(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $category = Category::factory()->create();

        // Test update
        $response = $this->putJson("/api/v1/admin/cms/categories/{$category->id}", [
            'name' => 'Updated Category',
        ]);
        $response->assertStatus(403);

        // Test delete
        $response = $this->deleteJson("/api/v1/admin/cms/categories/{$category->id}");
        $response->assertStatus(403);
    }

    /**
     * Test user with manage categories permission can manage categories.
     */
    public function test_user_with_manage_categories_permission_can_manage_categories(): void
    {
        $user = $this->createUser();
        // Grant both 'view categories' (for route access) and 'manage categories' (for controller logic)
        Permission::firstOrCreate(['name' => 'view categories', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage categories', 'guard_name' => 'web']);
        $user->givePermissionTo(['view categories', 'manage categories']);
        $this->actingAs($user, 'sanctum');

        $category = Category::factory()->create();

        // Test update
        $response = $this->putJson("/api/v1/admin/cms/categories/{$category->id}", array_merge(
            $category->toArray(),
            ['name' => 'Updated Category']
        ));
        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test user without permission cannot manage users.
     */
    public function test_user_without_permission_cannot_manage_users(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $targetUser = User::factory()->create();

        // Test view users list
        $response = $this->getJson('/api/v1/admin/core/users');
        $response->assertStatus(403);

        // Test update user
        $response = $this->putJson("/api/v1/admin/core/users/{$targetUser->id}", [
            'name' => 'Updated Name',
        ]);
        $response->assertStatus(403);
    }

    /**
     * Test user with manage users permission can manage users.
     */
    public function test_user_with_manage_users_permission_can_manage_users(): void
    {
        $user = $this->createUser();
        $permission = Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        $this->actingAs($user, 'sanctum');

        // Test view users list
        $response = $this->getJson('/api/v1/admin/core/users');
        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test admin role has all permissions.
     */
    public function test_admin_role_has_all_permissions(): void
    {
        $admin = $this->createAdminUser();

        $permissions = [
            'create content',
            'edit content',
            'delete content',
            'manage media',
            'manage categories',
            'manage users',
        ];

        foreach ($permissions as $permissionName) {
            $this->assertTrue($admin->hasPermissionTo($permissionName), "Admin should have {$permissionName} permission");
        }
    }

    /**
     * Test role assignment works correctly.
     */
    public function test_role_assignment_works_correctly(): void
    {
        $user = $this->createUser();
        $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

        // Give role some permissions
        $permission = Permission::firstOrCreate(['name' => 'edit content', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $user->assignRole($role);

        $this->assertTrue($user->hasRole('editor'));
        $this->assertTrue($user->hasPermissionTo('edit content'));
    }

    /**
     * Test permission can be assigned directly to user.
     */
    public function test_permission_can_be_assigned_directly_to_user(): void
    {
        $user = $this->createUser();
        $permission = Permission::firstOrCreate(['name' => 'create content', 'guard_name' => 'web']);

        $user->givePermissionTo($permission);

        $this->assertTrue($user->hasPermissionTo('create content'));
    }

    /**
     * Test user can have multiple permissions.
     */
    public function test_user_can_have_multiple_permissions(): void
    {
        $user = $this->createUser();

        $permissions = [
            Permission::firstOrCreate(['name' => 'create content', 'guard_name' => 'web']),
            Permission::firstOrCreate(['name' => 'edit content', 'guard_name' => 'web']),
            Permission::firstOrCreate(['name' => 'manage media', 'guard_name' => 'web']),
        ];

        $user->givePermissionTo($permissions);

        foreach ($permissions as $permission) {
            $this->assertTrue($user->hasPermissionTo($permission->name));
        }
    }

    /**
     * Test user can have multiple roles.
     */
    public function test_user_can_have_multiple_roles(): void
    {
        $user = $this->createUser();

        $role1 = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $role2 = Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web']);

        $user->assignRole([$role1, $role2]);

        $this->assertTrue($user->hasRole('editor'));
        $this->assertTrue($user->hasRole('author'));
    }

    /**
     * Test admin can list roles.
     */
    public function test_admin_can_list_roles(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web']);

        $response = $this->getJson('/api/v1/admin/core/roles');

        TestHelpers::assertApiSuccess($response);
        // Assert at least 3 roles exist (admin, editor, author may be among many)
        $this->assertGreaterThanOrEqual(3, count($response->json('data.data') ?? $response->json('data')));
    }

    /**
     * Test user without permission cannot list roles.
     */
    public function test_user_without_permission_cannot_list_roles(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/admin/core/roles');

        $response->assertStatus(403);
    }

    /**
     * Test permission check works with middleware.
     */
    public function test_permission_check_works_with_middleware(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        // Try to access endpoint that requires 'manage settings' permission
        $response = $this->getJson('/api/v1/admin/core/settings');

        $response->assertStatus(403);
    }

    /**
     * Test admin can access settings with permission.
     */
    public function test_admin_can_access_settings_with_permission(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson('/api/v1/admin/core/settings');

        TestHelpers::assertApiSuccess($response);
    }
}
