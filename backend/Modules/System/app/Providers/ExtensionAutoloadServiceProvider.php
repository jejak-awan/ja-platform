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
     * Register services.
     */
    public function register(): void
    {
        // Fail-safe check to prevent breaking Artisan CLI during early boot or installation phase
        try {
            if (! app()->runningInConsole() || Schema::hasTable('sys_extensions')) {
                $this->autoloadActiveExtensions();
            }
        } catch (\Throwable $e) {
            // Fail silently to keep application bootable during migrations or schema setups
        }
    }

    /**
     * Autoload namespaces for all active modules/plugins dynamically.
     */
    protected function autoloadActiveExtensions(): void
    {
        $cacheFile = storage_path('framework/cache/active_extensions.json');

        if (file_exists($cacheFile)) {
            $activeExtensions = json_decode(file_get_contents($cacheFile), true);
        } else {
            try {
                $activeExtensions = Extension::where('status', 'active')
                    ->get(['slug', 'type'])
                    ->toArray();

                @file_put_contents($cacheFile, json_encode($activeExtensions));
            } catch (\Throwable $e) {
                return;
            }
        }

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
}
