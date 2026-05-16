<?php

namespace Modules\Layout\Support;

/**
 * Filesystem root for Vue themes (code-first, lives in frontend package).
 *
 * Override with CMS_THEME_VIEWS_RELATIVE_PATH when backend is not in the default monorepo layout.
 */
final class ThemeViews
{
    public static function relativePathFromBackendRoot(): string
    {
        $raw = config('cms.theme_views_relative_path');
        if (! is_string($raw) || trim($raw) === '') {
            return '../frontend/src/modules/Cms/views/themes';
        }

        return trim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $raw), DIRECTORY_SEPARATOR);
    }

    public static function rootPath(): string
    {
        return base_path(self::relativePathFromBackendRoot());
    }

    public static function pathForSlug(string $slug): string
    {
        return self::rootPath().DIRECTORY_SEPARATOR.$slug;
    }
}
