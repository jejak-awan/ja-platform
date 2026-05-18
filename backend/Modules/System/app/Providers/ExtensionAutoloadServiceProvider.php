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
        $activeExtensions = Extension::where('status', 'active')->get();

        if ($activeExtensions->isEmpty()) {
            return;
        }

        $loader = new ClassLoader;

        foreach ($activeExtensions as $ext) {
            // Class namespace formatting: e.g. "Extensions\WhatsAppGateway\"
            $namespace = 'Extensions\\'.str_replace(' ', '', ucwords(str_replace('-', ' ', $ext->slug))).'\\';

            // Physical path of the extension src folder (e.g. Modules/School/app or Plugins/wa-gateway/src)
            $path = $ext->type === 'module'
                ? base_path('Modules/'.str_replace(' ', '', ucwords(str_replace('-', ' ', $ext->slug))).'/app')
                : base_path('Plugins/'.$ext->slug.'/src');

            if (is_dir($path)) {
                $loader->addPsr4($namespace, $path);
            }
        }

        $loader->register();
    }
}
