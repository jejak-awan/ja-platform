<?php

namespace Modules\Cms\Models;

use Modules\System\Traits\ScopedByWorkspace;
 
use Illuminate\Database\Eloquent\Factories\HasFactory as EloquentHasFactory;
use Illuminate\Database\Eloquent\Model;

use Modules\Cms\Support\ThemeViews;
use Illuminate\Support\Facades\Cache;
use Modules\Cms\Services\ThemeCacheService;

/**
 * @property int $id
 * @property int|null $workspace_id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property string $path
 * @property string|null $parent_theme
 * @property string $version
 * @property string|null $description
 * @property string|null $author
 * @property string|null $author_url
 * @property string|null $license
 * @property string|null $preview_image
 * @property array<string, mixed>|null $settings
 * @property string|null $custom_css
 * @property array<string, mixed>|null $dependencies
 * @property array<string|int, mixed>|null $supports
 * @property bool $is_active
 * @property string $status
 * @property string|null $update_url
 * @property bool $auto_update
 * @property string|null $requires_cms_version
 * @property \Illuminate\Support\Carbon|null $last_updated_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<string, mixed>|null $manifest
 * @property array<string, mixed> $assets
 */
class Theme extends Model
{
    protected $table = 'cms_themes';

    /** @use HasFactory<\Modules\Cms\Database\Factories\ThemeFactory> */
    use EloquentHasFactory, ScopedByWorkspace;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\Cms\Database\Factories\ThemeFactory
    {
        return \Modules\Cms\Database\Factories\ThemeFactory::new();
    }

