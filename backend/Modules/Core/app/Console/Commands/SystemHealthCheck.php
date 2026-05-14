<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SystemHealthCheck extends Command
{
    protected $signature = 'core:health-check';

    protected $description = 'Check system health status';

    public function handle(): int
    {
        $this->info('Running system health check...');
        $this->newLine();

        $issues = [];
        $warnings = [];

        // Check database connection
        try {
            DB::connection()->getPdo();
            $this->info('✅ Database connection: OK');
        } catch (\Exception $e) {
            $issues[] = 'Database connection failed';
            $this->error('❌ Database connection: FAILED');
        }

        // Check storage
        try {
            Storage::disk('public')->put('health-check.txt', 'test');
            Storage::disk('public')->delete('health-check.txt');
            $this->info('✅ Storage (public): OK');
        } catch (\Exception $e) {
            $issues[] = 'Storage (public) not writable';
            $this->error('❌ Storage (public): FAILED');
        }

        // Check cache
        try {
            \Cache::put('health-check', 'test', 1);
            \Cache::get('health-check');
            \Cache::forget('health-check');
            $this->info('✅ Cache: OK');
        } catch (\Exception $e) {
            $warnings[] = 'Cache not working properly';
            $this->warn('⚠️  Cache: WARNING');
        }

        // Check disk space
        $freeSpace = disk_free_space(base_path());
        $totalSpace = disk_total_space(base_path());
        $usedPercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;

        if ($usedPercent > 90) {
            $issues[] = 'Disk space critical: '.round($usedPercent, 2).'% used';
            $this->error('❌ Disk space: CRITICAL ('.round($usedPercent, 2).'% used)');
        } elseif ($usedPercent > 80) {
            $warnings[] = 'Disk space high: '.round($usedPercent, 2).'% used';
            $this->warn('⚠️  Disk space: WARNING ('.round($usedPercent, 2).'% used)');
        } else {
            $this->info('✅ Disk space: OK ('.round($usedPercent, 2).'% used)');
        }

        $this->newLine();

        if (count($issues) > 0) {
            $this->error('Issues found: '.count($issues));
            foreach ($issues as $issue) {
                $this->error("  - {$issue}");
            }

            return 1;
        }

        if (count($warnings) > 0) {
            $this->warn('Warnings: '.count($warnings));
            foreach ($warnings as $warning) {
                $this->warn("  - {$warning}");
            }

            return 0;
        }

        $this->info('✅ All checks passed!');

        return 0;
    }
}
