<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ja:install {--force : Force the installation even if already installed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install JA-Platform (Backend & Frontend) seamlessly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting JA-Platform Installation...');

        if (!$this->checkRequirements()) {
            return 1;
        }

        $this->setupEnvironment();
        $this->setupDatabase();
        $this->setupFrontend();

        $this->info('✅ JA-Platform has been installed successfully!');
        return 0;
    }

    protected function checkRequirements()
    {
        $this->comment('🔍 Checking system requirements...');

        // Check PHP Version
        if (version_compare(PHP_VERSION, '8.2.0', '<')) {
            $this->error('❌ PHP 8.2 or higher is required.');
            return false;
        }

        // Check for Node
        $nodeCheck = $this->runExternalCommand('node -v');
        if (!$nodeCheck) {
            $this->error('❌ Node.js is not installed.');
            return false;
        }

        // Check for NPM
        $npmCheck = $this->runExternalCommand('npm -v');
        if (!$npmCheck) {
            $this->error('❌ NPM is not installed.');
            return false;
        }

        $this->info('✅ System requirements met.');
        return true;
    }

    protected function setupEnvironment()
    {
        $this->comment('📝 Setting up environment variables...');

        if (!File::exists(base_path('.env'))) {
            File::copy(base_path('.env.example'), base_path('.env'));
            $this->info('✅ Created .env from .example');
        }

        $appName = $this->ask('Application Name', config('app.name', 'JA-Platform'));
        $appUrl = $this->ask('Application URL', config('app.url', 'http://localhost'));
        $rootDomain = $this->ask('Root Domain (for multi-tenancy)', env('VITE_ROOT_DOMAIN', 'localhost'));

        $this->updateEnv([
            'APP_NAME' => "\"$appName\"",
            'APP_URL' => $appUrl,
            'VITE_APP_NAME' => "\"$appName\"",
            'VITE_API_URL' => $appUrl,
            'VITE_ROOT_DOMAIN' => $rootDomain,
            'VITE_PORTAL_URL' => $appUrl,
            'APP_ROOT_DOMAIN' => $rootDomain,
        ]);

        $this->call('key:generate');
    }

    protected function setupDatabase()
    {
        $this->comment('🗄️ Setting up database...');

        $host = $this->ask('Database Host', env('DB_HOST', '127.0.0.1'));
        $port = $this->ask('Database Port', env('DB_PORT', '3306'));
        $database = $this->ask('Database Name', env('DB_DATABASE', 'ja_apps'));
        $username = $this->ask('Database Username', env('DB_USERNAME', 'root'));
        $password = $this->secret('Database Password');

        $this->updateEnv([
            'DB_HOST' => $host,
            'DB_PORT' => $port,
            'DB_DATABASE' => $database,
            'DB_USERNAME' => $username,
            'DB_PASSWORD' => $password,
        ]);

        // Refresh config
        config(['database.connections.mysql.host' => $host]);
        config(['database.connections.mysql.port' => $port]);
        config(['database.connections.mysql.database' => $database]);
        config(['database.connections.mysql.username' => $username]);
        config(['database.connections.mysql.password' => $password]);

        if ($this->confirm('Do you want to run migrations and seeders?', true)) {
            $this->call('migrate:fresh', ['--seed' => true]);
        }
    }

    protected function setupFrontend()
    {
        $this->comment('🌐 Setting up frontend assets...');

        if ($this->confirm('Do you want to install frontend dependencies (npm install)?', true)) {
            $this->info('📦 Running npm install in frontend directory...');
            $this->runExternalCommand('npm install', base_path('../frontend'));
        }

        if ($this->confirm('Do you want to build frontend assets?', true)) {
            $this->info('🏗️ Building assets...');
            $this->runExternalCommand('npm run build', base_path('../frontend'));
            $this->info('🚚 Syncing assets to public...');
            $this->runExternalCommand('npm run deploy:assets:full', base_path('..'));
        }
    }

    protected function updateEnv(array $data)
    {
        $path = base_path('.env');

        if (File::exists($path)) {
            foreach ($data as $key => $value) {
                // Ensure the value is string
                $value = (string) $value;
                
                if (strpos(File::get($path), "{$key}=") !== false) {
                    // Check if value contains spaces, if so wrap in quotes if not already
                    if (strpos($value, ' ') !== false && strpos($value, '"') === false) {
                        $value = "\"$value\"";
                    }
                    File::put($path, preg_replace(
                        "/^{$key}=.*/m",
                        "{$key}={$value}",
                        File::get($path)
                    ));
                } else {
                    File::append($path, "\n{$key}={$value}");
                }
            }
        }
    }

    protected function runExternalCommand($command, $cwd = null)
    {
        $process = Process::fromShellCommandline($command, $cwd);
        $process->setTimeout(null);
        
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process->isSuccessful();
    }
}
