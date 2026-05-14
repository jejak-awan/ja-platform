<?php

namespace Modules\Core\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\Media;
use Modules\Core\Models\MediaFolder;
use Modules\Core\Models\Tag;
use Modules\Core\Helpers\UploadSettingsHelper;
use Modules\Core\Models\Setting;
use Modules\Core\Services\CacheService;

class MediaService
{
    protected CacheService $cacheService;

    public function __construct()
    {
        $this->cacheService = new CacheService;
    }

    /**
     * Upload and process a media file
     *
     * @param  array<string, mixed>  $metadata
     */
    public function upload(UploadedFile $file, ?int $folderId = null, bool $optimize = true, ?int $authorId = null, bool $isShared = false, array $metadata = [], ?string $subPath = null, string $module = 'core'): Media
    {
        // Check for SVG and sanitize BEFORE optimization or other processing
        if ($file->getMimeType() === 'image/svg+xml' || strtolower($file->getClientOriginalExtension()) === 'svg') {
            $this->sanitizeSvg($file->getRealPath());
        }

        $uploadPath = $subPath ?:  'media';
        $pathRaw = $file->store($uploadPath, 'public');
        $path = is_string($pathRaw) ? $pathRaw : '';
        $fullPath = Storage::disk('public')->path($path);

        // Optimize image if requested (and not SVG, since we just sanitized/saved it, but optimizeImage might support it?)
        // optimizeImage uses Intervention which might not handle SVGs well depending on driver.
        // Usually we don't optimize SVGs with ImageManager.
        $mimeTypeStr = (string) $file->getMimeType(); // Original mime type as string for checks
        $mimeType = $mimeTypeStr; // This variable will hold the final mime type for the media record
        if ($optimize && str_starts_with($mimeTypeStr, 'image/') && $mimeTypeStr !== 'image/svg+xml') {
            $maxWidth = Setting::get('media_max_width', 1920);
            $maxWidth = is_numeric($maxWidth) ? (int) $maxWidth : 1920;

            $quality = Setting::get('media_optimization_quality', 85);
            $quality = is_numeric($quality) ? (int) $quality : 85;

            $autoConvert = (bool) Setting::get('media_auto_convert_webp', true);

            $this->optimizeImage($fullPath, $maxWidth, $quality);

            if ($autoConvert && $file->getMimeType() !== 'image/webp') {
                $webpPath = $this->convertToWebP($fullPath, $quality);
                if ($webpPath) {
                    $fullPath = $webpPath;
                    $path = $uploadPath . '/' . basename($fullPath);
                    $mimeType = 'image/webp';
                    $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME).'.webp';
                }
            }
        }

        $metadataCaption = $metadata['caption'] ?? null;
        $metadataAlt = $metadata['alt'] ?? null;

        $media = Media::create([
            'module' => $module,
            'name' => $fileName ?? $file->getClientOriginalName(),
            'file_name' => $fileName ?? $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'disk' => 'public',
            'path' => $path,
            'size' => filesize($fullPath),
            'folder_id' => $folderId,
            'author_id' => $authorId,
            'is_shared' => $isShared,
            'caption' => is_scalar($metadataCaption) ? (string) $metadataCaption : null,
            'alt' => is_scalar($metadataAlt) ? (string) $metadataAlt : $file->getClientOriginalName(),
        ]);

        // Sync tags if provided
        if (! empty($metadata['tags']) && is_array($metadata['tags'])) {
            /** @var array<int, string> $tags */
            $tags = $metadata['tags'];
            $this->syncTags($media, $tags);
        }

        // Auto-generate thumbnail for images
        $mediaMimeType = (string) $media->mime_type;
        if (str_starts_with($mediaMimeType, 'image/')) {
            try {
                $this->generateThumbnail($media);
            } catch (\Exception $e) {
                Log::channel('media')->warning('Auto-thumbnail generation failed: '.$e->getMessage());
            }
        }

        $this->cacheService->clearMediaCaches();

