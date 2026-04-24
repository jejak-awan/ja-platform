<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\User;
use Tests\TestCase;

class SvgUploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        Storage::fake('public');
    }

    public function test_media_upload_sanitizes_svg()
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);
        $user->givePermissionTo('upload media');

        $maliciousSvg = '<?xml version="1.0"?><svg><script>alert("xss")</script><circle cx="50" cy="50" r="40"/></svg>';
        $file = UploadedFile::fake()->createWithContent('malicious.svg', $maliciousSvg);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/admin/cms/media/upload', [
                'file' => $file,
            ]);

        $response->assertStatus(201);

        $path = $response->json('data.media.path');
        $content = Storage::disk('public')->get($path);

        $this->assertStringNotContainsString('<script>', $content);
        $this->assertStringContainsString('<circle', $content);
    }

    public function test_file_manager_upload_sanitizes_svg()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('manage files');

        $maliciousSvg = '<?xml version="1.0"?><svg><script>alert("xss")</script><rect width="100" height="100"/></svg>';
        $file = UploadedFile::fake()->createWithContent('evil.svg', $maliciousSvg);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/admin/core/file-manager/upload', [
                'file' => $file,
                'disk' => 'public',
                'path' => 'uploads',
            ]);

        $response->assertStatus(201);

        // Find the file in the fake storage
        // FileManager stores as: uploads/evil.svg
        $exists = Storage::disk('public')->exists('uploads/evil.svg');
        $this->assertTrue($exists, 'File should exist in storage');

        $content = Storage::disk('public')->get('uploads/evil.svg');
        $this->assertStringNotContainsString('<script>', $content);
        $this->assertStringContainsString('<rect', $content);
    }
}
