<?php

declare(strict_types=1);

namespace Modules\System\Providers;

use Composer\Autoload\ClassLoader;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\System\Models\Extension;

class ExtensionAutoloadServiceProvider extends ServiceProvider
{
    /**
     * List of cached active extensions loaded in current request lifecycle.
     *
     * @var array<int, array{slug: string, type: string}>|null
     */
    protected ?array $activeExtensions = null;

    /**
     * Register services.
     */
    public function register(): void
    {
        // Fail-safe check to prevent breaking Artisan CLI during early boot or installation phase
        try {
            if (! app()->runningInConsole() || Schema::hasTable('sys_extensions')) {
                $this->autoloadActiveExtensions();
                $this->registerActiveExtensionProviders();
                $this->registerActivePluginRoutes();
            }
        } catch (\Throwable $e) {
            // Fail silently to keep application bootable during migrations or schema setups
        }
    }

    /**
     * Get or fetch active extensions.
     *
     * @return array<int, array{slug: string, type: string}>
     */
    protected function getActiveExtensions(): array
    {
        if ($this->activeExtensions !== null) {
            return $this->activeExtensions;
        }

        $cacheFile = storage_path('framework/cache/active_extensions.json');

        if (file_exists($cacheFile)) {
            $content = @file_get_contents($cacheFile);
            if ($content !== false) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    /** @var array<int, array{slug: string, type: string}> $decoded */
                    $this->activeExtensions = $decoded;
                }
            }
        }

        if ($this->activeExtensions === null) {
            try {
                /** @var array<int, array{slug: string, type: string}> $extensions */
                $extensions = Extension::where('status', 'active')
                    ->get(['slug', 'type'])
                    ->toArray();

                $this->activeExtensions = $extensions;
                @file_put_contents($cacheFile, json_encode($this->activeExtensions));
            } catch (\Throwable $e) {
                $this->activeExtensions = [];
            }
        }

        return $this->activeExtensions;
    }

    /**
     * Autoload namespaces for all active modules/plugins dynamically.
     */
    protected function autoloadActiveExtensions(): void
    {
        $activeExtensions = $this->getActiveExtensions();

        if (empty($activeExtensions)) {
            return;
        }

        $loader = new ClassLoader;

        foreach ($activeExtensions as $ext) {
            $slug = $ext['slug'];
            $type = $ext['type'];

            // Class namespace formatting: e.g. "Extensions\WhatsAppGateway\"
            $namespace = 'Extensions\\'.str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))).'\\';

            // Physical path of the extension src folder (e.g. Modules/School/app or Plugins/wa-gateway/src)
            $path = $type === 'module'
                ? base_path('Modules/'.str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))).'/app')
                : base_path('Plugins/'.$slug.'/src');

            if (is_dir($path)) {
                $loader->addPsr4($namespace, $path);
            }
        }

        $loader->register();
    }

    /**
     * Register service providers for active plugins dynamically.
     */
    protected function registerActiveExtensionProviders(): void
    {
        $activeExtensions = $this->getActiveExtensions();

        if (empty($activeExtensions)) {
            return;
        }

        foreach ($activeExtensions as $ext) {
            // Static local modules are already registered in bootstrap/providers.php.
            // Dynamic plugins under Plugins/ need dynamic ServiceProvider booting.
            if (($ext['type'] ?? '') !== 'plugin') {
                continue;
            }

            $slug = $ext['slug'];
            $studlyName = str_replace(' ', '', ucwords(str_replace('-', ' ', $slug)));

            // Expected ServiceProvider pattern: Extensions\TelegramAlerts\TelegramAlertsServiceProvider
            $providerClass = "Extensions\\{$studlyName}\\{$studlyName}ServiceProvider";

            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
            }
        }
    }

    /**
     * Map static route contributions for all active plugins.
     */
    protected function registerActivePluginRoutes(): void
    {
        $activeExtensions = $this->getActiveExtensions();
        if (empty($activeExtensions)) {
            return;
        }

        foreach ($activeExtensions as $ext) {
            if (($ext['type'] ?? '') !== 'plugin') {
                continue;
            }

            $slug = $ext['slug'];
            $manifestPath = base_path("Plugins/{$slug}/manifest.json");

            if (file_exists($manifestPath)) {
                $content = @file_get_contents($manifestPath);
                if ($content) {
                    $manifest = json_decode($content, true);
                    if (is_array($manifest)) {
                        $contributionPoints = $manifest['contribution_points'] ?? null;
                        $contributes = $manifest['contributes'] ?? null;

                        $routes = null;
                        if (is_array($contributionPoints)) {
                            $routes = $contributionPoints['routes'] ?? null;
                        }
                        if (! is_array($routes) && is_array($contributes)) {
                            $routes = $contributes['routes'] ?? null;
                        }

                        if (is_array($routes)) {
                            foreach ($routes as $route) {
                                if (is_array($route)) {
                                    $methodVal = $route['method'] ?? null;
                                    $uriVal = $route['uri'] ?? null;
                                    $actionVal = $route['action'] ?? null;

                                    $method = strtoupper(is_scalar($methodVal) ? (string) $methodVal : 'GET');
                                    $uri = is_scalar($uriVal) ? (string) $uriVal : '';
                                    $action = is_scalar($actionVal) ? (string) $actionVal : '';

                                    if ($uri !== '' && $action !== '') {
                                        $uriClean = ltrim($uri, '/');
                                        if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'])) {
                                            \Illuminate\Support\Facades\Route::match([$method], $uriClean, $action);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