        return $media;
    }

    /**
     * Optimize an image file
     */
    public function optimizeImage(string $fullPath, int $maxWidth = 1920, int $quality = 85): bool
    {
        if (! class_exists(\Intervention\Image\ImageManager::class)) {
            return false;
        }

        try {
            $driver = $this->getImageDriver();
            if (! $driver) {
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
            Log::channel('media')->warning('Image optimization failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Convert an image to WebP format
     */
    public function convertToWebP(string $fullPath, int $quality = 85): ?string
    {
        if (! class_exists(\Intervention\Image\ImageManager::class)) {
            return null;
        }

        try {
            $driver = $this->getImageDriver();
            if (! $driver) {
                return null;
            }

            $manager = new \Intervention\Image\ImageManager($driver);
            $image = $manager->read($fullPath);

            $pathInfo = pathinfo($fullPath);
            $filename = $pathInfo['filename'];
            $dirname = $pathInfo['dirname'] ?? '.';
            $newPath = $dirname.'/'.$filename.'.webp';

            // Convert and save
            $image->toWebp($quality)->save($newPath);

            // If new path is different, delete original
            if ($fullPath !== $newPath && file_exists($newPath)) {
                unlink($fullPath);

                return $newPath;
            }

            return $newPath;
        } catch (\Exception $e) {
            Log::channel('media')->warning('WebP conversion failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Generate thumbnail for media
     */
    public function generateThumbnail(Media $media, ?int $width = null, ?int $height = null): ?string
    {
        $width = $width ?? UploadSettingsHelper::getThumbnailWidth();
        $height = $height ?? UploadSettingsHelper::getThumbnailHeight();
        $fullPath = Storage::disk($media->disk)->path($media->path);

        $pathInfo = pathinfo((string) $media->path);
        $fileName = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? '';
        $dirname = ($pathInfo['dirname'] === '.' || $pathInfo['dirname'] === '/') ? '' : $pathInfo['dirname'].'/';

        // Create thumbnails directory
        $thumbnailDir = Storage::disk($media->disk)->path($dirname.'thumbnails');
        if (! is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        // SVG files get converted to PNG for thumbnail
        $isSvg = $media->mime_type === 'image/svg+xml' || strtolower($extension) === 'svg';
        $thumbnailExtension = $isSvg ? 'png' : $extension;
        $thumbnailPath = $dirname.'thumbnails/'.$fileName.'_thumb.'.$thumbnailExtension;
        $thumbnailFullPath = Storage::disk($media->disk)->path($thumbnailPath);

        // Handle SVG with Imagick
        if ($isSvg && extension_loaded('imagick') && class_exists('Imagick')) {
            try {
                $imagick = new \Imagick;
                $imagick->setBackgroundColor(new \ImagickPixel('transparent'));
                $imagick->readImage($fullPath);
                $imagick->setImageFormat('png');
                $imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1, true);
                $imagick->writeImage($thumbnailFullPath);
                $imagick->clear();
                $imagick->destroy();

                return $thumbnailPath;
            } catch (\Exception $e) {
                Log::channel('media')->warning('SVG thumbnail generation failed: '.$e->getMessage());
            }
        }

        // Use Intervention Image for raster images
        $driver = $this->getImageDriver();
        if (! $driver) {
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
            Log::channel('media')->warning('Thumbnail generation failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Resize an image
     */
    public function resize(Media $media, int $width, ?int $height = null, int $quality = 85): bool
    {
        $driver = $this->getImageDriver();
        if (! $driver) {
            return false;
        }

        try {
            $fullPath = Storage::disk($media->disk)->path($media->path);
            $manager = new \Intervention\Image\ImageManager($driver);
            $image = $manager->read($fullPath);

            if ($height) {
                $image->resize($width, $height);
            } else {
                $image->scale(width: $width);
            }

            $image->save($fullPath, quality: $quality);

            // Update file size in database
            $media->update(['size' => filesize($fullPath)]);

            return true;
        } catch (\Exception $e) {
            Log::channel('media')->error('Image resize failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Delete media (soft delete by default, keeps physical files in .trash)
     */
    public function delete(Media $media, bool $permanent = false): void
    {
        if ($permanent) {
            $this->forceDelete($media);

            return;
        }

        // Soft delete - move to physical trash and record in deleted_files
        $originalPath = $media->path;
        $disk = $media->disk ?? 'public';
        $fileName = basename($originalPath);
        $trashPath = '.trash/'.uniqid().'_'.$fileName;

        try {
            // Ensure trash directory exists
            Storage::disk($disk)->makeDirectory('.trash');

            // Move file in storage
            if (Storage::disk($disk)->exists($originalPath)) {
                Storage::disk($disk)->move($originalPath, $trashPath);
            }

            // Also move thumbnails and variants to trash
            $this->moveVariantsToTrash($media, $trashPath);

            // Create record in deleted_files table for unified trash visibility
            \Modules\Core\Models\DeletedFile::create([
                'original_path' => '/'.ltrim($originalPath, '/'),
                'trash_path' => $trashPath,
                'disk' => $disk,
                'name' => $media->name ?: $fileName,
                'type' => 'file',
                'size' => $media->size,
                'extension' => pathinfo($fileName, PATHINFO_EXTENSION),
                'mime_type' => $media->mime_type ?: 'application/octet-stream',
                'deleted_by' => \Illuminate\Support\Facades\Auth::id(),
                'deleted_at' => now(),
            ]);

            // Update media path to point to trash and soft delete it
            $media->path = $trashPath;
            $media->save();
            $media->delete();

            $this->cacheService->clearMediaCaches();
        } catch (\Exception $e) {
            Log::channel('media')->error('Soft delete move to trash failed: '.$e->getMessage());
            // Fallback to simple soft delete if move fails
            $media->delete();
        }
    }

    /**
     * Permanently delete media and all its physical files
     */
    public function forceDelete(Media $media): void
    {
        $this->deleteVariants($media);
        Storage::disk($media->disk)->delete($media->path);

        // Delete matching deleted_files record if exists
        \Modules\Core\Models\DeletedFile::where('trash_path', $media->path)
            ->where('disk', $media->disk ?? 'public')
            ->delete();

        // Delete usages
        $media->usages()->delete();

        // Force delete the model
        $media->forceDelete();
        $this->cacheService->clearMediaCaches();
    }

    /**
     * Restore a soft-deleted media item
     */
    public function restore(int $mediaId): ?Media
    {
        $media = Media::onlyTrashed()->find($mediaId);
        if ($media) {
            $trashPath = $media->path;
            $disk = $media->disk ?? 'public';

            // Find matching record in deleted_files to get original path
            $deletedFile = \Modules\Core\Models\DeletedFile::where('trash_path', $trashPath)
                ->where('disk', $disk)
                ->first();

            if ($deletedFile) {
                $originalPath = ltrim($deletedFile->original_path, '/');

                try {
                    // Check if original path is taken
                    $finalPath = $originalPath;
                    if (Storage::disk($disk)->exists($originalPath)) {
                        $ext = pathinfo($originalPath, PATHINFO_EXTENSION);
                        $name = pathinfo($originalPath, PATHINFO_FILENAME);
                        $dir = pathinfo($originalPath, PATHINFO_DIRNAME);
                        $dir = ($dir === '.' || $dir === '/') ? '' : $dir.'/';
                        $finalPath = $dir.$name.'_restored_'.time().($ext ? '.'.$ext : '');
                    }

                    // Move file back from trash
                    if (Storage::disk($disk)->exists($trashPath)) {
                        Storage::disk($disk)->move($trashPath, $finalPath);
                    }

                    // Move thumbnails back from trash
                    $this->moveVariantsFromTrash($media, $trashPath, $finalPath);

                    // Update media model with restored path
                    $media->path = $finalPath;
                    $media->save();

                    // Restore the model
                    $media->restore();

                    // Clean up deleted_files record
                    $deletedFile->delete();
                } catch (\Exception $e) {
                    Log::channel('media')->error('Restore move from trash failed: '.$e->getMessage());
                    // Restore model anyway so it's visible (even if path is still trashed)
                    $media->restore();
                }
            } else {
                // No deleted_files record found, just restore the model
                $media->restore();
            }

            $this->cacheService->clearMediaCaches();

            return $media;
        }

        return null;
    }

    public function deleteVariants(Media $media): void
    {
        $fileName = pathinfo($media->path, PATHINFO_FILENAME);
        $extension = pathinfo($media->path, PATHINFO_EXTENSION);
        $pathInfo = pathinfo($media->path);
        $dirname = ($pathInfo['dirname'] === '.' || $pathInfo['dirname'] === '/') ? '' : $pathInfo['dirname'].'/';

        // Delete thumbnail
        $thumbnailPath = $dirname.'thumbnails/'.$fileName.'_thumb.'.$extension;
        if (Storage::disk($media->disk)->exists($thumbnailPath)) {
            Storage::disk($media->disk)->delete($thumbnailPath);
        }

        // Delete PNG thumbnail for SVG
        $pngThumbnailPath = $dirname.'thumbnails/'.$fileName.'_thumb.png';
        if (Storage::disk($media->disk)->exists($pngThumbnailPath)) {
            Storage::disk($media->disk)->delete($pngThumbnailPath);
        }

        // Delete sized variants
        foreach (['small', 'medium', 'large'] as $size) {
            $sizePath = $dirname.$size.'/'.$fileName.'.'.$extension;
            if (Storage::disk($media->disk)->exists($sizePath)) {
                Storage::disk($media->disk)->delete($sizePath);
            }
        }
    }

    public function moveVariantsToTrash(Media $media, string $trashPath): void
    {
        $oldFileName = pathinfo($media->path, PATHINFO_FILENAME);
        $newFileName = pathinfo($trashPath, PATHINFO_FILENAME);
        $extension = pathinfo($media->path, PATHINFO_EXTENSION);
        $disk = $media->disk ?? 'public';

        $pathInfo = pathinfo($media->path);
        $dirname = ($pathInfo['dirname'] === '.' || $pathInfo['dirname'] === '/') ? '' : $pathInfo['dirname'].'/';
        $trashPathInfo = pathinfo($trashPath);
        $trashDirname = ($trashPathInfo['dirname'] === '.' || $trashPathInfo['dirname'] === '/') ? '' : $trashPathInfo['dirname'].'/';

        // Move thumbnail
        $oldThumb = $dirname.'thumbnails/'.$oldFileName.'_thumb.'.$extension;
        $newThumb = $trashDirname.'thumbnails/'.$newFileName.'_thumb.'.$extension;
        if (Storage::disk($disk)->exists($oldThumb)) {
            if (! Storage::disk($disk)->exists($trashDirname.'thumbnails')) {
                Storage::disk($disk)->makeDirectory($trashDirname.'thumbnails');
            }
            Storage::disk($disk)->move($oldThumb, $newThumb);
        }

        // Handle SVG PNG thumb
        $oldPngThumb = $dirname.'thumbnails/'.$oldFileName.'_thumb.png';
        $newPngThumb = $trashDirname.'thumbnails/'.$newFileName.'_thumb.png';
        if (Storage::disk($disk)->exists($oldPngThumb)) {
            if (! Storage::disk($disk)->exists($trashDirname.'thumbnails')) {
                Storage::disk($disk)->makeDirectory($trashDirname.'thumbnails');
            }
            Storage::disk($disk)->move($oldPngThumb, $newPngThumb);
        }
    }

    /**
     * Move variants back from trash
     */
    public function moveVariantsFromTrash(Media $media, string $trashPath, string $newPath): void
    {
        $trashPathInfo = pathinfo($trashPath);
        $trashDirname = ($trashPathInfo['dirname'] === '.' || $trashPathInfo['dirname'] === '/') ? '' : $trashPathInfo['dirname'].'/';
        $newPathInfo = pathinfo($newPath);
        $newDirname = ($newPathInfo['dirname'] === '.' || $newPathInfo['dirname'] === '/') ? '' : $newPathInfo['dirname'].'/';

        $trashFileName = $trashPathInfo['filename'];
        $restoredFileName = $newPathInfo['filename'];
        $extension = $newPathInfo['extension'] ?? '';
        $disk = $media->disk ?? 'public';

        // Move thumbnail back
        $oldThumb = $trashDirname.'thumbnails/'.$trashFileName.'_thumb.'.$extension;
        $newThumb = $newDirname.'thumbnails/'.$restoredFileName.'_thumb.'.$extension;
        if (Storage::disk($disk)->exists($oldThumb)) {
            if (! Storage::disk($disk)->exists($newDirname.'thumbnails')) {
                Storage::disk($disk)->makeDirectory($newDirname.'thumbnails');
            }
            Storage::disk($disk)->move($oldThumb, $newThumb);
        }

        // Handle SVG PNG thumb
        $oldPngThumb = $trashDirname.'thumbnails/'.$trashFileName.'_thumb.png';
        $newPngThumb = $newDirname.'thumbnails/'.$restoredFileName.'_thumb.png';
        if (Storage::disk($disk)->exists($oldPngThumb)) {
            if (! Storage::disk($disk)->exists($newDirname.'thumbnails')) {
                Storage::disk($disk)->makeDirectory($newDirname.'thumbnails');
            }
            Storage::disk($disk)->move($oldPngThumb, $newPngThumb);
        }
    }

    /**
     * Perform bulk action on media
     *
     * @param  array<int, int>  $mediaIds
     * @param  array<int, int>  $folderIds
     * @return array{media_count: int, folder_count: int}
     */
    public function bulkAction(string $action, array $mediaIds, ?int $folderId = null, ?string $altText = null, array $folderIds = []): array
    {
        $affectedMedia = 0;
        $affectedFolders = 0;

        // Process Media
        if (! empty($mediaIds)) {
            $query = Media::withTrashed();
            if ($action === 'restore') {
                $query->onlyTrashed();
            }

            $media = $query->whereIn('id', $mediaIds)->get();

            foreach ($media as $item) {
                switch ($action) {
                    case 'delete':
                        $this->delete($item, false);
                        break;
                    case 'delete_permanent':
                        $this->delete($item, true);
                        break;
                    case 'restore':
                        $this->restore($item->id);
                        break;
                    case 'move_folder':
                    case 'move':
                        $item->update(['folder_id' => $folderId]);
                        break;
                    case 'update_alt':
                        $item->update(['alt' => $altText ?? '']);
                        break;
                    case 'update_caption':
                        $item->update(['caption' => $altText ?? '']);
                        break;
                }
                $affectedMedia++;
            }
        }

        // Process Folders
        if (! empty($folderIds)) {
            $folderQuery = \Modules\Core\Models\MediaFolder::withTrashed();
            if ($action === 'restore') {
                $folderQuery->onlyTrashed();
            }

            $folders = $folderQuery->whereIn('id', $folderIds)->get();

            foreach ($folders as $folder) {
                switch ($action) {
                    case 'delete':
                        $folder->delete();
                        break;
                    case 'delete_permanent':
                        $folder->forceDelete();
                        break;
                    case 'restore':
                        $folder->restore();
                        break;
                    case 'move':
                        $folder->update(['parent_id' => $folderId]);
                        break;
                }
                $affectedFolders++;
            }
        }

        if ($affectedMedia > 0 || $affectedFolders > 0) {
            $this->cacheService->clearMediaCaches();
        }

        return [
            'media_count' => $affectedMedia,
            'folder_count' => $affectedFolders,
        ];
    }

    /**
     * Create ZIP download from multiple media
     *
     * @param  array<int, int>  $mediaIds
     */
    public function createZip(array $mediaIds): ?string
    {
        $media = Media::withTrashed()->whereIn('id', $mediaIds)->get();

        if ($media->isEmpty()) {
            return null;
        }

        $zipFileName = 'media-'.now()->format('Y-m-d-His').'.zip';
        $zipPath = storage_path('app/temp/'.$zipFileName);

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        foreach ($media as $item) {
            $filePath = Storage::disk($item->disk)->path($item->path);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $item->file_name ?: $item->name);
            }
        }

        $zip->close();

        return $zipPath;
    }

    /**
     * Get usage information for media
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUsageInfo(Media $media): array
    {
        $usages = $media->usages()->get();

        return $usages->map(function ($usage) {
            /** @var \Modules\Core\Models\MediaUsage $usage */
            /** @var mixed $modelTypeRaw */
            $modelTypeRaw = $usage->getAttribute('model_type');
            $modelClass = is_string($modelTypeRaw) ? $modelTypeRaw : '';

            /** @var mixed $modelIdRaw */
            $modelIdRaw = $usage->getAttribute('model_id');
            $modelId = is_numeric($modelIdRaw) ? (int) $modelIdRaw : 0;

            $model = null;
            try {
                if (class_exists($modelClass)) {
                    /** @var \Illuminate\Database\Eloquent\Model|null $model */
                    $model = $modelClass::find($modelId);
                }
            } catch (\Exception $e) {
                Log::channel('media')->warning('Failed to load model for usage: '.$e->getMessage());
            }

            return [
                'id' => $usage->id,
                'model_type' => $modelClass,
                'model_id' => $modelId,
                'field_name' => is_scalar($usage->getAttribute('field_name')) ? (string) $usage->getAttribute('field_name') : '',
                'model' => $model ? [
                    'id' => $model->getKey(),
                    'title' => is_scalar($titleAttr = ($model->getAttribute('title') ?: ($model->getAttribute('name') ?: 'N/A'))) ? (string) $titleAttr : 'N/A',
                    'slug' => is_scalar($model->getAttribute('slug')) ? (string) $model->getAttribute('slug') : null,
                    'type' => class_basename($modelClass),
                ] : [
                    'id' => $modelId,
                    'title' => 'Deleted or not found',
                    'type' => class_basename($modelClass),
                ],
                'created_at' => $usage->created_at,
            ];
        })->all();
    }

    /**
     * Edit/replace image
     *
     * @param  array{name?: string, alt?: string, description?: string, caption?: string, tags?: array<int, string>}  $metadata
     */
    public function editImage(Media $media, UploadedFile $imageFile, bool $saveAsNew = false, array $metadata = []): ?Media
    {
        $driver = $this->getImageDriver();
        if (! $driver) {
            return null;
        }

        try {
            $manager = new \Intervention\Image\ImageManager($driver);
            $image = $manager->read($imageFile->getRealPath());

            $autoConvert = (bool) Setting::get('media_auto_convert_webp', true);
            $qualityRaw = Setting::get('media_optimization_quality', 85);
            $quality = is_numeric($qualityRaw) ? (int) $qualityRaw : 85;

            if ($saveAsNew) {
                $pathInfo = pathinfo((string) $media->path);
                $dirname = $pathInfo['dirname'] ?? 'media';
                $filename = $pathInfo['filename'];
                $extension = (string) ($pathInfo['extension'] ?? 'jpg');

                $finalExtension = $autoConvert ? 'webp' : $extension;
                $newFileName = $filename.'_edited_'.time().'.'.$finalExtension;
                $newPath = $dirname.'/'.$newFileName;

                $fullPath = Storage::disk($media->disk)->path($newPath);
                $directory = dirname($fullPath);
                if (! is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }

                if ($autoConvert) {
                    $image->toWebp($quality)->save($fullPath);
                } else {
                    $image->save($fullPath);
                }

                $newMedia = Media::create([
                    'name' => $metadata['name'] ?? ($pathInfo['filename'].'_edited'.($autoConvert ? '.webp' : '')),
                    'file_name' => $newFileName,
                    'path' => $newPath,
                    'mime_type' => $autoConvert ? 'image/webp' : $media->mime_type,
                    'size' => filesize($fullPath),
                    'disk' => $media->disk,
                    'folder_id' => $media->folder_id,
                    'alt' => $metadata['alt'] ?? $media->alt,
                    'description' => $metadata['description'] ?? $media->description,
                    'caption' => $metadata['caption'] ?? $media->caption,
                ]);

                if (! empty($metadata['tags'])) {
                    $this->syncTags($newMedia, $metadata['tags']);
                }

                try {
                    $this->generateThumbnail($newMedia);
                } catch (\Exception $e) {
                    Log::channel('media')->warning('Thumbnail generation failed for edited image: '.$e->getMessage());
                }

                return $newMedia;
            }

            // Overwrite existing
            $fullPath = Storage::disk($media->disk)->path($media->path);

            if ($autoConvert && $media->mime_type !== 'image/webp') {
                // Convert to webp even on overwrite
                $pathInfo = pathinfo((string) $media->path);
                $filename = $pathInfo['filename'];
                $dirname = (string) ($pathInfo['dirname'] ?? 'media');
                $newFileName = $filename.'.webp';
                $newPath = $dirname.'/'.$newFileName;
                $newFullPath = Storage::disk($media->disk)->path($newPath);

                $image->toWebp($quality)->save($newFullPath);

                // Delete old if different
                if ($fullPath !== $newFullPath) {
                    unlink($fullPath);
                }

                $media->update([
                    'file_name' => $newFileName,
                    'path' => $newPath,
                    'mime_type' => 'image/webp',
                    'size' => filesize($newFullPath),
                ]);
            } else {
                $image->save($fullPath, quality: $quality);
                $media->update(['size' => filesize($fullPath)]);
            }

            try {
                $this->generateThumbnail($media);
            } catch (\Exception $e) {
                Log::channel('media')->warning('Thumbnail regeneration failed: '.$e->getMessage());
            }

            return $media->fresh();
        } catch (\Exception $e) {
            Log::channel('media')->error('Image editing failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Get the appropriate image driver
     */
    protected function getImageDriver(): ?\Intervention\Image\Interfaces\DriverInterface
    {
        if (! class_exists(\Intervention\Image\ImageManager::class)) {
            return null;
        }

        if (extension_loaded('gd')) {
            return new \Intervention\Image\Drivers\Gd\Driver;
        }

        if (extension_loaded('imagick')) {
            return new \Intervention\Image\Drivers\Imagick\Driver;
        }

        return null;
    }

    /**
     * Check if image processing is available
     */
    public function isImageProcessingAvailable(): bool
    {
        return $this->getImageDriver() !== null;
    }

    /**
     * Scan storage for files not in database
     *
     * @return array{scanned: int, added: int, errors: int}
     */
    public function scan(string $disk = 'public', string $path = 'media'): array
    {
        $stats = [
            'scanned' => 0,
            'added' => 0,
            'errors' => 0,
        ];

        try {
            // Get all files recursively
            $files = Storage::disk($disk)->allFiles($path);

            foreach ($files as $filePath) {
                // Skip hidden files or temporary files
                if (str_starts_with(basename($filePath), '.')) {
                    continue;
                }

                // Skip thumbnails and variants directory
                if (str_contains($filePath, '/thumbnails/') || str_contains($filePath, '/variants/')) {
                    continue;
                }

                // Skip if already exists in DB
                // Use relative path matching (exact match)
                $exists = Media::where('path', $filePath)
                    ->orWhere('path', '/'.$filePath) // legacy paths might have leading slash
                    ->exists();

                if ($exists) {
                    $stats['scanned']++;

                    continue;
                }

                // File is missing in DB, add it
                try {
                    $stats['scanned']++;
                    $fileSize = (int) Storage::disk($disk)->size($filePath);
                    $mimeType = (string) Storage::disk($disk)->mimeType($filePath);
                    $fileName = basename($filePath);

                    // Determine folder ID from path structure?
                    // For now, scan puts everything in root or we could map folders.
                    // Let's keep it simple: just register the file.
                    // Future: map subdirectories to MediaFolder models.

                    $media = Media::create([
                        'name' => (string) pathinfo($fileName, PATHINFO_FILENAME),
                        'file_name' => $fileName,
                        'mime_type' => $mimeType,
                        'disk' => $disk,
                        'path' => $filePath, // Store relative path without leading slash
                        'size' => $fileSize,
                        'folder_id' => null, // Or try to find folder by name?
                        'author_id' => null, // System imported
                        'is_shared' => true, // Imported files usually visible to all
                        'module' => 'core',
                    ]);

                    $stats['added']++;

                    // Generate thumbnail if image
                    if (str_starts_with($mimeType, 'image/')) {
                        try {
                            $this->generateThumbnail($media);
                        } catch (\Exception $e) {
                            // Ignore thumbnail error
                        }
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    Log::channel('media')->error("Failed to import file during scan: {$filePath} - ".$e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::channel('media')->error('Media scan failed: '.$e->getMessage());
        }

        return $stats;
    }

    /**
     * Sanitize SVG file content
     */
    protected function sanitizeSvg(string $filePath): void
    {
        if (! class_exists(\enshrined\svgSanitize\Sanitizer::class)) {
            Log::channel('media')->warning('SVG Sanitizer class not found. Skipping sanitization.');

            return;
        }

        try {
            $sanitizer = new \enshrined\svgSanitize\Sanitizer;
            $sanitizer->removeRemoteReferences(true);

            /** @var string|false $content */
            $content = file_get_contents($filePath);
            if ($content === false) {
                return;
            }

            $cleanContent = $sanitizer->sanitize($content);
            if ($cleanContent === false) {
                return;
            }

            file_put_contents($filePath, $cleanContent);
        } catch (\Exception $e) {
            Log::channel('media')->error('SVG sanitization failed: '.$e->getMessage());
            // We do not stop the upload, but maybe we should?
            // For now, log the error.
        }
    }

    /**
     * Sync tags for media
     *
     * @param  array<int, string>  $tags
     */
    public function syncTags(Media $media, array $tags): void
    {
        $tagIds = [];
        foreach ($tags as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) {
                continue;
            }

            $tag = Tag::firstOrCreate([
                'name' => $tagName,
            ], [
                'slug' => \Illuminate\Support\Str::slug($tagName),
            ]);
            $tagIds[] = $tag->id;
        }
        $media->tags()->sync($tagIds);
    }
}
