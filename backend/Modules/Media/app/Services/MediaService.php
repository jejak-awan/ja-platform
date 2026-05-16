<?php

namespace Modules\Media\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Media\Contracts\MediaServiceInterface;
use Modules\Media\Models\File;
use Modules\Media\Models\Folder;
use Modules\Media\Models\Usage;
use Modules\Media\Models\DeletedFile;
use Modules\System\Models\Setting;
use Modules\Library\Models\Tag;

class MediaService implements MediaServiceInterface
{
    /**
     * Upload and process a media file.
     */
    public function upload(
        UploadedFile $file,
        ?string $folderId = null,
        bool $optimize = true,
        ?int $authorId = null,
        bool $isShared = false,
        array $metadata = [],
        ?string $subPath = null,
        string $module = 'system'
    ): File {
        // SVG Sanitization
        if ($file->getMimeType() === 'image/svg+xml' || strtolower($file->getClientOriginalExtension()) === 'svg') {
            $this->sanitizeSvg($file->getRealPath());
        }

        $uploadPath = $subPath ?: 'media';
        $pathRaw = $file->store($uploadPath, 'public');
        $path = is_string($pathRaw) ? $pathRaw : '';
        $fullPath = Storage::disk('public')->path($path);

        $mimeType = (string) $file->getMimeType();
        $fileName = $file->getClientOriginalName();

        // Image Optimization
        if ($optimize && str_starts_with($mimeType, 'image/') && $mimeType !== 'image/svg+xml') {
            $maxWidth = (int) Setting::get('media_max_width', 1920);
            $quality = (int) Setting::get('media_optimization_quality', 85);
            $autoConvert = (bool) Setting::get('media_auto_convert_webp', true);

            $this->optimizeImage($fullPath, $maxWidth, $quality);

            if ($autoConvert && $mimeType !== 'image/webp') {
                $webpPath = $this->convertToWebP($fullPath, $quality);
                if ($webpPath) {
                    $fullPath = $webpPath;
                    $path = $uploadPath . '/' . basename($fullPath);
                    $mimeType = 'image/webp';
                    $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '.webp';
                }
            }
        }

        $mediaFile = File::create([
            'module' => $module,
            'name' => $metadata['name'] ?? pathinfo($fileName, PATHINFO_FILENAME),
            'file_name' => $fileName,
            'mime_type' => $mimeType,
            'disk' => 'public',
            'path' => $path,
            'size' => filesize($fullPath),
            'folder_id' => $folderId,
            'author_id' => $authorId ?: Auth::id(),
            'is_shared' => $isShared,
            'caption' => $metadata['caption'] ?? null,
            'alt' => $metadata['alt'] ?? $fileName,
        ]);

        // Sync Tags
        if (!empty($metadata['tags']) && is_array($metadata['tags'])) {
            $this->syncTags($mediaFile, $metadata['tags']);
        }

        // Generate Thumbnail
        if (str_starts_with($mimeType, 'image/')) {
            $this->generateThumbnail($mediaFile);
        }

        return $mediaFile;
    }

