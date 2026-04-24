<?php

namespace Modules\Cms\Tests\Feature;

use Modules\Cms\Models\Tag;
use Modules\Core\Models\User;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class TagTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    /**
     * Test admin can list all tags.
     */
    public function test_admin_can_list_all_tags(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        Tag::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/admin/cms/tags');

        TestHelpers::assertApiSuccess($response);
        $this->assertIsArray($response->json('data'));
    }

    /**
     * Test admin can get tag statistics.
     */
    public function test_admin_can_get_tag_statistics(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        Tag::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/admin/cms/tags/statistics');

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test admin can create tag.
     */
    public function test_admin_can_create_tag(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $tagData = [
            'name' => 'Test Tag '.uniqid(),
            'slug' => 'test-tag-'.uniqid(),
            'description' => 'Test tag description',
        ];

        $response = $this->postJson('/api/v1/admin/cms/tags', $tagData);

        TestHelpers::assertApiSuccess($response, 201);
        $this->assertDatabaseHas('tags', [
            'name' => $tagData['name'],
            'slug' => $tagData['slug'],
        ]);
    }

    /**
     * Test tag creation requires name.
     */
    public function test_tag_creation_requires_name(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson('/api/v1/admin/cms/tags', []);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['name']);
    }

    /**
     * Test tag name must be unique.
     */
    public function test_tag_name_must_be_unique(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $existing = Tag::factory()->create();

        $response = $this->postJson('/api/v1/admin/cms/tags', [
            'name' => $existing->name, // Duplicate name
        ]);

        TestHelpers::assertApiValidationError($response);
    }

    /**
     * Test admin can view tag details.
     */
    public function test_admin_can_view_tag_details(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $tag = Tag::factory()->create();

        $response = $this->getJson("/api/v1/admin/cms/tags/{$tag->id}");

        TestHelpers::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'id' => $tag->id,
                'name' => $tag->name,
            ],
        ]);
    }

    /**
     * Test admin can update tag.
     */
    public function test_admin_can_update_tag(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $tag = Tag::factory()->create();

        $response = $this->putJson("/api/v1/admin/cms/tags/{$tag->id}", [
            'name' => 'Updated Tag Name',
            'slug' => 'updated-tag-name-'.uniqid(),
        ]);

        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Updated Tag Name',
        ]);
    }

    /**
     * Test admin can delete tag.
     */
    public function test_admin_can_delete_tag(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $tag = Tag::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/cms/tags/{$tag->id}");

        TestHelpers::assertApiSuccess($response);
        $this->assertSoftDeleted('tags', [
            'id' => $tag->id,
        ]);
    }

    /**
     * Test admin can bulk delete tags.
     */
    public function test_admin_can_bulk_delete_tags(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $tags = Tag::factory()->count(3)->create();
        $ids = $tags->pluck('id')->toArray();

        $response = $this->postJson('/api/v1/admin/cms/tags/bulk-delete', [
            'ids' => $ids,
        ]);

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test public can list tags (for frontend).
     */
    public function test_public_can_list_tags(): void
    {
        Tag::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/ja/tags');

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test unauthenticated user cannot create tags.
     */
    public function test_unauthenticated_user_cannot_create_tags(): void
    {
        $response = $this->postJson('/api/v1/admin/cms/tags', [
            'name' => 'Test Tag',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test user without permission cannot manage tags.
     */
    public function test_user_without_permission_cannot_manage_tags(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/v1/admin/cms/tags', [
            'name' => 'Test Tag',
        ]);

        $response->assertStatus(403);
    }
}