    protected $fillable = [
        'workspace_id',
        'name',
        'slug',
        'type',
        'path',
        'parent_theme',
        'version',
        'description',
        'author',
        'author_url',
        'license',
        'preview_image',
        'settings',
        'custom_css',
        'dependencies',
        'supports',
        'is_active',
        'status',
        'update_url',
        'auto_update',
        'requires_cms_version',
        'last_updated_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'dependencies' => 'array',
        'supports' => 'array',
        'is_active' => 'boolean',
        'auto_update' => 'boolean',
        'last_updated_at' => 'datetime',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get active theme by type
     * Auto-activates default theme if no theme is active
     */
    public static function getActiveTheme(string $type = 'frontend'): ?self
    {
        $workspaceId = \Illuminate\Support\Facades\Context::get('workspace_id');

        // 1. Try to find workspace-specific active theme
        if ($workspaceId) {
            $activeTheme = self::withoutGlobalScope('workspace')
                ->where('workspace_id', $workspaceId)
                ->where('is_active', true)
                ->where('type', $type)
                ->where('status', 'active')
                ->first();

            if ($activeTheme) {
                return $activeTheme;
            }
        }

        // 2. Fallback to global active theme
        $activeTheme = self::withoutGlobalScope('workspace')
            ->whereNull('workspace_id')
            ->where('is_active', true)
            ->where('type', $type)
            ->where('status', 'active')
            ->first();

        // 3. Auto-activate default global theme if still none
        if (! $activeTheme) {
            $defaultTheme = self::withoutGlobalScope('workspace')
                ->whereNull('workspace_id')
                ->where('type', $type)
                ->where('slug', 'default')
                ->first();

            if (! $defaultTheme) {
                $defaultTheme = self::withoutGlobalScope('workspace')
                    ->whereNull('workspace_id')
                    ->where('type', $type)
                    ->orderBy('id')
                    ->first();
            }

            if ($defaultTheme) {
                try {
                    $defaultTheme->update([
                        'is_active' => true,
                        'status' => 'active',
                    ]);
                    Cache::forget(ThemeCacheService::PREFIX_ACTIVE.$type);

                    return $defaultTheme->fresh();
                } catch (\Exception $e) {
                    \Log::warning('Failed to auto-activate default theme: '.$e->getMessage());
                }
            }
        }

        return $activeTheme;
    }

    /**
     * Activate this theme
     */
    public function activate(): bool
    {
        // Validate theme before activation (warn but don't block)
        $errors = $this->validate();
        if (! empty($errors)) {
            // Log warnings but allow activation
            \Log::warning("Theme '{$this->name}' has validation warnings but will be activated", [
                'theme_id' => $this->id,
                'errors' => $errors,
            ]);

            // Only block if critical errors (invalid JSON)
            $criticalErrors = array_filter($errors, function ($error) {
                return strpos($error, 'Invalid theme.json format') !== false;
            });

            if (! empty($criticalErrors)) {
                throw new \Exception("Theme '{$this->name}' is invalid and cannot be activated: ".implode(', ', $criticalErrors));
            }
        }

        // Deactivate all other themes of the same type
        self::where('id', '!=', $this->id)
            ->where('type', $this->type)
            ->update(['is_active' => false]);

        // Activate this theme
        $this->update([
            'is_active' => true,
            'status' => 'active',
        ]);

        // Clear cache
        Cache::forget(ThemeCacheService::PREFIX_ACTIVE.$this->type);

        return true;
    }

    /**
     * Deactivate this theme
     */
    public function deactivate(): bool
    {
        $this->update(['is_active' => false]);
        Cache::forget(ThemeCacheService::PREFIX_ACTIVE.$this->type);

        return true;
    }

    /**
     * Get theme path
     */
    public function getThemePath(): string
    {
        // Code-First themes are located in frontend/src/modules/Cms/views/themes
        // We use slug as the reliable folder name. Path is relative to project root.
        return ThemeViews::pathForSlug($this->slug);
    }

    /**
     * Get theme public path (virtual path for assets)
     */
    public function getPublicPath(): string
    {
        return "themes/{$this->slug}";
    }

    /**
     * Check if theme has parent
     */
    public function hasParent(): bool
    {
        return ! empty($this->parent_theme);
    }

    /**
     * Get parent theme
     */
    public function getParent(): ?self
    {
        if (! $this->hasParent()) {
            return null;
        }

        return self::where('slug', $this->parent_theme)->first();
    }

    /**
     * Check if theme supports a feature
     */
    public function supports(string $feature): bool
    {
        if (! $this->supports) {
            return false;
        }

        return in_array($feature, $this->supports) ||
               (isset($this->supports[$feature]) && $this->supports[$feature] === true);
    }

    /**
     * Get theme setting
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        if (! is_array($this->settings)) {
            return $default;
        }

        return $this->settings[$key] ?? $default;
    }

    /**
     * Set theme setting
     */
    public function setSetting(string $key, mixed $value): void
    {
        /** @var array<string, mixed> $settings */
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->update(['settings' => $settings]);
    }

    /**
     * Validate theme structure
     *
     * @return array<int, string>
     */
    public function validate(): array
    {
        /** @var array<int, string> $errors */
        $errors = [];
        $themePath = $this->getThemePath();

        // Check if theme directory exists
        if (! is_dir($themePath)) {
            $errors[] = "Theme directory does not exist: {$themePath}";

            return $errors;
        }

        // Check for theme.json
        $manifestPath = "{$themePath}/theme.json";
        if (! file_exists($manifestPath)) {
            $errors[] = 'Theme manifest (theme.json) not found';
        } else {
            // Validate manifest
            $content = file_get_contents($manifestPath);
            if ($content === false) {
                $errors[] = 'Could not read theme.json';
            } else {
                $manifest = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errors[] = 'Invalid theme.json format: '.json_last_error_msg();
                }
            }
        }

        // For code-first Vue themes, assets directory is optional.
        // Many themes are fully component-driven and ship no static assets folder.
        if (! is_dir("{$themePath}/assets")) {
            \Log::info("Theme '{$this->name}' does not have an assets directory");
        }

        // Check for Vue components directory (optional but recommended)
        if (! is_dir("{$themePath}/components")) {
            // Not an error, just a warning
            \Log::info("Theme '{$this->name}' does not have a components directory");
        }

        // Update status based on validation
        if (empty($errors)) {
            $this->update(['status' => 'active']);
        } else {
            $this->update(['status' => 'broken']);
        }

        return $errors;
    }

    /**
     * Get theme manifest
     *
     * @return array<string, mixed>|null
     */
    public function getManifest(): ?array
    {
        $manifestPath = "{$this->getThemePath()}/theme.json";

        if (! file_exists($manifestPath)) {
            return null;
        }

        $content = file_get_contents($manifestPath);
        if ($content === false) {
            return null;
        }

        $manifest = json_decode($content, true);

        /** @var array<string, mixed>|null $manifest */
        return is_array($manifest) ? $manifest : null;
    }

