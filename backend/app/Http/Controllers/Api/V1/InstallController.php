<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class InstallController extends Controller
{
    public function getStatus()
    {
        return response()->json([
            'is_installed' => config('app.installed', false),
            'requirements' => $this->checkRequirements()
        ]);
    }

    public function install(Request $request)
    {
        if (config('app.installed')) {
            return response()->json(['message' => 'Already installed.'], 403);
        }

        $validated = $request->validate([
            'app_name' => 'required|string',
            'app_url' => 'required|url',
            'db_connection' => 'required|in:mysql,pgsql,sqlite',
            'db_host' => 'required_unless:db_connection,sqlite',
            'db_port' => 'required_unless:db_connection,sqlite',
            'db_database' => 'required',
            'db_username' => 'required_unless:db_connection,sqlite',
            'db_password' => 'nullable',
            // Add other fields as needed
        ]);

        try {
            // 1. Update Environment
            $this->updateEnv([
                'APP_NAME' => "\"{$validated['app_name']}\"",
                'APP_URL' => $validated['app_url'],
                'DB_CONNECTION' => $validated['db_connection'],
                'DB_HOST' => $validated['db_host'] ?? '',
                'DB_PORT' => $validated['db_port'] ?? '',
                'DB_DATABASE' => $validated['db_database'],
                'DB_USERNAME' => $validated['db_username'] ?? '',
                'DB_PASSWORD' => $validated['db_password'] ?? '',
                'VITE_APP_NAME' => "\"{$validated['app_name']}\"",
                'VITE_API_URL' => $validated['app_url'],
                'VITE_ROOT_DOMAIN' => parse_url($validated['app_url'], PHP_URL_HOST),
                'VITE_PORTAL_URL' => $validated['app_url'],
                'APP_ROOT_DOMAIN' => parse_url($validated['app_url'], PHP_URL_HOST),
            ]);

            // 2. Generate Key if empty
            if (empty(env('APP_KEY'))) {
                Artisan::call('key:generate', ['--force' => true]);
            }

            // 3. Run Migrations
            // Note: In web environment, this might time out if database is large.
            // For first install, it should be fine.
            Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);

            // 4. Finalize
            File::put(storage_path('installed'), 'Web installation completed at: ' . now());
            $this->updateEnv(['APP_INSTALLED' => 'true']);

            return response()->json([
                'message' => 'Installation successful!',
                'redirect_url' => url('/')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Installation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function checkRequirements()
    {
        $extensions = [
            'bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'gd', 
            'intl', 'json', 'mbstring', 'openssl', 'pdo_pgsql', 
            'tokenizer', 'xml', 'zip'
        ];

        $results = [
            'php_version' => PHP_VERSION,
            'php_supported' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'writable_env' => is_writable(base_path('.env')) || is_writable(base_path()),
            'writable_storage' => is_writable(storage_path()) && is_writable(bootstrap_path('cache')),
            'pdo_enabled' => extension_loaded('pdo_pgsql'),
        ];

        foreach ($extensions as $ext) {
            $results["ext_$ext"] = extension_loaded($ext);
        }

        return $results;
    }

    protected function updateEnv(array $data)
    {
        $path = base_path('.env');
        if (!File::exists($path)) {
            File::copy(base_path('.env.example'), $path);
        }

        $content = File::get($path);
        foreach ($data as $key => $value) {
            if (strpos($content, "{$key}=") !== false) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }
        File::put($path, $content);
    }
}
