<?php

namespace Modules\Cms\Helpers;

use Modules\Cms\Models\Theme;
use Modules\Cms\Services\ThemeService;

/**
 * Theme Helper Class
 * Provides static methods for interacting with the Vue-based theme system.
 * Handles theme data retrieval, settings, assets, and feature detection.
 */
class ThemeHelper
{
    protected static ?ThemeService $themeService = null;

    /**
     * Get ThemeService instance
     */
    protected static function getThemeService(): ThemeService
    {
        if (self::$themeService === null) {
            self::$themeService = app(ThemeService::class);
        }

        return self::$themeService;
    }

    /**
     * Get active theme
     */
    public static function activeTheme(string $type = 'frontend'): ?Theme
    {
        try {
            return self::getThemeService()->getActiveTheme($type);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get theme setting
     */
    public static function setting(string $key, mixed $default = null, string $type = 'frontend'): mixed
    {
        try {
            $theme = self::getThemeService()->getActiveTheme($type);
            if ($theme) {
                return self::getThemeService()->getThemeSetting($theme, $key, $default);
            }
        } catch (\Exception $e) {
            // Return default on error
        }

        return $default;
    }

    /**
     * Get theme asset URL
     */
    public static function asset(string $path, string $type = 'frontend'): ?string
    {
        try {
            $theme = self::getThemeService()->getActiveTheme($type);
            if ($theme && $theme->path) {
                $themePath = storage_path('app/themes/'.$theme->path);
                $assetPath = $themePath.'/'.ltrim($path, '/');

                if (file_exists($assetPath)) {
                    // Use /themes/{slug}/ path (via symlink)
                    return asset('themes/'.$theme->path.'/'.ltrim($path, '/'));
                }
            }
        } catch (\Exception $e) {
            // Return null on error
        }

        return null;
    }

    /**
     * Check if theme supports a feature
     */
    public static function supports(string $feature, string $type = 'frontend'): bool
    {
        try {
            $theme = self::getThemeService()->getActiveTheme($type);
            if ($theme && $theme->supports) {
                $supports = $theme->supports;

                return isset($supports[$feature]) && $supports[$feature] === true;
            }
        } catch (\Exception $e) {
            // Return false on error
        }

        return false;
    }

    /**
     * Get theme custom CSS
     */
    public static function customCss(string $type = 'frontend'): string
    {
        try {
            $theme = self::getThemeService()->getActiveTheme($type);
            if ($theme) {
                return self::getThemeService()->getThemeCustomCss($theme);
            }
        } catch (\Exception $e) {
            // Return empty string on error
        }

        return '';
    }

    /**
     * Get menu by slug (for API use)
     */
    public static function getMenu(string $slug): ?\Modules\Cms\Models\Menu
    {
        try {
            return \Modules\Cms\Models\Menu::where('slug', $slug)
                ->where('is_active', true)
                ->with(['items' => function ($query) {
                    $query->where('is_active', true)
                        ->whereNull('parent_id')
                        ->orderBy('sort_order');
                }, 'items.children' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('sort_order');
                }])
                ->first();
        } catch (\Exception $e) {
            return null;
        }
    }
}