    /**
     * Check if theme has updates available
     */
    public function hasUpdate(): bool
    {
        if (! $this->update_url) {
            return false;
        }

        // TODO: Implement update check logic
        return false;
    }

    /**
     * Get CSS assets
     *
     * @return array<int, string>
     */
    public function getCssAssets(): array
    {
        $assets = [];
        $themePath = $this->getThemePath();
        $cssDir = "{$themePath}/assets/css";

        if (is_dir($cssDir)) {
            $files = glob("{$cssDir}/*.css");
            if (is_array($files)) {
                foreach ($files as $file) {
                    $assets[] = basename($file);
                }
            }
        }

        return $assets;
    }

    /**
     * Get JS assets
     *
     * @return array<int, string>
     */
    public function getJsAssets(): array
    {
        $assets = [];
        $themePath = $this->getThemePath();
        $jsDir = "{$themePath}/assets/js";

        if (is_dir($jsDir)) {
            $files = glob("{$jsDir}/*.js");
            if (is_array($files)) {
                foreach ($files as $file) {
                    $assets[] = basename($file);
                }
            }
        }

        return $assets;
    }

    /**
     * Scope: Get themes by type
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     * @return \Illuminate\Database\Eloquent\Builder<self>
     */
    public function scopeOfType(\Illuminate\Database\Eloquent\Builder $query, string $type): \Illuminate\Database\Eloquent\Builder
    {
        if ($type === 'all') {
            return $query;
        }

        return $query->where('type', $type);
    }

    /**
     * Scope: Get active themes
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     * @return \Illuminate\Database\Eloquent\Builder<self>
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get themes by status
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     * @return \Illuminate\Database\Eloquent\Builder<self>
     */
    public function scopeByStatus(\Illuminate\Database\Eloquent\Builder $query, string $status): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', $status);
    }

    // =====================================================
    // VUE SPA METHODS (Added for Vue-based themes)
    // =====================================================

    /**
     * Check if theme has Vue components
     */
    public function hasVueComponents(): bool
    {
        $themePath = $this->getThemePath();
        $componentsDir = "{$themePath}/components";

        if (! is_dir($componentsDir)) {
            return false;
        }

        $files = glob("{$componentsDir}/*");

        return is_array($files) && count($files) > 0;
    }

    /**
     * Get Vue components directory path
     */
    public function getVueComponentsPath(): ?string
    {
        $themePath = $this->getThemePath();
        $componentsDir = "{$themePath}/components";

        return is_dir($componentsDir) ? $componentsDir : null;
    }

    /**
     * Get components manifest from theme.json
     *
     * @return array<string, mixed>
     */
    public function getComponentManifest(): array
    {
        $manifest = $this->getManifest();

        /** @var array<string, mixed> $components */
        $components = $manifest['components'] ?? [];

        return $components;
    }

    /**
     * Get composables directory path
     */
    public function getComposablesPath(): ?string
    {
        $themePath = $this->getThemePath();
        $composablesDir = "{$themePath}/composables";

        return is_dir($composablesDir) ? $composablesDir : null;
    }

    /**
     * Get theme configuration
     *
     * @return array{settings_schema: array<mixed>, supports: array<mixed>, menus: array<mixed>, components: array<mixed>}
     */
    public function getThemeConfig(): array
    {
        $manifest = $this->getManifest() ?? [];

        return [
            'settings_schema' => is_array($manifest['settings_schema'] ?? null) ? $manifest['settings_schema'] : [],
            'supports' => is_array($manifest['supports'] ?? null) ? $manifest['supports'] : [],
            'menus' => is_array($manifest['menus'] ?? null) ? $manifest['menus'] : [],
            'components' => is_array($manifest['components'] ?? null) ? $manifest['components'] : [],
        ];
    }

    /**
     * Check if theme is Vue-based
     */
    public function isVueBased(): bool
    {
        $manifest = $this->getManifest();

        return isset($manifest['framework']) && $manifest['framework'] === 'vue';
    }
}
