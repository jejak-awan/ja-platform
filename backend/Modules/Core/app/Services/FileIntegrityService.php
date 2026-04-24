<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * File Integrity Monitoring Service.
 * Generates and verifies SHA-256 hashes of critical application files
 * to detect unauthorized modifications.
 */
class FileIntegrityService
{
    /** @var string Backup storage directory */
    private const BACKUP_PATH = 'security/integrity_snapshots';

    /** @var array<string> Critical files to monitor */
    private const MONITORED_FILES = [
        '.env',
        'artisan',
        'composer.json',
        'composer.lock',
        'package.json',
        'package-lock.json',
        'bootstrap/app.php',
        'config/app.php',
        'config/auth.php',
        'config/database.php',
        'config/sanctum.php',
        'config/session.php',
        'public/index.php',
        'public/.htaccess',
    ];

    /** @var array<string> Critical directories to monitor (all PHP files) */
    private const MONITORED_DIRS = [
        'Modules/Core/app/Http/Middleware',
        'Modules/Core/app/Services',
        'Modules/Cms/app/Http/Middleware',
        'Modules/Cms/app/Services',
    ];

    /**
     * Generate baseline hashes for all monitored files.
     *
     * @return array{created: int, updated: int, errors: int}
     */
    public function generateBaseline(): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'errors' => 0];

        $files = $this->getMonitoredFiles();

        foreach ($files as $relativePath) {
            $absolutePath = base_path($relativePath);

            if (! file_exists($absolutePath)) {
                continue;
            }

            try {
                $hash = hash_file('sha256', $absolutePath);
                if ($hash === false) {
                    $stats['errors']++;

                    continue;
                }

                $exists = DB::table('file_integrity_baselines')
                    ->where('file_path', $relativePath)
                    ->exists();

                if ($exists) {
                    DB::table('file_integrity_baselines')
                        ->where('file_path', $relativePath)
                        ->update([
                            'hash' => $hash,
                            'file_size' => filesize($absolutePath) ?: 0,
                            'status' => 'ok',
                            'checked_at' => now(),
                            'updated_at' => now(),
                        ]);
                    $stats['updated']++;
                } else {
                    DB::table('file_integrity_baselines')->insert([
                        'file_path' => $relativePath,
                        'hash' => $hash,
                        'file_size' => filesize($absolutePath) ?: 0,
                        'status' => 'ok',
                        'checked_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $stats['created']++;
                }
            } catch (\Exception $e) {
                Log::error("File integrity baseline error for {$relativePath}", ['error' => $e->getMessage()]);
                $stats['errors']++;
            }
        }

        return $stats;
    }

    /**
     * Create a secure snapshot of all monitored files.
     *
     * @return array{backed_up: int, errors: int}
     */
    public function createBackup(): array
    {
        $stats = ['backed_up' => 0, 'errors' => 0];
        $files = $this->getMonitoredFiles();

        $backupDir = storage_path(self::BACKUP_PATH);
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0750, true);
        }

        foreach ($files as $relativePath) {
            $src = base_path($relativePath);
            if (! file_exists($src)) {
                continue;
            }

            $dest = $backupDir.'/'.md5($relativePath).'.bak';

            try {
                if (copy($src, $dest)) {
                    $stats['backed_up']++;
                } else {
                    $stats['errors']++;
                }
            } catch (\Exception $e) {
                Log::error("Integrity backup failed for {$relativePath}", ['error' => $e->getMessage()]);
                $stats['errors']++;
            }
        }

        return $stats;
    }

    /**
     * Restore a specific file from backup.
     */
    public function restoreFile(string $relativePath): bool
    {
        $src = storage_path(self::BACKUP_PATH.'/'.md5($relativePath).'.bak');
        $dest = base_path($relativePath);

        if (! file_exists($src)) {
            Log::warning("Cannot restore {$relativePath}: Backup not found.");

            return false;
        }

        try {
            return copy($src, $dest);
        } catch (\Exception $e) {
            Log::error("Failed to restore {$relativePath}", ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Verify all monitored files against baselines.
     *
     * @return array{ok: int, modified: array<array{path: string, detail: string, expected: string, actual: string}>, missing: array<array{path: string, detail: string, expected: string}>, new: array<array{path: string, detail: string, actual: string}>, violations: array<array{path: string, status: string, detail: string}>}
     */
    public function verify(): array
    {
        $stats = [
            'ok' => 0,
            'modified' => [],
            'missing' => [],
            'new' => [],
            'violations' => [],
        ];

        $baselines = DB::table('file_integrity_baselines')
            ->get()
            ->keyBy('file_path');

        $currentFiles = $this->getMonitoredFiles();

        // Check each baseline entry
        foreach ($baselines as $path => $baseline) {
            $absolutePath = base_path((string) $path);

            if (! file_exists($absolutePath)) {
                $violation = [
                    'path' => (string) $path,
                    'status' => 'missing',
                    'detail' => 'File has been deleted',
                ];
                $stats['missing'][] = [
                    'path' => (string) $path,
                    'detail' => 'File has been deleted',
                    'expected' => $baseline->hash,
                ];
                $stats['violations'][] = $violation;

                DB::table('file_integrity_baselines')
                    ->where('file_path', $path)
                    ->update(['status' => 'missing', 'checked_at' => now(), 'updated_at' => now()]);

                continue;
            }

            $currentHash = hash_file('sha256', $absolutePath);

            if ($currentHash !== $baseline->hash) {
                $violation = [
                    'path' => (string) $path,
                    'status' => 'modified',
                    'detail' => "Hash mismatch: expected {$baseline->hash}, got {$currentHash}",
                ];
                $stats['modified'][] = [
                    'path' => (string) $path,
                    'detail' => 'File content has been modified',
                    'expected' => $baseline->hash,
                    'actual' => (string) $currentHash,
                ];
                $stats['violations'][] = $violation;

                DB::table('file_integrity_baselines')
                    ->where('file_path', $path)
                    ->update(['status' => 'modified', 'checked_at' => now(), 'updated_at' => now()]);
            } else {
                $stats['ok']++;

                DB::table('file_integrity_baselines')
                    ->where('file_path', $path)
                    ->update(['status' => 'ok', 'checked_at' => now(), 'updated_at' => now()]);
            }
        }

        // Check for new files not in baseline
        foreach ($currentFiles as $path) {
            if (! isset($baselines[$path])) {
                $absolutePath = base_path((string) $path);
                if (file_exists($absolutePath)) {
                    $currentHash = hash_file('sha256', $absolutePath);
                    $violation = [
                        'path' => (string) $path,
                        'status' => 'new',
                        'detail' => 'File not in baseline (new file detected)',
                    ];
                    $stats['new'][] = [
                        'path' => (string) $path,
                        'detail' => 'New file detected',
                        'actual' => (string) $currentHash,
                    ];
                    $stats['violations'][] = $violation;
                }
            }
        }

        return $stats;
    }

    /**
     * Get all files to monitor (individual files + directory scans).
     *
     * @return array<string>
     */
    private function getMonitoredFiles(): array
    {
        $files = self::MONITORED_FILES;

        foreach (self::MONITORED_DIRS as $dir) {
            $dirPath = base_path($dir);
            if (! is_dir($dirPath)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file instanceof \SplFileInfo && $file->getExtension() === 'php') {
                    $relativePath = str_replace(base_path().'/', '', $file->getPathname());
                    $files[] = $relativePath;
                }
            }
        }

        return array_unique($files);
    }
}
