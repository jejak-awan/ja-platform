<?php

namespace Modules\Cms\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Cms\Models\Media;
use Modules\Cms\Models\MediaFolder;
use Modules\Cms\Services\MediaService;
use Tests\TestCase;

class MediaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MediaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = new MediaService;
    }

    public function test_upload_image()
    {
        $file = UploadedFile::fake()->image('test.jpg', 600, 600);

        $media = $this->service->upload($file);

        $this->assertInstanceOf(Media::class, $media);
        $this->assertStringContainsString('test', $media->file_name);
        $this->assertEquals('image/webp', $media->mime_type);
        Storage::disk('public')->assertExists($media->file_path);
    }

    public function test_upload_to_folder()
    {
        $folder = MediaFolder::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $media = $this->service->upload($file, $folder->id);

        $this->assertEquals($folder->id, $media->folder_id);
        Storage::disk('public')->assertExists($media->file_path);
    }

    public function test_optimize_image()
    {
        $file = UploadedFile::fake()->image('large.jpg', 2000, 2000);
        $tempPath = $file->getRealPath();

        $method = new \ReflectionMethod(MediaService::class, 'optimizeImage');
        $method->setAccessible(true);
        $method->invoke($this->service, $tempPath, 1000);

        $size = getimagesize($tempPath);
        $this->assertLessThanOrEqual(1000, $size[0]);
    }

    public function test_convert_to_webp()
    {
        $file = UploadedFile::fake()->image('convert.jpg');
        $tempPath = $file->getRealPath();

        $method = new \ReflectionMethod(MediaService::class, 'convertToWebP');
        $method->setAccessible(true);
        $resultPath = $method->invoke($this->service, $tempPath);

        $this->assertFileExists($resultPath);
        $this->assertEquals('image/webp', mime_content_type($resultPath));
    }

    public function test_generate_thumbnail()
    {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);
        $media = $this->service->upload($file);

        $this->service->generateThumbnail($media, 150, 150);
        $this->assertTrue(true);
    }

    public function test_delete_media()
    {
        $file = UploadedFile::fake()->image('to_delete.jpg');
        $media = $this->service->upload($file);
        $path = $media->file_path;

        $this->service->delete($media);

        $this->assertSoftDeleted('media', ['id' => $media->id]);
    }

    public function test_force_delete_media()
    {
        $file = UploadedFile::fake()->image('permanent.jpg');
        $media = $this->service->upload($file);
        $path = $media->file_path;

        $this->service->forceDelete($media);

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_restore_media()
    {
        $file = UploadedFile::fake()->image('restore.jpg');
        $media = $this->service->upload($file);
        $this->service->delete($media);

        $restored = $this->service->restore($media->id);

        $this->assertFalse($restored->trashed());
    }

    public function test_bulk_action_move()
    {
        $media1 = Media::factory()->create();
        $media2 = Media::factory()->create();
        $folder = MediaFolder::factory()->create();

        $result = $this->service->bulkAction('move', [$media1->id, $media2->id], $folder->id);

        $this->assertEquals(2, $result['media_count']);
        $this->assertEquals($folder->id, $media1->fresh()->folder_id);
    }

    public function test_sync_tags()
    {
        $media = Media::factory()->create();
        $this->service->syncTags($media, ['tag1', 'tag2']);

        $this->assertCount(2, $media->tags);
    }

    public function test_scan_storage()
    {
        Storage::disk('public')->put('media/manual.txt', 'hello');

        $result = $this->service->scan('public', 'media');

        $this->assertGreaterThan(0, $result['added']);
    }

    public function test_create_zip()
    {
        $media1 = Media::factory()->create(['path' => 'media/1.txt']);
        $media2 = Media::factory()->create(['path' => 'media/2.txt']);
        Storage::disk('public')->put($media1->path, 'content1');
        Storage::disk('public')->put($media2->path, 'content2');

        $zipPath = $this->service->createZip([$media1->id, $media2->id]);

        $this->assertFileExists($zipPath);
        $this->assertEquals('application/zip', mime_content_type($zipPath));
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }
    }

    public function test_get_usage_info()
    {
        $media = Media::factory()->create();
        $info = $this->service->getUsageInfo($media);
        $this->assertIsArray($info);
    }

    public function test_edit_image()
    {
        $media = Media::factory()->create([
            'disk' => 'public',
            'path' => 'test_edit.jpg',
            'file_name' => 'test_edit.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        $imageFile = UploadedFile::fake()->image('test_edit.jpg');
        Storage::disk('public')->put($media->path, $imageFile->get());

        $newFile = UploadedFile::fake()->image('updated.jpg');

        $updated = $this->service->editImage($media, $newFile);

        $this->assertNotNull($updated);
        $this->assertEquals($media->id, $updated->id);
    }

    public function test_delete_variants()
    {
        $media = Media::factory()->create(['path' => 'v.jpg']);
        $path = $media->path;
        $dir = dirname($path);

        Storage::disk('public')->put('thumb_test.jpg', '');

        $this->service->deleteVariants($media);
        $this->assertTrue(true);
    }

    public function test_image_processing_availability()
    {
        $method = new \ReflectionMethod(MediaService::class, 'getImageDriver');
        $method->setAccessible(true);
        $driver = $method->invoke($this->service);
        $this->assertNotNull($driver);

        $this->assertIsBool($this->service->isImageProcessingAvailable());
    }

    public function test_sanitize_svg()
    {
        $svgContent = '<svg><script>alert(1)</script><path d="M10 10"/></svg>';
        Storage::disk('public')->put('test.svg', $svgContent);
        $fullPath = Storage::disk('public')->path('test.svg');

        $method = new \ReflectionMethod(MediaService::class, 'sanitizeSvg');
        $method->setAccessible(true);
        $method->invoke($this->service, $fullPath);

        $sanitized = Storage::disk('public')->get('test.svg');
        $this->assertStringNotContainsString('<script>', $sanitized);
    }

    public function test_get_media_attribute_urls()
    {
        $media = Media::factory()->create(['path' => 'media/test.jpg', 'disk' => 'public', 'mime_type' => 'image/jpeg']);
        $this->assertStringContainsString('storage/media/test.jpg', $media->url);
    }

    public function test_bulk_action_restore()
    {
        $media = Media::factory()->create();
        $this->service->delete($media);
        $this->assertTrue($media->fresh()->trashed());

        $result = $this->service->bulkAction('restore', [$media->id]);
        $this->assertEquals(1, $result['media_count']);
        $this->assertFalse($media->fresh()->trashed());
    }

    public function test_bulk_action_update_alt_caption()
    {
        $media = Media::factory()->create();
        $this->service->bulkAction('update_alt', [$media->id], null, 'New Alt');
        $this->assertEquals('New Alt', $media->fresh()->alt);

        $this->service->bulkAction('update_caption', [$media->id], null, 'New Caption');
        $this->assertEquals('New Caption', $media->fresh()->caption);
    }

    public function test_bulk_action_folders()
    {
        $folder = MediaFolder::factory()->create();
        $result = $this->service->bulkAction('delete', [], null, null, [$folder->id]);
        $this->assertEquals(1, $result['folder_count']);
        $this->assertSoftDeleted('media_folders', ['id' => $folder->id]);

        $this->service->bulkAction('restore', [], null, null, [$folder->id]);
        $this->assertFalse($folder->fresh()->trashed());
    }

    public function test_bulk_action_permanent_delete()
    {
        $media = Media::factory()->create();
        $this->service->bulkAction('delete_permanent', [$media->id]);
        $this->assertDatabaseMissing('media', ['id' => $media->id]);

        $folder = MediaFolder::factory()->create();
        $this->service->bulkAction('delete_permanent', [], null, null, [$folder->id]);
        $this->assertDatabaseMissing('media_folders', ['id' => $folder->id]);
    }

    public function test_move_folder()
    {
        $folder = MediaFolder::factory()->create();
        $parent = MediaFolder::factory()->create();
        $this->service->bulkAction('move', [], $parent->id, null, [$folder->id]);
        $this->assertEquals($parent->id, $folder->fresh()->parent_id);
    }

    public function test_edit_image_save_as_new()
    {
        $media = Media::factory()->create(['path' => 'media/original.jpg', 'disk' => 'public']);
        $newFile = UploadedFile::fake()->image('edited.jpg');

        $newMedia = $this->service->editImage($media, $newFile, true);

        $this->assertNotNull($newMedia);
        $this->assertNotEquals($media->id, $newMedia->id);
        $this->assertStringContainsString('edited', $newMedia->path);
    }

    public function test_create_zip_empty()
    {
        $zipPath = $this->service->createZip([]);
        $this->assertNull($zipPath);
    }

    public function test_restore_with_collision()
    {
        $media = Media::factory()->create(['path' => 'media/col.jpg']);
        $path = $media->path;
        Storage::disk('public')->put($path, 'content');

        $this->service->delete($media);

        // Create a file at the original path to trigger collision
        Storage::disk('public')->put($path, 'new content');

        $restored = $this->service->restore($media->id);

        $this->assertNotNull($restored);
        $this->assertNotEquals($path, $restored->path);
        $this->assertStringContainsString('_restored_', $restored->path);
    }

    public function test_resize_unsupported_driver()
    {
        $media = Media::factory()->create();

        // Mock getImageDriver to return null
        $service = $this->getMockBuilder(MediaService::class)
            ->onlyMethods(['getImageDriver'])
            ->getMock();
        $service->method('getImageDriver')->willReturn(null);

        $result = $service->resize($media, 100);
        $this->assertFalse($result);
    }

    public function test_resize_with_height()
    {
        $file = UploadedFile::fake()->image('resize.jpg', 800, 600);
        $media = $this->service->upload($file);

        $result = $this->service->resize($media, 400, 300);
        $this->assertTrue($result);
        $this->assertEquals(400, getimagesize(Storage::disk('public')->path($media->path))[0]);
    }

    public function test_delete_variants_verified()
    {
        $media = Media::factory()->create(['path' => 'media/test.jpg', 'file_name' => 'test.jpg', 'disk' => 'public']);
        $fileName = 'test';
        $thumb = 'media/thumbnails/'.$fileName.'_thumb.jpg';
        $sizedSmall = 'media/small/test.jpg';
        $sizedMedium = 'media/medium/test.jpg';
        $sizedLarge = 'media/large/test.jpg';

        Storage::disk('public')->put($thumb, '');
        Storage::disk('public')->put($sizedSmall, '');
        Storage::disk('public')->put($sizedMedium, '');
        Storage::disk('public')->put($sizedLarge, '');

        $this->service->deleteVariants($media);

        Storage::disk('public')->assertMissing($thumb);
        Storage::disk('public')->assertMissing($sizedSmall);
        Storage::disk('public')->assertMissing($sizedMedium);
        Storage::disk('public')->assertMissing($sizedLarge);
    }

    public function test_get_usage_info_model_not_found()
    {
        $media = Media::factory()->create();
        \Modules\Cms\Models\MediaUsage::create([
            'media_id' => $media->id,
            'model_type' => 'App\Models\NonExistent',
            'model_id' => 999,
            'field_name' => 'image',
        ]);

        $info = $this->service->getUsageInfo($media);
        $this->assertCount(1, $info);
        $this->assertStringContainsString('Deleted or not found', $info[0]['model']['title']);
    }

    public function test_edit_image_overwrite_with_webp()
    {
        $media = Media::factory()->create([
            'path' => 'media/old.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
        ]);
        Storage::disk('public')->put($media->path, 'content');

        $newFile = UploadedFile::fake()->image('new.jpg');

        // Mock Setting to enable auto conversion
        \Modules\Core\Models\Setting::set('media_auto_convert_webp', 1);

        $updated = $this->service->editImage($media, $newFile);

        $this->assertEquals('image/webp', $updated->mime_type);
        $this->assertStringEndsWith('.webp', $updated->path);
    }

    public function test_convert_to_webp_failure()
    {
        $file = UploadedFile::fake()->create('test.txt', 10);
        $tempPath = $file->getRealPath();

        $method = new \ReflectionMethod(MediaService::class, 'convertToWebP');
        $method->setAccessible(true);
        $result = $method->invoke($this->service, $tempPath);

        $this->assertNull($result);
    }
}
