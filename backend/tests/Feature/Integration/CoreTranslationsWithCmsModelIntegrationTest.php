<?php

namespace Tests\Feature\Integration;

use Modules\System\Models\Language;
use Modules\System\Models\Translation;
use Modules\System\Models\User;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

/**
 * Integration tests: Core translation endpoints with a CMS model as translatable.
 * Located outside `Modules/Core` to keep Core module independent from CMS.
 */
class CoreTranslationsWithCmsModelIntegrationTest extends TestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->admin = $this->createAdminUser();
    }

    public function test_admin_can_get_translations_for_cms_entity(): void
    {
        $language = Language::factory()->create(['code' => 'es']);
        $content = \Modules\Cms\Models\Content::factory()->create();

        Translation::create([
            'translatable_type' => get_class($content),
            'translatable_id' => $content->id,
            'language_code' => $language->code,
            'field' => 'title',
            'value' => 'Título en Español',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/system/translations?translatable_type='.get_class($content)."&translatable_id={$content->id}");

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonFragment([
            'field' => 'title',
            'value' => 'Título en Español',
        ]);
    }

    public function test_admin_can_set_translation_for_cms_entity(): void
    {
        $language = Language::factory()->create(['code' => 'es']);
        $content = \Modules\Cms\Models\Content::factory()->create();

        $translationData = [
            'translatable_type' => get_class($content),
            'translatable_id' => $content->id,
            'language_code' => $language->code,
            'field' => 'title',
            'value' => 'Nuevo Título',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/system/translations', $translationData);

        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseHas('sys_translations', [
            'translatable_id' => $content->id,
            'language_code' => $language->code,
            'value' => 'Nuevo Título',
        ]);
    }
}

