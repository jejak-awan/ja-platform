<?php

declare(strict_types=1);

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\ActivityLog;
use Modules\Core\Models\LoginHistory;
use Modules\Core\Models\SecurityLog;
use Modules\Core\Models\Setting;
use Modules\Core\Services\SecurityMaintenanceService;

class SecurityCleanupLogs extends Command
{
    protected $signature = 'security:cleanup-logs
                            {--dry-run : Show count of deletable logs without deleting}
                            {--days= : Override retention days from settings}';

    protected $description = 'Clean up old security, activity, and login logs based on retention policy';

    public function handle(): int
    {
        // Respect maintenance mode
        $maintenance = app(SecurityMaintenanceService::class);
        if ($maintenance->isModulePaused('notifications')) {
            $this->info('Security maintenance mode active. Skipping cleanup.');

            return self::SUCCESS;
        }

        $isDryRun = (bool) $this->option('dry-run');

        // 1. Security Logs
        $this->cleanupTable(
            'Security Logs',
            SecurityLog::class,
            'security_log_retention_days',
            90,
            'created_at',
            $isDryRun,
        );

        // 2. Activity Logs
        $this->cleanupTable(
            'Activity Logs',
            ActivityLog::class,
            'activity_log_retention_days',
            90,
            'created_at',
            $isDryRun,
        );

        // 3. Login History
        $this->cleanupTable(
            'Login History',
            LoginHistory::class,
            'login_history_retention_days',
            180,
            'login_at',
            $isDryRun,
        );

        return self::SUCCESS;
    }

    /**
     * Clean up a specific log table.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     */
    private function cleanupTable(
        string $label,
        string $modelClass,
        string $settingKey,
        int $defaultDays,
        string $dateColumn,
        bool $isDryRun,
    ): void {
        $daysOption = $this->option('days');
        $settingValue = Setting::get($settingKey, $defaultDays);
        $rawDays = is_numeric($daysOption) ? $daysOption : $settingValue;
        $retentionDays = is_numeric($rawDays) ? (int) $rawDays : $defaultDays;
        $retentionDays = max(7, min(365, $retentionDays));

        $cutoff = now()->subDays($retentionDays);
        $count = $modelClass::where($dateColumn, '<', $cutoff)->count();

        if ($isDryRun) {
            $this->info("[Dry Run] {$label}: Would delete {$count} record(s) older than {$retentionDays} days.");

            return;
        }

        if ($count === 0) {
            $this->info("{$label}: No records older than {$retentionDays} days. Nothing to clean.");

            return;
        }

        $deleted = 0;
        $modelClass::where($dateColumn, '<', $cutoff)
            ->chunkById(1000, function ($logs) use (&$deleted, $modelClass) {
                $ids = $logs->pluck('id')->toArray();
                $modelClass::whereIn('id', $ids)->delete();
                $deleted += count($ids);
            });

        $this->info("{$label}: Cleaned up {$deleted} record(s) older than {$retentionDays} days.");
        Log::info("{$label} cleanup: deleted {$deleted} records older than {$retentionDays} days.");
    }
}
