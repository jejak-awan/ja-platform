<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\Backup;
use Modules\Core\Services\BackupService;
use Modules\Core\Services\SecurityAlertService;

class SecurityRecoveryDrill extends Command
{
    protected $signature = 'security:recovery-drill
        {--create-backup : Create a fresh database backup before drill}
        {--max-rpo-minutes=1440 : Maximum allowed RPO in minutes (default 24h)}
        {--max-rto-seconds=600 : Maximum allowed RTO in seconds (default 10m)}';

    protected $description = 'Run measurable recovery drill and produce RTO/RPO report';

    public function handle(BackupService $backupService, SecurityAlertService $alertService): int
    {
        $startedAt = now();
        $startedHr = hrtime(true);
        $this->info('Running security recovery drill...');

        $createdBackup = null;
        $backupDurationSeconds = null;

        if ((bool) $this->option('create-backup')) {
            $this->line('Step 1/3: creating fresh backup...');
            $backupStart = hrtime(true);
            $createdBackup = $backupService->createDatabaseBackup('recovery-drill-'.$startedAt->format('Ymd-His'));
            $backupDurationSeconds = round((hrtime(true) - $backupStart) / 1_000_000_000, 3);

            if (! $createdBackup->isCompleted()) {
                $this->error('Backup step failed: '.($createdBackup->error_message ?? 'unknown'));

                return self::FAILURE;
            }

            $this->info('Backup completed in '.$backupDurationSeconds.'s');
        }

        $this->line('Step 2/3: validating route/permission hardening...');
        $smokeStart = hrtime(true);
        $smokeExit = Artisan::call('security:smoke-check');
        $smokeOutput = trim(Artisan::output());
        $smokeDurationSeconds = round((hrtime(true) - $smokeStart) / 1_000_000_000, 3);

        if ($smokeExit !== 0) {
            $this->error('security:smoke-check failed');
            $this->line($smokeOutput);

            return self::FAILURE;
        }

        $this->line('Step 3/3: validating alert engine responsiveness...');
        $alertStart = hrtime(true);
        $alerts = $alertService->getAlerts();
        $alertCount = count($alerts);
        $alertDurationSeconds = round((hrtime(true) - $alertStart) / 1_000_000_000, 3);

        /** @var Backup|null $latestBackup */
        $latestBackup = Backup::query()
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();

        $rpoMinutes = $latestBackup?->completed_at ? $latestBackup->completed_at->diffInMinutes(now()) : null;
        $rtoSeconds = round((hrtime(true) - $startedHr) / 1_000_000_000, 3);

        $maxRpoMinutes = max(1, (int) $this->option('max-rpo-minutes'));
        $maxRtoSeconds = max(1, (int) $this->option('max-rto-seconds'));
        $rpoPass = $rpoMinutes !== null && $rpoMinutes <= $maxRpoMinutes;
        $rtoPass = $rtoSeconds <= $maxRtoSeconds;
        $overallPass = $rtoPass && $rpoPass;

        $report = [
            'started_at' => $startedAt->toISOString(),
            'completed_at' => now()->toISOString(),
            'environment' => app()->environment(),
            'targets' => [
                'max_rto_seconds' => $maxRtoSeconds,
                'max_rpo_minutes' => $maxRpoMinutes,
            ],
            'results' => [
                'observed_rto_seconds' => $rtoSeconds,
                'observed_rpo_minutes' => $rpoMinutes,
                'rto_pass' => $rtoPass,
                'rpo_pass' => $rpoPass,
                'overall_pass' => $overallPass,
            ],
            'steps' => [
                'backup' => [
                    'requested' => (bool) $this->option('create-backup'),
                    'duration_seconds' => $backupDurationSeconds,
                    'created_backup_id' => $createdBackup?->id,
                    'created_backup_name' => $createdBackup?->name,
                ],
                'smoke_check' => [
                    'exit_code' => $smokeExit,
                    'duration_seconds' => $smokeDurationSeconds,
                ],
                'alerts_engine' => [
                    'duration_seconds' => $alertDurationSeconds,
                    'alerts_count' => $alertCount,
                ],
            ],
            'latest_backup' => $latestBackup ? [
                'id' => $latestBackup->id,
                'name' => $latestBackup->name,
                'completed_at' => $latestBackup->completed_at?->toISOString(),
                'size' => $latestBackup->size,
                'size_human' => $latestBackup->size_human,
            ] : null,
        ];

        $reportPath = 'security/recovery-drills/'.$startedAt->format('Ymd_His').'.json';
        $payload = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        Storage::disk('local')->put($reportPath, is_string($payload) ? $payload : '{}');

        $this->newLine();
        $this->info('Observed RTO: '.$rtoSeconds.'s (target <= '.$maxRtoSeconds.'s)');
        $this->info('Observed RPO: '.($rpoMinutes !== null ? $rpoMinutes.'m' : 'N/A').' (target <= '.$maxRpoMinutes.'m)');
        $this->info('Report: storage/app/'.$reportPath);

        if (! $overallPass) {
            $this->warn('Recovery drill completed with threshold violations.');

            return self::FAILURE;
        }

        $this->info('Recovery drill passed.');

        return self::SUCCESS;
    }
}
