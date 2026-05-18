<?php

namespace Modules\System\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Modules\System\Console\Commands\CleanupOldLogs;
use Modules\System\Contracts\LayoutRegistryInterface;
use Modules\System\Facades\Hook;
use Modules\System\Http\Controllers\Console\DashboardController;
use Modules\System\Registries\DashboardRegistry;
use Modules\System\Registries\HookRegistry;
use Modules\System\Registries\LayoutRegistry;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class SystemServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'System';

    protected string $nameLower = 'system';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        // Super Admin Gate
        Gate::before(fn ($user, $capability) => $user->hasRole('super') ? true : null);

        // Register Request Macro for CSP Nonce
        Request::macro('cspNonce', function () {
            if (! $this->has('__csp_nonce')) {
                $this->attributes->set('__csp_nonce', Str::random(32));
            }

            return $this->attributes->get('__csp_nonce');
        });

        $this->app->booted(function (): void {
            if ($this->app->bound(DashboardRegistry::class)) {
                $registry = $this->app->make(DashboardRegistry::class);

                // Register Media Stats
                $registry->register('media', [
                    'title' => 'Media Library',
                    'component' => 'MediaStatsWidget',
                    'width' => '1/2',
                    'data_callback' => [DashboardController::class, 'getMediaStats'],
                ]);

                // Register User Stats
                $registry->register('users', [
                    'title' => 'User Management',
                    'component' => 'UserStatsWidget',
                    'width' => '1/2',
                    'data_callback' => [DashboardController::class, 'getUserStats'],
                ]);
            }
        });

        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        $this->app->singleton(DashboardRegistry::class);
        $this->app->singleton(\Modules\System\Services\DashboardRegistry::class);
        $this->app->singleton(HookRegistry::class);
        $this->app->singleton(LayoutRegistryInterface::class, LayoutRegistry::class);

        // Register Global Hook Facade Alias
        if (class_exists(AliasLoader::class)) {
            AliasLoader::getInstance()->alias('Hook', Hook::class);
        }
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
            CleanupOldLogs::class,
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
        $configPath = (string) module_path($this->name, (string) config('modules.paths.generator.config.path'));

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            /** @var \SplFileInfo $file */
            foreach ($iterator as $file) {
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
        $module_config = require $path;

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

        Blade::componentNamespace((string) config('modules.namespace').'\\'.$this->name.'\\View\\Components', $this->nameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach ((array) config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }
}
