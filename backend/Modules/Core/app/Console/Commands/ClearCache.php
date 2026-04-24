<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Services\CacheService;

class ClearCache extends Command
{
    protected $signature = 'cms:clear-cache {--type=all : Type of cache to clear (all, content, category, media, seo)}';

    protected $description = 'Clear CMS caches';

    public function handle(): int
    {
        $cacheService = new CacheService;
        /** @var string $type */
        $type = $this->option('type') ?? 'all';

        switch ($type) {
            case 'content':
                $cacheService->clearContentCaches();
                $this->info('Content caches cleared');
                break;
            case 'category':
                $cacheService->clearCategoryCaches();
                $this->info('Category caches cleared');
                break;
            case 'media':
                $cacheService->clearMediaCaches();
                $this->info('Media caches cleared');
                break;
            case 'seo':
                $cacheService->clearSeoCaches();
                $this->info('SEO caches cleared');
                break;
            case 'all':
            default:
                $cacheService->clearAll();
                $this->info('All caches cleared');
                break;
        }

        return 0;
    }
}
