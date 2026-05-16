<?php

namespace Modules\Media\Helpers;

use Modules\System\Models\Setting;

class UploadSettingsHelper
{
    /**
     * Get maximum upload size in kilobytes
     */
    public static function getMaxUploadSize(): int
    {
        return (int) Setting::get('max_upload_size', 5120); // Default 5MB
    }

    /**
     * Get allowed file extensions
     *
     * @return string[]
     */
    public static function getAllowedExtensions(): array
    {
        $extensions = Setting::get('allowed_upload_extensions', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,zip');
        return explode(',', $extensions);
    }

    /**
     * Get allowed image types
     *
     * @return string[]
     */
    public static function getAllowedImageTypes(): array
    {
        $types = Setting::get('allowed_image_types', 'jpg,jpeg,png,gif,webp');
        return explode(',', $types);
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
