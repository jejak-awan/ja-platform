<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Language;
use Modules\Core\Models\User;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->admin = $this->createAdminUser();
    }

    // use RefreshDatabase;

    protected User $admin;

    /**
     * Test admin can list all languages.
     */
    public function test_admin_can_list_languages(): void
    {
        Language::factory()->count(3)->create(['is_active' => true]);
        Language::factory()->count(2)->create(['is_active' => false]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/core/languages');

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'code',
                    'name',
                    'is_active',
                    'is_default',
                ],
            ],
        ]);

        // Should only return active languages
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test admin can create a language.
     */
    public function test_admin_can_create_language(): void
    {
        $languageData = [
            'code' => 'id',
            'name' => 'Indonesian',
            'native_name' => 'Bahasa Indonesia',
            'flag' => '🇮🇩',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 1,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/core/languages', $languageData);

        TestHelpers::assertApiSuccess($response, 201);
        $response->assertJsonFragment([
            'code' => 'id',
            'name' => 'Indonesian',
            'native_name' => 'Bahasa Indonesia',
        ]);

        $this->assertDatabaseHas('languages', [
            'code' => 'id',
            'name' => 'Indonesian',
        ]);
    }

    /**
     * Test language creation requires code and name.
     */
    public function test_language_creation_requires_code_and_name(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/core/languages', []);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['code', 'name']);
    }

    /**
     * Test language code must be unique.
     */
    public function test_language_code_must_be_unique(): void
    {
        Language::factory()->create(['code' => 'en']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/core/languages', [
                'code' => 'en',
                'name' => 'English',
            ]);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['code']);
    }

    /**
     * Test setting a language as default unsets other defaults.
     */
    public function test_setting_language_as_default_unsets_other_defaults(): void
    {
        $existingDefault = Language::factory()->create([
            'is_default' => true,
            'code' => 'en',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/core/languages', [
                'code' => 'id',
                'name' => 'Indonesian',
                'is_default' => true,
            ]);

        TestHelpers::assertApiSuccess($response, 201);

        $this->assertDatabaseHas('languages', [
            'code' => 'id',
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('languages', [
            'id' => $existingDefault->id,
            'is_default' => false,
        ]);
    }

    /**
     * Test admin can update a language.
     */
    public function test_admin_can_update_language(): void
    {
        $language = Language::factory()->create([
            'code' => 'en',
            'name' => 'English',
        ]);

        $updateData = [
            'name' => 'English (US)',
            'native_name' => 'English (US)',
            'is_active' => false,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/core/languages/{$language->id}", $updateData);

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonFragment([
            'name' => 'English (US)',
            'native_name' => 'English (US)',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'name' => 'English (US)',
        ]);
    }

    /**
     * Test admin can delete a language.
     */
    public function test_admin_can_delete_language(): void
    {
        $language = Language::factory()->create([
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/admin/core/languages/{$language->id}");

        TestHelpers::assertApiSuccess($response);

        $this->assertDatabaseMissing('languages', [
            'id' => $language->id,
        ]);
    }

    /**
     * Test cannot delete default language.
     */
    public function test_cannot_delete_default_language(): void
    {
        $language = Language::factory()->create([
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/admin/core/languages/{$language->id}");

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['language']);

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
        ]);
    }

    /**
     * Test admin can get translations for an entity.
     */
    public function test_admin_can_get_translations_for_entity(): void
    {
        $language = Language::factory()->create(['code' => 'es']);
        $content = \Modules\Cms\Models\Content::factory()->create();
        \Modules\Core\Models\Translation::create([
            'translatable_type' => get_class($content),
            'translatable_id' => $content->id,
            'language_code' => 'es',
            'field' => 'title',
            'value' => 'Título en Español',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/core/translations?translatable_type='.get_class($content)."&translatable_id={$content->id}");

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonFragment([
            'field' => 'title',
            'value' => 'Título en Español',
        ]);
    }

    /**
     * Test admin can set translation for an entity.
     */
    public function test_admin_can_set_translation_for_entity(): void
    {
        $language = Language::factory()->create(['code' => 'es']);
        $content = \Modules\Cms\Models\Content::factory()->create();

        $translationData = [
            'translatable_type' => get_class($content),
            'translatable_id' => $content->id,
            'language_code' => 'es',
            'field' => 'title',
            'value' => 'Nuevo Título',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/core/translations', $translationData);

        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseHas('translations', [
            'translatable_id' => $content->id,
            'language_code' => 'es',
            'value' => 'Nuevo Título',
        ]);
    }

    /**
     * Test translation requires all fields.
     */
    public function test_translation_requires_all_fields(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/core/translations', []);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors([
            'translatable_type',
            'translatable_id',
            'language_code',
            'field',
            'value',
        ]);
    }

    /**
     * Test unauthenticated user cannot access languages.
     */
    public function test_unauthenticated_user_cannot_access_languages(): void
    {
        $response = $this->getJson('/api/v1/admin/core/languages');
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/admin/core/languages', []);
        $response->assertStatus(401);
    }

    /**
     * Test user without permission cannot manage languages.
     */
    public function test_user_without_permission_cannot_manage_languages(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/admin/core/languages');

        $response->assertStatus(403);
    }
}
