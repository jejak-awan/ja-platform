<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Services\CacheWarmingService;

class WarmCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm
                            {--type= : Specific type to warm (content, categories, tags, media, languages, statistics)}
                            {--limit=50 : Limit for content/media warming}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up application cache with frequently accessed data';

    /**
     * Execute the console command.
     */
    public function handle(CacheWarmingService $warmingService): int
    {
        $this->info('Starting cache warming...');

        $type = (string) $this->option('type');
        $limit = (int) $this->option('limit');

        $startTime = microtime(true);

        if ($type) {
            $this->info("Warming cache for type: {$type}");
            $count = $warmingService->warmByType($type, $limit);
            $this->info("✓ Cached {$count} items for {$type}");
        } else {
            $this->info('Warming all caches...');
            $results = $warmingService->warmAll();

            $this->newLine();
            $this->info('Cache warming completed:');
            foreach ($results as $type => $count) {
                $this->line("  • {$type}: {$count} items");
            }
        }

        $duration = round(microtime(true) - $startTime, 2);
        $this->newLine();
        $this->info("Cache warming completed in {$duration} seconds");

        return Command::SUCCESS;
    }
}
