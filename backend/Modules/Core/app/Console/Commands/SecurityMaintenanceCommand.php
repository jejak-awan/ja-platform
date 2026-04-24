<?php

declare(strict_types=1);

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Services\SecurityMaintenanceService;

class SecurityMaintenanceCommand extends Command
{
    protected $signature = 'security:maintenance
        {action : start, stop, or status}
        {--modules=all : Comma-separated module list or "all"}
        {--duration=60 : Duration in minutes (max 240)}';

    protected $description = 'Manage Security Maintenance Mode (pause security modules during deployments)';

    public function handle(SecurityMaintenanceService $service): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'start' => $this->startMaintenance($service),
            'stop' => $this->stopMaintenance($service),
            'status' => $this->showStatus($service),
            default => $this->invalidAction(),
        };
    }

    private function startMaintenance(SecurityMaintenanceService $service): int
    {
        $modulesInput = $this->option('modules');
        $modules = is_string($modulesInput) ? explode(',', $modulesInput) : ['all'];
        $modules = array_map('trim', $modules);

        $durationInput = $this->option('duration');
        $duration = is_numeric($durationInput) ? (int) $durationInput : 60;

        $this->info('🔧 Activating Security Maintenance Mode...');
        $result = $service->activate($modules, $duration);

        if (! $result['success']) {
            $this->error($result['message'] ?? 'Failed to activate maintenance mode.');

            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('✅ Maintenance mode ACTIVATED');
        $this->table(
            ['Property', 'Value'],
            [
                ['Modules Paused', implode(', ', (array) ($result['modules'] ?? []))],
                ['Duration', (string) ($result['duration'] ?? 0).' minutes'],
                ['Auto-Expires At', (string) ($result['expires_at'] ?? 'N/A')],
            ]
        );

        $this->newLine();
        $this->warn('⚠️  Security features listed above are now PAUSED.');
        $this->warn('   Run "php artisan security:maintenance stop" when done.');

        return Command::SUCCESS;
    }

    private function stopMaintenance(SecurityMaintenanceService $service): int
    {
        $this->info('🔧 Deactivating Security Maintenance Mode...');
        $result = $service->deactivate();

        if (! $result['success']) {
            $this->warn($result['message'] ?? 'Maintenance mode is not active.');

            return Command::SUCCESS;
        }

        $this->newLine();
        $this->info('✅ Maintenance mode DEACTIVATED');
        $this->info('📝 '.($result['post_actions'] ?? 'Post-maintenance actions completed.'));

        return Command::SUCCESS;
    }

    private function showStatus(SecurityMaintenanceService $service): int
    {
        $status = $service->getStatus();

        if (! $status['active']) {
            $this->info('ℹ️  Security Maintenance Mode is NOT active. All modules are running.');

            return Command::SUCCESS;
        }

        $remainingMinutes = intdiv($status['remaining_seconds'], 60);
        $remainingSeconds = $status['remaining_seconds'] % 60;

        $this->newLine();
        $this->warn('⚠️  Security Maintenance Mode is ACTIVE');
        $this->table(
            ['Property', 'Value'],
            [
                ['Paused Modules', implode(', ', $status['modules'])],
                ['Started At', $status['started_at'] ?? 'N/A'],
                ['Expires At', $status['expires_at'] ?? 'N/A'],
                ['Remaining', "{$remainingMinutes}m {$remainingSeconds}s"],
            ]
        );

        return Command::SUCCESS;
    }

    private function invalidAction(): int
    {
        $this->error('Invalid action. Use: start, stop, or status');

        return Command::FAILURE;
    }
}
