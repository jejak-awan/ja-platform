<?php

namespace Modules\Cms\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Cms\Console\Commands\BackfillThemeJanariParentCommand;
use Modules\Cms\Console\Commands\ThemeMake;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CmsServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Cms';

    protected string $nameLower = 'cms';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->registerUserModuleIntegrations();
        $this->registerDashboardStats();
        $this->registerModelRelations();
        $this->registerCacheIntegrations();
        $this->registerLayoutIntegrations();
    }

    protected function registerLayoutIntegrations(): void
    {
        $this->app->booted(function (): void {
            if ($this->app->bound(\Modules\System\Contracts\LayoutRegistryInterface::class)) {
                $registry = $this->app->make(\Modules\System\Contracts\LayoutRegistryInterface::class);
                $themeService = $this->app->make(\Modules\Layout\Services\ThemeService::class);
                
                try {
                    $activeTheme = $themeService->getActiveTheme('frontend');
                } catch (\Exception) {
                    $activeTheme = null;
                }

                if ($activeTheme) {
                    $registry->registerMenuLocations('cms', $themeService->getMenuLocations($activeTheme));
                    $registry->registerWidgetLocations('cms', $themeService->getWidgetLocations($activeTheme));
                } else {
                    // Fallback defaults
                    $registry->registerMenuLocations('cms', ['header', 'footer', 'sidebar']);
                    $registry->registerWidgetLocations('cms', ['sidebar', 'footer_top', 'footer_bottom']);
                }
            }
        });
    }

    /**
     * Register CMS cache clearers and warmers into Core.
     */
    protected function registerCacheIntegrations(): void
    {
        \Modules\System\Services\CacheService::registerClearer('cms', function (): void {
            app(\Modules\Cms\Services\CmsCacheService::class)->clearAll();
        });

        \Modules\System\Services\CacheWarmingService::registerWarmer('cms', fn() => app(\Modules\Cms\Services\CmsCacheService::class)->warmUp());
    }

    /**
     * Register relationships for Core models that point to CMS models.
     */
    protected function registerModelRelations(): void
    {
        \Modules\Library\Models\Tag::resolveRelationUsing('contents', fn($tagModel) => $tagModel->belongsToMany(\Modules\Cms\Models\Content::class, 'content_tag'));

        \Modules\Analytics\Models\AnalyticsEvent::resolveRelationUsing('content', fn($analyticsModel) => $analyticsModel->belongsTo(\Modules\Cms\Models\Content::class, 'content_id'));
    }

    /**
     * Register CMS-specific stats to Core Dashboard Registry.
     */
    protected function registerDashboardStats(): void
    {
        $this->app->booted(function (): void {
            $registry = $this->app->make(\Modules\System\Services\DashboardRegistry::class);

            $registry->registerStatsProvider('contents', fn() => [
                'total' => \Modules\Cms\Models\Content::count(),
                'published' => \Modules\Cms\Models\Content::where('status', 'published')->count(),
                'draft' => \Modules\Cms\Models\Content::where('status', 'draft')->count(),
                'pending' => \Modules\Cms\Models\Content::where('status', 'pending')->count(),
                'archived' => \Modules\Cms\Models\Content::where('status', 'archived')->count(),
            ]);

            $registry->registerStatsProvider('cms_info', fn() => [
                'categories' => \Modules\Library\Models\Category::count(),
                'comments' => \Modules\Cms\Models\Comment::count(),
                'forms' => \Modules\Forms\Models\Form::count(),
                'form_submissions' => \Modules\Forms\Models\FormSubmission::count(),
                'total_email_templates' => \Modules\System\Models\EmailTemplate::count(),
                'newsletter_subscribers' => \Modules\Newsletter\Models\NewsletterSubscriber::count(),
                'email' => [
                    'templates' => \Modules\System\Models\EmailTemplate::count(),
                    'subscribers' => \Modules\Newsletter\Models\NewsletterSubscriber::count(),
                    'smtp_status' => \Illuminate\Support\Facades\Cache::get('email_smtp_status', 'unknown'),
                ],
            ]);

            $registry->registerChartProvider('contentByStatus', fn() => \Modules\Cms\Models\Content::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get());

            $registry->registerStatsProvider('viewer', fn() => \Modules\Cms\Models\Content::where('status', 'published')
                ->latest()
                ->take(5)
                ->select('id', 'title', 'slug', 'created_at')
                ->get());
        });
    }

    /**
     * Register CMS-specific integrations to Core User model.
     */
    protected function registerUserModuleIntegrations(): void
    {
        // Register CMS-specific role ranks
        \Modules\System\Models\User::registerRoleRanks([
            'editor' => 60,
            'author' => 40,
        ]);
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
            //
        ]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom((string) module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom((string) module_path($this->name, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        /** @var string $configPathRelative */
        $configPathRelative = config('modules.paths.generator.config.path');
        $configPath = (string) module_path($this->name, $configPathRelative);

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            /** @var \SplFileInfo $file */
            foreach ($iterator as $file) {
                /** @var \SplFileInfo $file */
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $config = str_replace($configPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $config_key = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
                    $segments = explode('.', $this->nameLower.'.'.$config_key);

                    // Remove duplicated adjacent segments
                    $normalized = [];
                    foreach ($segments as $segment) {
                        if (end($normalized) !== $segment) {
                            $normalized[] = $segment;
                        }
                    }

                    $key = ($config === 'config.php') ? $this->nameLower : implode('.', $normalized);

                    $this->publishes([$file->getPathname() => config_path($config)], 'config');
                    $this->merge_config_from($file->getPathname(), $key);
                }
            }
        }
    }

    /**
     * Merge config from the given path recursively.
     */
    protected function merge_config_from(string $path, string $key): void
    {
        $existing = (array) config($key, []);
        $existing = is_array($existing) ? $existing : [];
        $module_config = require $path;
        $module_config = is_array($module_config) ? $module_config : [];

        config([$key => array_replace_recursive((array) $existing, (array) $module_config)]);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = (string) module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        /** @var string $namespace */
        $namespace = config('modules.namespace');
        Blade::componentNamespace($namespace.'\\'.$this->name.'\\View\\Components', $this->nameLower);
    }

    /**
     * @return array<string>
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    private function getPublishableViewPaths(): array
    {
        $paths = [];
        $viewPaths = config('view.paths');
        if (is_iterable($viewPaths)) {
            foreach ($viewPaths as $path) {
                $path = (string) $path;
                if (is_dir($path.'/modules/'.$this->nameLower)) {
                    $paths[] = $path.'/modules/'.$this->nameLower;
                }
            }
        }

        return $paths;
    }
}
