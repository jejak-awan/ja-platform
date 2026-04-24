<?php

declare(strict_types=1);

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Services\FileIntegrityService;
use Modules\Core\Services\SecurityNotificationService;

class CheckFileIntegrity extends Command
{
    protected $signature = 'security:check-integrity {--baseline : Generate new baseline instead of verifying}';

    protected $description = 'Check file integrity against stored baselines or generate new baseline';

    public function handle(FileIntegrityService $service, SecurityNotificationService $notifier): int
    {
        if ($this->option('baseline')) {
            $this->info('Generating file integrity baseline...');
            $stats = $service->generateBaseline();
            $this->info("Baseline generated: {$stats['created']} created, {$stats['updated']} updated, {$stats['errors']} errors");

            return Command::SUCCESS;
        }

        // Skip verification during maintenance mode (baseline generation still allowed)
        $maintenance = app(\Modules\Core\Services\SecurityMaintenanceService::class);
        if ($maintenance->isModulePaused('integrity')) {
            $this->info('⏸️  Skipping file integrity check — Security Maintenance Mode active.');

            return Command::SUCCESS;
        }

        $this->info('Verifying file integrity...');
        $result = $service->verify();

        $this->info("Results: {$result['ok']} OK, ".count($result['modified']).' modified, '.count($result['missing']).' missing, '.count($result['new']).' new files');

        if (! empty($result['violations'])) {
            $this->warn('⚠️ Integrity violations detected:');

            foreach ($result['violations'] as $violation) {
                $this->error("  [{$violation['status']}] {$violation['path']} — {$violation['detail']}");
            }

            // Send alert
            $violationCount = count($result['violations']);
            $paths = array_map(fn ($v) => $v['path'], $result['violations']);
            $notifier->critical(
                'file_integrity_violation',
                "File Integrity Alert: {$violationCount} violation(s)",
                "Modified/missing files detected:\n".implode("\n", $paths),
                [
                    'modified' => $result['modified'],
                    'missing' => $result['missing'],
                    'new_files' => $result['new'],
                ]
            );

            return Command::FAILURE;
        }

        $this->info('✅ All files passed integrity check.');

        return Command::SUCCESS;
    }
}
