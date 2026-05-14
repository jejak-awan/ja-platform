<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Services\BackupService;

class CreateBackup extends Command
{
    protected $signature = 'core:backup {--type=database : Type of backup (database, files, full)}';

    protected $description = 'Create a backup of the system';

    public function handle(BackupService $backupService): int
    {
        /** @var string $type */
        $type = $this->option('type') ?? 'database';

        $this->info("Creating {$type} backup...");

        $backup = $backupService->createDatabaseBackup();

        if ($backup->isCompleted()) {
            $this->info("Backup created successfully: {$backup->name}");
            $this->info("Size: {$backup->size_human}");
            $this->info("Path: {$backup->path}");
        } else {
            $this->error("Backup failed: {$backup->error_message}");

            return 1;
        }

        return 0;
    }
}
