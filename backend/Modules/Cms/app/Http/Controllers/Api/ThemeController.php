<?php

namespace Modules\Cms\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Cms\Models\Theme;
use Modules\Cms\Services\ThemeService;
use Modules\Core\Http\Controllers\Api\BaseApiController;

class ThemeController extends BaseApiController
{
    protected ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $typeRaw = $request->input('type', 'frontend');
        $type = is_string($typeRaw) ? $typeRaw : 'frontend';
        $themes = Theme::ofType($type)->latest()->get();

        // Attach manifest to each theme
        $themes->each(function ($theme) {
            $theme->manifest = $theme->getManifest();
        });

        return $this->success($themes, 'Themes retrieved successfully');
    }

    // Store method removed (Themes are code-managed)

    public function show(Theme $theme): \Illuminate\Http\JsonResponse
    {
        $this->normalizeThemeAdvancedBindings($theme);

        // Load theme assets
        $assets = $this->themeService->loadThemeAssets($theme);
        $theme->assets = $assets;

        // Load manifest if available
        $manifest = $theme->getManifest();
        if ($manifest) {
            $theme->manifest = $manifest;
        } else {
            // Provide default settings schema if no manifest
            $theme->manifest = [
                'name' => $theme->name,
                'version' => $theme->version ?? '1.0.0',
                'description' => $theme->description ?? '',
                'author' => $theme->author ?? '',
                'settings_schema' => $this->themeService->getDefaultSettingsSchema(),
            ];
        }

        return $this->success($theme, 'Theme retrieved successfully');
    }

    public function update(Request $request, Theme $theme): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:themes,slug,'.$theme->id,
            'type' => 'sometimes|string|in:frontend,admin,email',
            'version' => 'nullable|string',
            'description' => 'nullable|string',
            'author' => 'nullable|string',
            'author_url' => 'nullable|url',
            'license' => 'nullable|string',
            'preview_image' => 'nullable|string',
            'settings' => 'nullable|array',
            'custom_css' => 'nullable|string',
            'parent_theme' => 'nullable|string|exists:themes,slug',
            'dependencies' => 'nullable|array',
            'supports' => 'nullable|array',
        ]);

        $theme->update($validated);

        // Clear cache after update
        $this->themeService->clearThemeCache($theme);

        return $this->success($theme, 'Theme updated successfully');
    }

    public function destroy(Theme $theme): \Illuminate\Http\JsonResponse
    {
        if ($theme->is_active) {
            return $this->validationError(
                ['theme' => ['Cannot delete active theme']],
                'Cannot delete active theme'
            );
        }

        $theme->delete();
        $this->themeService->clearThemeCache($theme);

        return $this->success(null, 'Theme deleted successfully');
    }

    public function activate(Theme $theme): \Illuminate\Http\JsonResponse
    {
        try {
            $this->themeService->activateTheme($theme);

            return $this->success([
                'theme' => $theme->fresh(),
            ], 'Theme activated successfully');
        } catch (\Exception $e) {
            Log::error('Theme activation failed', [
                'theme_id' => $theme->id,
                'theme_slug' => $theme->slug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error($e->getMessage(), 422);
        }
    }

    public function deactivate(Theme $theme): \Illuminate\Http\JsonResponse
    {
        try {
            $this->themeService->deactivateTheme($theme);

            return $this->success([
                'theme' => $theme->fresh(),
            ], 'Theme deactivated successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function getActive(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $typeRaw = $request->input('type', 'frontend');
            $type = is_string($typeRaw) ? $typeRaw : 'frontend';
            $theme = $this->themeService->getActiveTheme($type);

            if (! $theme) {
                // Return null instead of 404 for public endpoint
                // Frontend can work without theme
                return $this->success(null, 'No active theme found');
            }

            $this->normalizeThemeAdvancedBindings($theme);

            // Load assets
            $assets = $this->themeService->loadThemeAssets($theme);
            $theme->assets = $assets;

            // Load manifest
            $theme->manifest = $theme->getManifest();

            return $this->success($theme, 'Active theme retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to get active theme: '.$e->getMessage());

            // Return null instead of error for public endpoint
            return $this->success(null, 'Theme service unavailable');
        }
    }

    public function updateSettings(Request $request, Theme $theme): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        try {
            // Merge with existing settings instead of replacing
            $existingSettings = is_array($theme->settings) ? $theme->settings : [];
            $settingsInput = is_array($validated['settings']) ? $validated['settings'] : [];

            $newSettings = array_merge($existingSettings, $settingsInput);
            $newSettings = $this->normalizeAdvancedBindingsInSettings($newSettings);

            $theme->update(['settings' => $newSettings]);

            // Sync brand_logo to global site_logo
            if (isset($settingsInput['brand_logo'])) {
                \Modules\Core\Models\Setting::set('site_logo', $settingsInput['brand_logo'], 'image', 'general');
            }

            $this->themeService->clearThemeCache($theme);

            return $this->success($theme->fresh(), 'Theme settings updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update theme settings: '.$e->getMessage(), [
                'theme_id' => $theme->id,
                'error' => $e->getTraceAsString(),
            ]);

            return $this->error('Failed to update theme settings: '.$e->getMessage(), 500);
        }
    }

    public function updateCustomCss(Request $request, Theme $theme): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'custom_css' => 'nullable|string',
        ]);

        $customCss = isset($validated['custom_css']) && is_string($validated['custom_css']) ? $validated['custom_css'] : '';
        $theme->update(['custom_css' => $customCss]);
        $this->themeService->clearThemeCache($theme);

        return $this->success($theme, 'Theme custom CSS updated successfully');
    }

    public function validate(Theme $theme): \Illuminate\Http\JsonResponse
    {
        $errors = $this->themeService->validateTheme($theme);

        if (empty($errors)) {
            return $this->success([
                'valid' => true,
                'theme' => $theme->fresh(),
            ], 'Theme is valid');
        }

        return $this->success([
            'valid' => false,
            'errors' => $errors,
            'theme' => $theme->fresh(),
        ], 'Theme validation completed');
    }

    // Legacy Blade methods removed

    public function scan(): \Illuminate\Http\JsonResponse
    {
        try {
            $themes = $this->themeService->scanThemes();

            return $this->success([
                'themes' => $themes,
                'count' => count($themes),
            ], 'Themes scanned successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function getSetting(Theme $theme, Request $request): \Illuminate\Http\JsonResponse
    {
        $keyRaw = $request->input('key');
        $key = is_string($keyRaw) ? $keyRaw : '';
        $default = $request->input('default');

        if (! $key) {
            return $this->validationError(['key' => ['Key is required']], 'Key is required');
        }

        $value = $this->themeService->getThemeSetting($theme, $key, $default);

        return $this->success([
            'key' => $key,
            'value' => $value,
        ], 'Theme setting retrieved successfully');
    }

    // Import/Export methods removed

    // =====================================================
    // VUE SPA ENDPOINTS (New methods for Vue themes)
    // =====================================================

    /**
     * Get active theme menu locations
     */
    public function locations(Request $request): \Illuminate\Http\JsonResponse
    {
        $typeRaw = $request->input('type', 'frontend');
        $type = is_string($typeRaw) ? $typeRaw : 'frontend';
        $theme = $this->themeService->getActiveTheme($type);

        if (! $theme) {
            return $this->success([], 'No active theme found');
        }

        $locations = $this->themeService->getMenuLocations($theme);

        return $this->success($locations, 'Menu locations retrieved successfully');
    }

    /**
     * Get Vue components manifest
     */
    public function getComponents(Theme $theme): \Illuminate\Http\JsonResponse
    {
        try {
            $componentManifest = $theme->getComponentManifest();

            return $this->success([
                'components' => $componentManifest,
                'has_vue_components' => $theme->hasVueComponents(),
                'is_vue_based' => $theme->isVueBased(),
            ], 'Theme components retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get theme configuration
     */
    public function getConfig(Theme $theme): \Illuminate\Http\JsonResponse
    {
        try {
            $config = $theme->getThemeConfig();

            return $this->success($config, 'Theme configuration retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get theme composables
     */
    public function getComposables(Theme $theme): \Illuminate\Http\JsonResponse
    {
        try {
            $composablesPath = $theme->getComposablesPath();

            if (! $composablesPath) {
                return $this->success([
                    'has_composables' => false,
                    'message' => 'Theme does not have composables directory',
                ]);
            }

            /** @var list<string>|false $composableFiles */
            $composableFiles = glob("{$composablesPath}/*.js");
            $composables = $composableFiles ? array_map('basename', $composableFiles) : [];

            return $this->success([
                'has_composables' => true,
                'composables' => $composables,
            ], 'Theme composables retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Normalize legacy advanced binding keys in theme settings.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function normalizeAdvancedBindingsInSettings(array $settings): array
    {
        $bindings = $settings['_advanced_bindings'] ?? null;
        if (! is_array($bindings)) {
            return $settings;
        }

        /** @var array<string, array<int, string>> $componentAliases */
        $componentAliases = [
            'majors' => ['programs'],
            'partners' => ['partner'],
        ];

        /** @var array<string, array<string, array<int, string>>> $slotAliases */
        $slotAliases = [
            'majors' => ['programs' => ['default']],
            'stats' => ['counters' => ['default']],
            'testimonials' => ['items' => ['default']],
            'partners' => ['partners' => ['default']],
        ];

        /** @var array<string, mixed> $normalized */
        $normalized = [];

        foreach ($bindings as $componentId => $componentConfig) {
            if (! is_array($componentConfig)) {
                continue;
            }

            $targetComponentId = $componentId;
            foreach ($componentAliases as $canonical => $aliases) {
                if ($componentId === $canonical || in_array($componentId, $aliases, true)) {
                    $targetComponentId = $canonical;
                    break;
                }
            }

            $existingComponent = $normalized[$targetComponentId] ?? ['slots' => []];
            $slotsInput = $componentConfig['slots'] ?? [];
            if (! is_array($slotsInput)) {
                $slotsInput = [];
            }

            foreach ($slotsInput as $slotId => $slotConfig) {
                if (! is_array($slotConfig)) {
                    continue;
                }

                $targetSlotId = $slotId;
                $slotMap = $slotAliases[$targetComponentId] ?? [];
                foreach ($slotMap as $canonicalSlot => $aliases) {
                    if ($slotId === $canonicalSlot || in_array($slotId, $aliases, true)) {
                        $targetSlotId = $canonicalSlot;
                        break;
                    }
                }

                // Normalize api_pages selector to slug key while keeping backward compatibility.
                if (! isset($slotConfig['pageSlug']) && isset($slotConfig['pageId']) && is_scalar($slotConfig['pageId'])) {
                    $slotConfig['pageSlug'] = (string) $slotConfig['pageId'];
                }

                if (! isset($slotConfig['propMapping']) || ! is_array($slotConfig['propMapping'])) {
                    $slotConfig['propMapping'] = [];
                }

                $existingComponent['slots'][$targetSlotId] = array_merge(
                    is_array($existingComponent['slots'][$targetSlotId] ?? null) ? $existingComponent['slots'][$targetSlotId] : [],
                    $slotConfig
                );
            }

            $normalized[$targetComponentId] = array_merge($componentConfig, $existingComponent);
        }

        $settings['_advanced_bindings'] = $normalized;

        return $settings;
    }

    private function normalizeThemeAdvancedBindings(Theme $theme): void
    {
        $settings = is_array($theme->settings) ? $theme->settings : [];
        $theme->settings = $this->normalizeAdvancedBindingsInSettings($settings);
    }
}
