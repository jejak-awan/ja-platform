<?php

namespace Modules\Core\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Modules\Core\Console\Commands\AutoTuneSecurityCommand;
use Modules\Core\Console\Commands\AssignSecurityOfficer;
use Modules\Core\Console\Commands\CheckFileIntegrity;
use Modules\Core\Console\Commands\CheckThreatIntel;
use Modules\Core\Console\Commands\CleanupCspReports;
use Modules\Core\Console\Commands\CleanupOldLogs;
use Modules\Core\Console\Commands\CleanupSlowQueryLogs;
use Modules\Core\Console\Commands\CleanupTempMedia;
use Modules\Core\Console\Commands\ClearBlockedIPs;
use Modules\Core\Console\Commands\ClearCache;
use Modules\Core\Console\Commands\ClearRateLimit;
use Modules\Core\Console\Commands\CreateAdminUser;
use Modules\Core\Console\Commands\CreateBackup;
use Modules\Core\Console\Commands\GenerateMediaThumbnails;
use Modules\Core\Console\Commands\CleanupAnalytics;

use Modules\Core\Console\Commands\SecurityAuditDependencies;
use Modules\Core\Console\Commands\SecurityCleanupLogs;
use Modules\Core\Console\Commands\SecurityKpiReport;
use Modules\Core\Console\Commands\SecurityMaintenanceCommand;
use Modules\Core\Console\Commands\SecurityRecoveryDrill;
use Modules\Core\Console\Commands\SecuritySmokeCheck;
use Modules\Core\Console\Commands\SecuritySelfHealing;
use Modules\Core\Console\Commands\SystemHealthCheck;

use Modules\Core\Console\Commands\UpdateCloudflareIps;
use Modules\Core\Console\Commands\WarmCache;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CoreServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Core';

    protected string $nameLower = 'core';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $capability) {
            return $user->hasRole('super') ? true : null;
        });

        // Register Request Macro for CSP Nonce
        Request::macro('cspNonce', function () {
            /** @var Request $this */
            if (! $this->has('__csp_nonce')) {
                $this->attributes->set('__csp_nonce', Str::random(32));
            }

            return $this->attributes->get('__csp_nonce');
        });

        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));

        // Config Bridge: Apply DB settings to runtime config
        $this->applyDatabaseSettings();
    }

    /**
     * Apply database settings to Laravel configuration.
     */
    protected function applyDatabaseSettings(): void
    {
        // Avoid errors during installation or when tables don't exist
        try {
            $argv = request()->server('argv');
            $argvString = is_array($argv) ? implode(' ', $argv) : (is_string($argv) ? $argv : '');
            
            if (! $this->app->runningInConsole() || str_contains($argvString, 'serve')) {
                if (\Illuminate\Support\Facades\Schema::hasTable('core_settings')) {
                    $enableCache = \Modules\Core\Models\Setting::get('enable_cache', true, null);
                    $cacheDriver = \Modules\Core\Models\Setting::get('cache_driver', 'database', null);
                    $cacheTtl = \Modules\Core\Models\Setting::get('cache_ttl', 3600, null);

                    if (! $enableCache) {
                        config(['cache.default' => 'array']); // Effectively disabled
                    } else {
                        config(['cache.default' => $cacheDriver]);
                    }

                    // Apply Redis credentials if driver is redis or failover
                    if (in_array($cacheDriver, ['redis', 'failover']) && \Illuminate\Support\Facades\Schema::hasTable('core_redis_settings')) {
                        $redisHost = \Modules\Core\Models\RedisSetting::getValue('redis_host', '127.0.0.1');
                        $redisPort = \Modules\Core\Models\RedisSetting::getValue('redis_port', 6379);
                        $redisPass = \Modules\Core\Models\RedisSetting::getValue('redis_password');
                        $redisUser = \Modules\Core\Models\RedisSetting::getValue('redis_username', 'default');
                        $redisDb = \Modules\Core\Models\RedisSetting::getValue('redis_database', 0);
                        $redisCacheDb = \Modules\Core\Models\RedisSetting::getValue('redis_cache_database', 1);

                        config([
                            'database.redis.default.host' => $redisHost,
                            'database.redis.default.port' => $redisPort,
                            'database.redis.default.password' => $redisPass,
                            'database.redis.default.username' => $redisUser,
                            'database.redis.default.database' => $redisDb,
                            
                            'database.redis.cache.host' => $redisHost,
                            'database.redis.cache.port' => $redisPort,
                            'database.redis.cache.password' => $redisPass,
                            'database.redis.cache.username' => $redisUser,
                            'database.redis.cache.database' => $redisCacheDb,
                        ]);
                    }

                    // Apply Session and Queue settings if enabled
                    if (\Illuminate\Support\Facades\Schema::hasTable('core_redis_settings')) {
                        $sessionEnabled = \Modules\Core\Models\RedisSetting::getValue('session_enabled', false);
                        $queueEnabled = \Modules\Core\Models\RedisSetting::getValue('queue_enabled', false);

                        if ($sessionEnabled) {
                            config(['session.driver' => 'redis']);
                            config(['session.connection' => 'cache']); // Use cache connection for sessions too
                        }

                        if ($queueEnabled) {
                            config(['queue.default' => 'redis']);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently fail if DB is not ready
        }
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton(\Modules\Core\Services\DashboardRegistry::class);
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
            AutoTuneSecurityCommand::class,
            AssignSecurityOfficer::class,
            CheckFileIntegrity::class,
            CheckThreatIntel::class,
            CleanupCspReports::class,
            CleanupOldLogs::class,
            CleanupSlowQueryLogs::class,
            CleanupTempMedia::class,
            ClearBlockedIPs::class,
            ClearCache::class,
            ClearRateLimit::class,
            CreateAdminUser::class,
            CreateBackup::class,
            GenerateMediaThumbnails::class,
            SecurityAuditDependencies::class,
            SecurityCleanupLogs::class,
            SecurityKpiReport::class,
            SecurityMaintenanceCommand::class,
            SecurityRecoveryDrill::class,
            SecuritySmokeCheck::class,
            SecuritySelfHealing::class,
            SystemHealthCheck::class,

            UpdateCloudflareIps::class,
            WarmCache::class,
            CleanupAnalytics::class,
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
