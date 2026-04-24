<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Services\FileIntegrityService;
use Modules\Core\Services\SecurityNotificationService;

class SecuritySelfHealing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:self-healing 
                            {--dry-run : Only audit and report without restoring}
                            {--backup : Create a new baseline backup of current files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit file integrity and automatically restore tampered files from secure backup';

    /**
     * Execute the console command.
     */
    public function handle(
        FileIntegrityService $integrityService,
        SecurityNotificationService $notifier
    ): int {
        if ($this->option('backup')) {
            $this->info('Creating new integrity backup snapshot...');
            $stats = $integrityService->createBackup();
            $this->success("Backup completed: {$stats['backed_up']} files secured, {$stats['errors']} errors.");

            return 0;
        }

        $this->info('Starting file integrity self-healing audit...');
        $results = $integrityService->verify();

        $violations = array_merge($results['modified'], $results['missing']);

        if (empty($violations)) {
            $this->info('All monitored files are legit. No healing required.');

            return 0;
        }

        $this->warn('Unauthorized file modifications detected!');

        foreach ($violations as $v) {
            $path = $v['path'];
            $type = isset($v['actual']) ? 'Modified' : 'Missing';

            $this->line("- [{$type}] {$path}");

            if ($this->option('dry-run')) {
                continue;
            }

            $this->info("Restoring {$path} from backup...");
            if ($integrityService->restoreFile($path)) {
                $this->success("Successfully restored {$path}");

                // Alert via notifications
                $notifier->critical( // Changed from $notificationService->sendCriticalAlert
                    'security_integrity',
                    'Self-Healing: Restored tampered file',
                    "File `{$path}` was found to be {$type} and has been automatically restored from backup snapshot.",
                    ['file_path' => $path, 'violation_type' => $type] // Added context
                );
            } else {
                $this->error("Failed to restore {$path}. Manual intervention required!");

                $notifier->critical( // Changed from $notificationService->sendCriticalAlert
                    'security_integrity',
                    'Self-Healing FAILED',
                    "Security system failed to restore tampered file: `{$path}`. Immediate manual audit required.",
                    ['file_path' => $path, 'violation_type' => $type] // Added context
                );
            }
        }

        if (! $this->option('dry-run')) {
            $this->info('Self-healing process completed.');
            // Regenerate baseline after restoration to sync DB state
            $integrityService->generateBaseline();
        }

        return 0;
    }

    protected function success(string $message): void // Added type hint for $message and return type
    {
        $this->info("✅ {$message}"); // Changed output format
    }
}
