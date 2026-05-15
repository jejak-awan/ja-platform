<?php

namespace Modules\Media\Contracts;

use Illuminate\Http\UploadedFile;
use Modules\Media\Models\File;

interface MediaServiceInterface
{
    /**
     * Upload and process a media file.
     */
    public function upload(
        UploadedFile $file,
        ?int $folderId = null,
        bool $optimize = true,
        ?int $authorId = null,
        bool $isShared = false,
        array $metadata = [],
        ?string $subPath = null,
        string $module = 'system'
    ): File;

    /**
     * Optimize an image file.
     */
    public function optimizeImage(string $fullPath, int $maxWidth = 1920, int $quality = 85): bool;

    /**
     * Convert an image to WebP format.
     */
    public function convertToWebP(string $fullPath, int $quality = 85): ?string;

    /**
     * Generate thumbnail for media.
     */
    public function generateThumbnail(File $file, ?int $width = null, ?int $height = null): ?string;

    /**
     * Resize an image.
     */
    public function resize(File $file, int $width, ?int $height = null, int $quality = 85): bool;

    /**
     * Delete media.
     */
    public function delete(File $file, bool $permanent = false): void;

    /**
     * Restore a soft-deleted media item.
     */
    public function restore(int $mediaId): ?File;

    /**
     * Perform bulk action on media.
     * 
     * @return array{media_count: int, folder_count: int}
     */
    public function bulkAction(string $action, array $mediaIds, ?int $folderId = null, ?string $altText = null, array $folderIds = []): array;

    /**
     * Create ZIP download from multiple media.
     */
    public function createZip(array $mediaIds): ?string;

    /**
     * Get usage information for media.
     */
    public function getUsageInfo(File $file): array;
}
