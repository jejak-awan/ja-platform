<?php

declare(strict_types=1);

namespace Modules\System\Services;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SysMaintenanceService
{
    /**
     * Clean all temporary and orphan files across sandboxes and upload directories.
     *
     * @return array{cleaned_bytes: int, files_removed: int}
     */
    public function cleanJunk(): array
    {
        $cleanedBytes = 0;
        $filesRemoved = 0;

        // 1. Clean temporary ZIP uploads and scaffold outputs
        $tempPaths = [
            storage_path('app/uploads'),
            storage_path('app/scaffolds'),
            storage_path('framework/views'),
        ];

        foreach ($tempPaths as $path) {
            if (File::isDirectory($path)) {
                $files = File::allFiles($path);
                foreach ($files as $file) {
                    // Do not delete gitignore files
                    if ($file->getFilename() === '.gitignore') {
                        continue;
                    }

                    $size = $file->getSize();
                    try {
                        File::delete($file->getRealPath());
                        $cleanedBytes += $size;
                        $filesRemoved++;
                    } catch (Exception) {
                        // Suppress failures on locked files
                    }
                }
            }
        }

        // 2. Clean temporary/expired cache files inside extension sandboxes
        $sandboxBase = storage_path('app/extensions');
        if (File::isDirectory($sandboxBase)) {
            $extensionDirs = File::directories($sandboxBase);
            foreach ($extensionDirs as $extDir) {
                $cachePath = $extDir.'/sandbox/cache';
                if (File::isDirectory($cachePath)) {
                    $files = File::allFiles($cachePath);
                    foreach ($files as $file) {
                        $size = $file->getSize();
                        try {
                            File::delete($file->getRealPath());
                            $cleanedBytes += $size;
                            $filesRemoved++;
                        } catch (Exception) {
                        }
                    }
                }
            }
        }

        Log::info('[SysMaintenance] Junk cleaner routine completed.', [
            'files_removed' => $filesRemoved,
            'bytes_freed' => $cleanedBytes,
        ]);

        return [
            'cleaned_bytes' => $cleanedBytes,
            'files_removed' => $filesRemoved,
        ];
    }

    /**
     * Optimize database tables and clear orphan dynamic records.
     *
     * @return array{success: bool, optimized_tables: int, purged_orphans: int}
     */
    public function optimizeDatabase(): array
    {
        $purgedOrphans = 0;
        $optimizedTablesCount = 0;

        // 1. Purge orphan dynamic CCK records (records whose ContentType no longer exists)
        if (Schema::hasTable('sys_content_types') && Schema::hasTable('sys_dynamic_records')) {
            $activeTypeIds = DB::table('sys_content_types')->pluck('id')->toArray();
            if (! empty($activeTypeIds)) {
                $purgedOrphans = DB::table('sys_dynamic_records')
                    ->whereNotIn('content_type_id', $activeTypeIds)
                    ->delete();
            } else {
                $purgedOrphans = DB::table('sys_dynamic_records')->delete();
            }
        }

        // 2. Optimize DB tables (Vacuum for SQLite, Optimize for MySQL)
        try {
            $driver = DB::connection()->getDriverName();
            if ($driver === 'sqlite') {
                DB::statement('VACUUM');
                $optimizedTablesCount = 1;
            } elseif ($driver === 'mysql') {
                $tables = ['sys_content_types', 'sys_dynamic_records', 'users', 'settings', 'media'];
                foreach ($tables as $table) {
                    if (Schema::hasTable($table)) {
                        DB::statement("OPTIMIZE TABLE {$table}");
                        $optimizedTablesCount++;
                    }
                }
            }
        } catch (Exception $e) {
            Log::warning('[SysMaintenance] Database table optimization threw an exception: '.$e->getMessage());
        }

        Log::info('[SysMaintenance] Database optimizer routine completed.', [
            'purged_orphans' => $purgedOrphans,
            'optimized_tables' => $optimizedTablesCount,
        ]);

        return [
            'success' => true,
            'optimized_tables' => $optimizedTablesCount,
            'purged_orphans' => $purgedOrphans,
        ];
    }

    /**
     * Run in-process framework optimization warm-ups.
     *
     * @return array{success: bool}
     */
    public function boostPerformance(): array
    {
        try {
            // Clear framework compilation caches to resolve segmentations
            Artisan::call('optimize:clear');

            // Warm-up configuration caches
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
        } catch (Exception $e) {
            Log::warning('[SysMaintenance] Framework boost compilation failed: '.$e->getMessage());
        }

        Log::info('[SysMaintenance] Performance boost caches generated.');

        return [
            'success' => true,
        ];
    }

    /**
     * Reset the entire system back to pristine clean factory default state.
     *
     * @return array{success: bool, message: string}
     */
    public function factoryReset(): array
    {
        // 1. Wipe all local custom Plugins physical directory
        $pluginsDir = base_path('Plugins');
        if (File::isDirectory($pluginsDir)) {
            $subDirs = File::directories($pluginsDir);
            foreach ($subDirs as $subDir) {
                File::deleteDirectory($subDir);
            }
        }

        // 2. Wipe all VFS Sandboxes files
        $sandboxBase = storage_path('app/extensions');
        if (File::isDirectory($sandboxBase)) {
            File::deleteDirectory($sandboxBase);
            File::makeDirectory($sandboxBase, 0755, true);
        }

        // 3. Clear temporary files
        $this->cleanJunk();

        // 4. Wipe DB migrations fresh and re-seed pristine admin records
        Artisan::call('migrate:fresh', ['--force' => true]);

        // Check if standard seeder is available
        if (class_exists('Database\\Seeders\\DatabaseSeeder')) {
            Artisan::call('db:seed', ['--force' => true]);
        }

        Log::critical('[SysMaintenance] Platform has been restored to factory default pristine state.');

        return [
            'success' => true,
            'message' => 'System successfully restored to default factory clean state.',
        ];
    }
}