    /**
     * Optimize an image file.
     */
    public function optimizeImage(string $fullPath, int $maxWidth = 1920, int $quality = 85): bool
    {
        if (!class_exists(\Intervention\Image\ImageManager::class)) {
            return false;
        }

        try {
            $driver = $this->getImageDriver();
            if (!$driver instanceof \Intervention\Image\Interfaces\DriverInterface) {
                return false;
            }

            $manager = new \Intervention\Image\ImageManager($driver);
            $image = $manager->read($fullPath);

            if ($image->width() > $maxWidth) {
                $image->scale(width: $maxWidth);
            }

            $image->save($fullPath, quality: $quality);
            return true;
        } catch (\Exception $e) {
            Log::channel('media')->warning('Image optimization failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Convert an image to WebP format.
     */
    public function convertToWebP(string $fullPath, int $quality = 85): ?string
    {
        if (!class_exists(\Intervention\Image\ImageManager::class)) {
            return null;
        }

        try {
            $driver = $this->getImageDriver();
            if (!$driver instanceof \Intervention\Image\Interfaces\DriverInterface) {
                return null;
            }

            $manager = new \Intervention\Image\ImageManager($driver);
            $image = $manager->read($fullPath);

            $pathInfo = pathinfo($fullPath);
            $newPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';

            $image->toWebp($quality)->save($newPath);

            if ($fullPath !== $newPath && file_exists($newPath)) {
                unlink($fullPath);
                return $newPath;
            }

            return $newPath;
        } catch (\Exception $e) {
            Log::channel('media')->warning('WebP conversion failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate thumbnail for media.
     */
    public function generateThumbnail(File $file, ?int $width = null, ?int $height = null): ?string
    {
        $width ??= (int) Setting::get('media_thumbnail_width', 300);
        $height ??= (int) Setting::get('media_thumbnail_height', 300);
        $fullPath = Storage::disk($file->disk)->path($file->path);

        $pathInfo = pathinfo($file->path);
        $fileName = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? '';
        $dirname = ($pathInfo['dirname'] === '.' || $pathInfo['dirname'] === '/') ? '' : $pathInfo['dirname'] . '/';

        $thumbnailDir = Storage::disk($file->disk)->path($dirname . 'thumbnails');
        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        $isSvg = $file->mime_type === 'image/svg+xml' || strtolower($extension) === 'svg';
        $thumbnailExtension = $isSvg ? 'png' : $extension;
        $thumbnailPath = $dirname . 'thumbnails/' . $fileName . '_thumb.' . $thumbnailExtension;
        $thumbnailFullPath = Storage::disk($file->disk)->path($thumbnailPath);

        // Handle SVG with Imagick if available
        if ($isSvg && extension_loaded('imagick') && class_exists('Imagick')) {
            try {
                $imagick = new \Imagick;
                $imagick->setBackgroundColor(new \ImagickPixel('transparent'));
                $imagick->readImage($fullPath);
                $imagick->setImageFormat('png');
                $imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1, true);
                $imagick->writeImage($thumbnailFullPath);
                return $thumbnailPath;
            } catch (\Exception $e) {
                Log::channel('media')->warning('SVG thumbnail generation failed: ' . $e->getMessage());
            }
        }

        // Fallback to Intervention
        $driver = $this->getImageDriver();
        if (!$driver instanceof \Intervention\Image\Interfaces\DriverInterface) {
            return null;
        }

        try {
            $manager = new \Intervention\Image\ImageManager($driver);
            $image = $manager->read($fullPath);
            $image->cover($width, $height);

            if ($isSvg) {
                $image->toPng()->save($thumbnailFullPath);
            } else {
                $image->save($thumbnailFullPath, quality: 85);
            }

            return $thumbnailPath;
        } catch (\Exception $e) {
            Log::channel('media')->warning('Thumbnail generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Resize an image.
     */
    public function resize(File $file, int $width, ?int $height = null, int $quality = 85): bool
    {
        $driver = $this->getImageDriver();
        if (!$driver instanceof \Intervention\Image\Interfaces\DriverInterface) {
            return false;
        }

        try {
            $fullPath = Storage::disk($file->disk)->path($file->path);
            $manager = new \Intervention\Image\ImageManager($driver);
            $image = $manager->read($fullPath);

            if ($height) {
                $image->resize($width, $height);
            } else {
                $image->scale(width: $width);
            }

            $image->save($fullPath, quality: $quality);
            $file->update(['size' => filesize($fullPath)]);

            return true;
        } catch (\Exception $e) {
            Log::channel('media')->error('Image resize failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete media.
     */
    public function delete(File $file, bool $permanent = false): void
    {
        if ($permanent) {
            $this->forceDelete($file);
            return;
        }

        $originalPath = $file->path;
        $disk = $file->disk ?: 'public';
        $fileName = basename($originalPath);
        $trashPath = '.trash/' . uniqid() . '_' . $fileName;

        try {
            Storage::disk($disk)->makeDirectory('.trash');
            if (Storage::disk($disk)->exists($originalPath)) {
                Storage::disk($disk)->move($originalPath, $trashPath);
            }

            DeletedFile::create([
                'original_path' => '/' . ltrim($originalPath, '/'),
                'trash_path' => $trashPath,
                'disk' => $disk,
                'name' => $file->name ?: $fileName,
                'type' => 'file',
                'size' => $file->size,
                'extension' => pathinfo($fileName, PATHINFO_EXTENSION),
                'mime_type' => $file->mime_type ?: 'application/octet-stream',
                'deleted_by' => Auth::id(),
                'deleted_at' => now(),
            ]);

            $file->path = $trashPath;
            $file->save();
            $file->delete();
        } catch (\Exception $e) {
            Log::channel('media')->error('Soft delete move to trash failed: ' . $e->getMessage());
            $file->delete();
        }
    }

    /**
     * Restore a soft-deleted media item.
     */
    public function restore(string $fileId): ?File
    {
        $file = File::onlyTrashed()->find($fileId);
        if (!$file) {
            return null;
        }

        $deletedFile = DeletedFile::where('trash_path', $file->path)->first();
        if ($deletedFile) {
            $originalPath = ltrim((string) $deletedFile->original_path, '/');
            try {
                if (Storage::disk($file->disk)->exists($file->path)) {
                    Storage::disk($file->disk)->move($file->path, $originalPath);
                }
                $file->path = $originalPath;
                $file->save();
                $file->restore();
                $deletedFile->delete();
                return $file;
            } catch (\Exception $e) {
                Log::channel('media')->error('Restore failed: ' . $e->getMessage());
            }
        }
        
        $file->restore();
        return $file;
    }

    /**
     * Perform bulk action on media.
     */
    public function bulkAction(string $action, array $mediaIds, ?string $folderId = null, ?string $altText = null, array $folderIds = []): array
    {
        $affectedMedia = 0;
        $affectedFolders = 0;

        foreach ($mediaIds as $id) {
            $file = File::withTrashed()->find($id);
            if (!$file) {
                continue;
            }

            switch ($action) {
                case 'delete': $this->delete($file, false); break;
                case 'delete_permanent': $this->delete($file, true); break;
                case 'restore': $this->restore($file->id); break;
                case 'move': $file->update(['folder_id' => $folderId]); break;
            }
            $affectedMedia++;
        }

        return ['media_count' => $affectedMedia, 'folder_count' => $affectedFolders];
    }

    /**
     * Create ZIP download from multiple media.
     */
    public function createZip(array $mediaIds): ?string
    {
        $files = File::whereIn('id', $mediaIds)->get();
        if ($files->isEmpty()) {
            return null;
        }

        $zipFileName = 'media-' . now()->format('Y-m-d-His') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        foreach ($files as $file) {
            $filePath = Storage::disk($file->disk)->path($file->path);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $file->file_name ?: $file->name);
            }
        }
        $zip->close();
        return $zipPath;
    }

    /**
     * Get usage information for media.
     */
    public function getUsageInfo(File $file): array
    {
        return Usage::where('file_id', $file->id)->get()->toArray();
    }

    /**
     * Internal Helpers
     */
    protected function getImageDriver(): ?\Intervention\Image\Interfaces\DriverInterface
    {
        if (extension_loaded('gd')) {
            return new \Intervention\Image\Drivers\Gd\Driver;
        }
        if (extension_loaded('imagick')) {
            return new \Intervention\Image\Drivers\Imagick\Driver;
        }
        return null;
    }

    protected function sanitizeSvg(string $filePath): void
    {
        if (!class_exists(\enshrined\svgSanitize\Sanitizer::class)) {
            return;
        }
        try {
            $sanitizer = new \enshrined\svgSanitize\Sanitizer;
            $content = file_get_contents($filePath);
            if ($content) {
                $cleanContent = $sanitizer->sanitize($content);
                file_put_contents($filePath, $cleanContent);
            }
        } catch (\Exception $e) {
            Log::channel('media')->error('SVG sanitization failed: ' . $e->getMessage());
        }
    }

    public function syncTags(File $file, array $tags): void
    {
        $tagIds = [];
        foreach ($tags as $tagName) {
            if (in_array(trim((string) $tagName), ['', '0'], true)) {
                continue;
            }
            $tag = Tag::firstOrCreate(['name' => trim((string) $tagName)], ['slug' => Str::slug($tagName)]);
            $tagIds[] = $tag->id;
        }
        
        $file->tags()->sync($tagIds);
    }

    protected function forceDelete(File $file): void
    {
        Storage::disk($file->disk)->delete($file->path);
        $file->forceDelete();
    }
}
