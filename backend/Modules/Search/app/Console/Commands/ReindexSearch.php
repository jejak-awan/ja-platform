<?php

namespace Modules\Search\Console\Commands;

use Illuminate\Console\Command;
use Modules\Search\Services\SearchService;
use Modules\Search\Models\SearchIndex;

class ReindexSearch extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'search:reindex {--clear : Clear the search index table before reindexing}';

    /**
     * The console command description.
     */
    protected $description = 'Rebuild the full search database index for all contents, categories, and tags';

    /**
     * Execute the console command.
     */
    public function handle(SearchService $searchService): int
    {
        $this->info('Starting Search Index Rebuild...');

        if ($this->option('clear')) {
            $this->warn('Clearing existing search index...');
            SearchIndex::query()->delete();
            $this->info('Search index cleared successfully.');
        }

        // 1. Reindex Contents
        $this->line('Indexing Published CMS Contents...');
        $contentsCount = \Modules\Cms\Models\Content::where('status', 'published')->count();
        $this->info("Found {$contentsCount} published contents to index.");
        
        // 2. Reindex Categories
        $this->line('Indexing Active Categories...');
        $categoriesCount = \Modules\Library\Models\Category::where('is_active', true)->count();
        $this->info("Found {$categoriesCount} active categories to index.");

        // 3. Reindex Tags
        $this->line('Indexing Tags...');
        $tagsCount = \Modules\Library\Models\Tag::count();
        $this->info("Found {$tagsCount} tags to index.");

        $this->newLine();
        $this->info('Reindexing all items via SearchService...');
        
        $stats = $searchService->reindexAll();

        $this->newLine();
        $this->info('Search Index Rebuilt Successfully!');
        $this->table(
            ['Resource Type', 'Indexed Count'],
            [
                ['CMS Contents', $stats['cms_contents'] ?? 0],
                ['Library Categories', $stats['cms_categories'] ?? 0],
                ['Library Tags', $stats['cms_tags'] ?? 0],
                ['System Pages & Features', $stats['system_pages'] ?? 0],
            ]
        );

        return 0;
    }
}
