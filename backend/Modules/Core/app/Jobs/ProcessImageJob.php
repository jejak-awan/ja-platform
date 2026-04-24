<?php

namespace Modules\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Cms\Models\Media;
use Modules\Core\Models\Setting;

class ProcessImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300; // 5 minutes

    /** @var array<int, int> */
    public array $backoff = [60, 120, 300]; // Retry after 1min, 2min, 5min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $mediaId,
        public string $action = 'thumbnail', // thumbnail, resize, optimize
        public ?int $width = null,
        public ?int $height = null,
        public ?int $quality = 85
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $media = Media::findOrFail($this->mediaId);
            $mimeType = (string) $media->mime_type;

            if (! str_starts_with($mimeType, 'image/')) {
                Log::channel('media')->warning("ProcessImageJob: Media {$this->mediaId} is not an image");

                return;
            }

            switch ($this->action) {
                case 'thumbnail':
                    $this->generateThumbnail($media);
                    break;
                case 'resize':
                    $this->resizeImage($media);
                    break;
                case 'optimize':
                    $this->optimizeImage($media);
                    break;
                default:
                    Log::channel('media')->warning("ProcessImageJob: Unknown action {$this->action}");
            }
        } catch (\Exception $e) {
            Log::channel('media')->error('ProcessImageJob failed: '.$e->getMessage(), [
                'media_id' => $this->mediaId,
                'action' => $this->action,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e; // Re-throw to trigger retry
        }
    }

    protected function generateThumbnail(Media $media): void
    {
        $fullPath = Storage::disk($media->disk)->path($media->path);
        $width = $this->width ?? 300;
        $height = $this->height ?? 300;

        // Create thumbnails directory
        $thumbnailDir = Storage::disk($media->disk)->path('media/thumbnails');
        if (! is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        // Generate thumbnail filename
        $fileName = pathinfo($media->path, PATHINFO_FILENAME);
        $extension = pathinfo($media->path, PATHINFO_EXTENSION);

        // For SVG files, convert to PNG for thumbnail
        $isSvg = $media->mime_type === 'image/svg+xml' || strtolower($extension) === 'svg';
        $thumbnailExtension = $isSvg ? 'png' : $extension;
        $thumbnailFileName = $fileName.'_thumb.'.$thumbnailExtension;
        $thumbnailPath = 'media/thumbnails/'.$thumbnailFileName;
        $thumbnailFullPath = Storage::disk($media->disk)->path($thumbnailPath);

        // Handle SVG files - convert to PNG using Imagick (if available)
        if ($isSvg && extension_loaded('imagick') && class_exists('Imagick')) {
            try {
                $imagick = new \Imagick;
                $imagick->setBackgroundColor(new \ImagickPixel('transparent'));
                $imagick->readImage($fullPath);
                $imagick->setImageFormat('png');

                // Resize SVG to thumbnail size
                $imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1, true);

                // Save as PNG
                $imagick->writeImage($thumbnailFullPath);
                $imagick->clear();
                $imagick->destroy();

                Log::channel('media')->info("ProcessImageJob: SVG thumbnail generated for media {$media->id}");

                return;
            } catch (\Exception $e) {
                Log::channel('media')->warning('ProcessImageJob: SVG thumbnail generation failed with Imagick: '.$e->getMessage());
                // Fall through to try Intervention Image
            }
        }

        if (class_exists(\Intervention\Image\ImageManager::class)) {
            $driver = null;
            if (extension_loaded('gd')) {
                $driver = new \Intervention\Image\Drivers\Gd\Driver;
            } elseif (extension_loaded('imagick')) {
                $driver = new \Intervention\Image\Drivers\Imagick\Driver;
            }

            if ($driver) {
                try {
                    $manager = new \Intervention\Image\ImageManager($driver);
                    $image = $manager->read($fullPath);
                    $image->cover($width, $height);

                    // For SVG converted to PNG, save as PNG
                    if ($isSvg) {
                        $image->toPng()->save($thumbnailFullPath);
                    } else {
                        $image->save($thumbnailFullPath, quality: $this->quality ?? 85);
                    }

                    Log::channel('media')->info("ProcessImageJob: Thumbnail generated for media {$media->id}");
                } catch (\Exception $e) {
                    Log::channel('media')->warning('ProcessImageJob: Thumbnail generation failed: '.$e->getMessage());
                }
            }
        }
    }

    protected function resizeImage(Media $media): void
    {
        if (! $this->width && ! $this->height) {
            Log::channel('media')->warning('ProcessImageJob: Resize requires width or height');

            return;
        }

        $fullPath = Storage::disk($media->disk)->path($media->path);

        if (class_exists(\Intervention\Image\ImageManager::class)) {
            $driver = null;
            if (extension_loaded('gd')) {
                $driver = new \Intervention\Image\Drivers\Gd\Driver;
            } elseif (extension_loaded('imagick')) {
                $driver = new \Intervention\Image\Drivers\Imagick\Driver;
            }

            if ($driver) {
                $manager = new \Intervention\Image\ImageManager($driver);
                $image = $manager->read($fullPath);

                if ($this->width && $this->height) {
                    $image->cover($this->width, $this->height);
                } elseif ($this->width) {
                    $image->scale(width: $this->width);
                } elseif ($this->height) {
                    $image->scale(height: $this->height);
                }

                $image->save($fullPath, quality: $this->quality ?? 85);

                // Update file size
                $media->update(['size' => filesize($fullPath)]);

                Log::channel('media')->info("ProcessImageJob: Image resized for media {$media->id}");
            }
        }
    }

    protected function optimizeImage(Media $media): void
    {
        $fullPath = Storage::disk($media->disk)->path($media->path);

        if (class_exists(\Intervention\Image\ImageManager::class)) {
            $driver = null;
            if (extension_loaded('gd')) {
                $driver = new \Intervention\Image\Drivers\Gd\Driver;
            } elseif (extension_loaded('imagick')) {
                $driver = new \Intervention\Image\Drivers\Imagick\Driver;
            }

            if ($driver) {
                $manager = new \Intervention\Image\ImageManager($driver);
                $image = $manager->read($fullPath);

                $autoConvert = filter_var(Setting::get('media_auto_convert_webp', true), FILTER_VALIDATE_BOOLEAN);
                $qualityConfig = Setting::get('media_optimization_quality', 85);
                $quality = $this->quality ?? (is_numeric($qualityConfig) ? intval($qualityConfig) : 85);
                $maxWidthConfig = Setting::get('media_max_width', 1920);
                $maxWidth = is_numeric($maxWidthConfig) ? intval($maxWidthConfig) : 1920;

                // Resize if too large
                if ($image->width() > $maxWidth) {
                    $image->scale(width: $maxWidth);
                }

                if ($autoConvert && $media->mime_type !== 'image/webp') {
                    $pathInfo = pathinfo((string) $media->path);
                    $newFileName = $pathInfo['filename'].'.webp';
                    $dirname = strval($pathInfo['dirname'] ?? '.');
                    $newPath = ($dirname !== '.' ? $dirname.'/' : '').$newFileName;
                    $newFullPath = Storage::disk($media->disk)->path($newPath);

                    $image->toWebp($quality)->save($newFullPath);

                    if ($fullPath !== $newFullPath) {
                        unlink($fullPath);
                    }

                    $media->update([
                        'file_name' => $newFileName,
                        'path' => $newPath,
                        'mime_type' => 'image/webp',
                        'size' => filesize($newFullPath),
                    ]);
                } else {
                    $image->save($fullPath, quality: $quality);
                    $media->update(['size' => filesize($fullPath)]);
                }

                Log::channel('media')->info("ProcessImageJob: Image optimized for media {$media->id}");
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('media')->error('ProcessImageJob permanently failed', [
            'media_id' => $this->mediaId,
            'action' => $this->action,
            'error' => $exception->getMessage(),
        ]);
    }
}
