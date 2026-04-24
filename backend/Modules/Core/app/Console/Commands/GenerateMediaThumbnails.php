<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\Cms\Http\Controllers\Api\MediaController;
use Modules\Cms\Models\Media;

class GenerateMediaThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:generate-thumbnails {--force : Force regenerate existing thumbnails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate thumbnails for all existing media images';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating thumbnails for media images...');

        $mediaController = new MediaController;
        $force = (bool) $this->option('force');

        // Get all image media
        $mediaList = Media::where('mime_type', 'like', 'image/%')->get();

        $bar = $this->output->createProgressBar($mediaList->count());
        $bar->start();

        $successCount = 0;
        $skipCount = 0;
        $errorCount = 0;

        foreach ($mediaList as $media) {
            try {
                // Check if thumbnail already exists
                if (! $force) {
                    $fileName = pathinfo($media->path, PATHINFO_FILENAME);
                    $extension = pathinfo($media->path, PATHINFO_EXTENSION);
                    $thumbnailPath = 'media/thumbnails/'.$fileName.'_thumb.'.$extension;

                    if (Storage::disk($media->disk)->exists($thumbnailPath)) {
                        $skipCount++;
                        $bar->advance();

                        continue;
                    }
                }

                // Use reflection to call protected method
                $reflection = new \ReflectionClass($mediaController);
                $method = $reflection->getMethod('generateThumbnailForMedia');
                $method->setAccessible(true);

                $result = $method->invoke($mediaController, $media);

                if ($result) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to generate thumbnail for media ID {$media->id}: ".$e->getMessage());
                $errorCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Success: {$successCount}");
        if ($skipCount > 0) {
            $this->comment("⏭️  Skipped (already exists): {$skipCount}");
        }
        if ($errorCount > 0) {
            $this->error("❌ Failed: {$errorCount}");
        }

        return 0;
    }
}
