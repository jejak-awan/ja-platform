<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanupTempMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:cleanup-temp {--hours=24 : The number of hours to keep temp files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up temporary media files older than specified hours';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = $this->option('hours');
        // Ensure hours is an integer
        $hours = is_numeric($hours) ? (int) $hours : 24;

        $this->info("Cleaning up temporary files older than {$hours} hours...");

        $tempPath = storage_path('app/temp');

        if (! File::isDirectory($tempPath)) {
            $this->info('Temp directory does not exist. Nothing to cleanup.');

            return 0;
        }

        $files = File::files($tempPath);
        $count = 0;
        $now = now();

        foreach ($files as $file) {
            $lastModified = $file->getMTime();
            // Convert timestamp to Carbon
            $lastModifiedDate = \Illuminate\Support\Carbon::createFromTimestamp($lastModified);

            if ($lastModifiedDate->diffInHours($now) >= $hours) {
                File::delete($file->getRealPath());
                $count++;
            }
        }

        $this->info("Deleted {$count} temporary files.");

        return 0;
    }
}
