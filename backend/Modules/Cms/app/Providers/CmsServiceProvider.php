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
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));

        $this->registerUserModuleIntegrations();
        $this->registerDashboardStats();
        $this->registerModelRelations();
        $this->registerCacheIntegrations();
    }

    /**
     * Register CMS cache clearers and warmers into Core.
     */
    protected function registerCacheIntegrations(): void
    {
        \Modules\Core\Services\CacheService::registerClearer('cms', function () {
            app(\Modules\Cms\Services\CmsCacheService::class)->clearAll();
        });

        \Modules\Core\Services\CacheWarmingService::registerWarmer('cms', function () {
            return app(\Modules\Cms\Services\CmsCacheService::class)->warmUp();
        });
    }

    /**
     * Register relationships for Core models that point to CMS models.
     */
    protected function registerModelRelations(): void
    {
        \Modules\Core\Models\Tag::resolveRelationUsing('contents', function ($tagModel) {
            return $tagModel->belongsToMany(\Modules\Cms\Models\Content::class, 'content_tag');
        });

        \Modules\Core\Models\AnalyticsEvent::resolveRelationUsing('content', function ($analyticsModel) {
            return $analyticsModel->belongsTo(\Modules\Cms\Models\Content::class, 'content_id');
        });
    }

    /**
     * Register CMS-specific stats to Core Dashboard Registry.
     */
    protected function registerDashboardStats(): void
    {
        $this->app->booted(function () {
            $registry = $this->app->make(\Modules\Core\Services\DashboardRegistry::class);

            $registry->registerStatsProvider('contents', function () {
                return [
                    'total' => \Modules\Cms\Models\Content::count(),
                    'published' => \Modules\Cms\Models\Content::where('status', 'published')->count(),
                    'draft' => \Modules\Cms\Models\Content::where('status', 'draft')->count(),
                    'pending' => \Modules\Cms\Models\Content::where('status', 'pending')->count(),
                    'archived' => \Modules\Cms\Models\Content::where('status', 'archived')->count(),
                ];
            });

            $registry->registerStatsProvider('cms_info', function () {
                return [
                    'categories' => \Modules\Cms\Models\Category::count(),
                    'comments' => \Modules\Cms\Models\Comment::count(),
                    'forms' => \Modules\Cms\Models\Form::count(),
                    'form_submissions' => \Modules\Cms\Models\FormSubmission::count(),
                    'total_email_templates' => \Modules\Cms\Models\EmailTemplate::count(),
                    'newsletter_subscribers' => \Modules\Cms\Models\NewsletterSubscriber::count(),
                    'email' => [
                        'templates' => \Modules\Cms\Models\EmailTemplate::count(),
                        'subscribers' => \Modules\Cms\Models\NewsletterSubscriber::count(),
                        'smtp_status' => \Illuminate\Support\Facades\Cache::get('email_smtp_status', 'unknown'),
                    ],
                ];
            });

            $registry->registerChartProvider('contentByStatus', function () {
                return \Modules\Cms\Models\Content::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->get();
            });

            $registry->registerStatsProvider('viewer', function () {
                return \Modules\Cms\Models\Content::where('status', 'published')
                    ->latest()
                    ->take(5)
                    ->select('id', 'title', 'slug', 'created_at')
                    ->get();
            });
        });
    }

    /**
     * Register CMS-specific integrations to Core User model.
     */
    protected function registerUserModuleIntegrations(): void
    {
        // Register CMS-specific role ranks
        \Modules\Core\Models\User::registerRoleRanks([
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
            BackfillThemeJanariParentCommand::class,
            ThemeMake::class,
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
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        /** @var string $configPathRelative */
        $configPathRelative = config('modules.paths.generator.config.path');
        $configPath = module_path($this->name, $configPathRelative);

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                /** @var \SplFileInfo $file */
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $config = str_replace($configPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $config_key = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
                    $segments = explode('.', (string) $this->nameLower.'.'.$config_key);

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
        $existing = config($key, []);
        $existing = is_array($existing) ? $existing : [];
        $module_config = require $path;
        $module_config = is_array($module_config) ? $module_config : [];

        config([$key => array_replace_recursive($existing, $module_config)]);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

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
