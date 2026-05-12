<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Services\CacheService;

class ClearCache extends Command
{
    protected $signature = 'core:clear-cache {--type=all : Type of cache to clear (all, media, tag, user)}';

    protected $description = 'Clear system caches';

    public function handle(): int
    {
        $cacheService = new CacheService;
        /** @var string $type */
        $type = $this->option('type') ?? 'all';

        switch ($type) {
            case 'media':
                $cacheService->clearMediaCaches();
                $this->info('Media caches cleared');
                break;
            case 'tag':
                $cacheService->clearTagCaches();
                $this->info('Tag caches cleared');
                break;
            case 'user':
                $cacheService->clearUserCaches();
                $this->info('User caches cleared');
                break;
            case 'all':
            default:
                $cacheService->clearAll();
                $this->info('All system and module caches cleared');
                break;
        }

        return 0;
    }
}
