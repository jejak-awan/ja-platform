<?php

namespace Modules\Media\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Media\Contracts\MediaServiceInterface;
use Modules\Media\Services\MediaService;

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
        $this->registerConfig();
        // Migrations are automatically loaded by the module package

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Media\Console\MigrateLegacyMedia::class,
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
