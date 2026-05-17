<?php

declare(strict_types=1);

namespace Modules\Media\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Media\Contracts\MediaServiceInterface;
use Modules\Media\Services\MediaService;
use Modules\Media\Models\File;
use Modules\Media\Policies\FilePolicy;
use Illuminate\Support\Facades\Gate;

class MediaServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton(MediaServiceInterface::class, MediaService::class);
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        Gate::policy(File::class, FilePolicy::class);
        $this->registerConfig();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Media\Console\MigrateLegacyMedia::class,
                \Modules\Media\Console\Commands\CleanupTempMedia::class,
            ]);
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/config.php' => config_path('media.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/config.php', 'media'
        );
    }
}
