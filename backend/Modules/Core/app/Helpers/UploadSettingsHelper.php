<?php

namespace Modules\Core\Helpers;

use Modules\Core\Models\Setting;

/**
 * Helper class for platform-wide upload settings and security.
 * Centralizes whitelist, max size, and validation rules.
 */
class UploadSettingsHelper
{
    /**
     * Get maximum upload size in kilobytes
     */
    public static function getMaxUploadSize(): int
    {
        $size = Setting::get('max_upload_size', 10240);
        /** @var int $sizeInt */
        $sizeInt = is_numeric($size) ? (int) $size : 10240;

        return $sizeInt;
    }

    /**
     * Get allowed image types (comma-separated)
     *
     * @return array<int, string>
     */
    public static function getAllowedImageTypes(): array
    {
        $types = Setting::get('allowed_image_types', 'jpg,jpeg,png,gif,webp,svg');
        /** @var string $typesStr */
        $typesStr = is_string($types) ? $types : 'jpg,jpeg,png,gif,webp,svg';

        return array_map('trim', explode(',', $typesStr));
    }

    /**
     * Get allowed file types (comma-separated)
     *
     * @return array<int, string>
     */
    public static function getAllowedFileTypes(): array
    {
        $types = Setting::get('allowed_file_types', 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar');
        /** @var string $typesStr */
        $typesStr = is_string($types) ? $types : 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar';

        return array_map('trim', explode(',', $typesStr));
    }

    /**
     * Get allowed video types (comma-separated)
     *
     * @return array<int, string>
     */
    public static function getAllowedVideoTypes(): array
    {
        $types = Setting::get('allowed_video_types', 'mp4,webm,ogg');
        /** @var string $typesStr */
        $typesStr = is_string($types) ? $types : 'mp4,webm,ogg';

        return array_map('trim', explode(',', $typesStr));
    }

    /**
     * Get allowed audio types (comma-separated)
     *
     * @return array<int, string>
     */
    public static function getAllowedAudioTypes(): array
    {
        $types = Setting::get('allowed_audio_types', 'mp3,wav,aac');
        /** @var string $typesStr */
        $typesStr = is_string($types) ? $types : 'mp3,wav,aac';

        return array_map('trim', explode(',', $typesStr));
    }

    /**
     * Get all allowed extensions (images + files + video + audio)
     *
     * @return array<int, string>
     */
    public static function getAllowedExtensions(): array
    {
        return array_merge(
            self::getAllowedImageTypes(),
            self::getAllowedFileTypes(),
            self::getAllowedVideoTypes(),
            self::getAllowedAudioTypes()
        );
    }

    /**
     * Check if extension is allowed
     */
    public static function isExtensionAllowed(string $extension): bool
    {
        $extension = strtolower(trim($extension, '.'));

        return in_array($extension, self::getAllowedExtensions());
    }

    /**
     * Should auto-optimize images on upload?
     */
    public static function shouldOptimizeOnUpload(): bool
    {
        return (bool) Setting::get('auto_optimize_images', true);
    }

    /**
     * Get thumbnail width
     */
    public static function getThumbnailWidth(): int
    {
        $width = Setting::get('thumbnail_width', 300);
        /** @var int $widthInt */
        $widthInt = is_numeric($width) ? (int) $width : 300;

        return $widthInt;
    }

    /**
     * Get thumbnail height
     */
    public static function getThumbnailHeight(): int
    {
        $height = Setting::get('thumbnail_height', 300);
        /** @var int $heightInt */
        $heightInt = is_numeric($height) ? (int) $height : 300;

        return $heightInt;
    }

    /**
     * Get storage driver
     */
    public static function getStorageDriver(): string
    {
        $driver = Setting::get('storage_driver', 'local');
        /** @var string $driverStr */
        $driverStr = is_string($driver) ? $driver : 'local';

        return $driverStr;
    }

    /**
     * Get validation rules for file upload
     *
     * @return array<string, array<int, string>>
     */
    public static function getUploadValidationRules(): array
    {
        $maxSize = self::getMaxUploadSize();
        $allowedExtensions = implode(',', self::getAllowedExtensions());

        return [
            'file' => [
                'required',
                'file',
                'max:'.$maxSize,
                'mimes:'.$allowedExtensions,
            ],
        ];
    }

    /**
     * Get validation rules for image upload only
     *
     * @return array<string, array<int, string>>
     */
    public static function getImageUploadValidationRules(): array
    {
        $maxSize = self::getMaxUploadSize();
        $allowedImages = implode(',', self::getAllowedImageTypes());

        return [
            'file' => [
                'required',
                'file',
                'max:'.$maxSize,
                'mimes:'.$allowedImages,
            ],
        ];
    }
}
