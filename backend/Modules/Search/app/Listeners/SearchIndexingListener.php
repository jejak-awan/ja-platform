<?php

namespace Modules\Search\Listeners;

use Modules\Search\Services\SearchService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SearchIndexingListener implements ShouldQueue
{
    public function __construct(
        protected SearchService $searchService
    ) {}

    public function handle($event): void
    {
        // Handle Eloquent events
        if (isset($event->model)) {
            $this->searchService->sync($event->model);
        } elseif (is_object($event) && method_exists($event, 'model')) {
            $this->searchService->sync($event->model());
        }
    }

    /**
     * Handle model saved/updated/deleted events
     */
    public function onModelSaved($event): void
    {
        $this->searchService->sync($event);
    }

    public function onModelDeleted($event): void
    {
        // Handle deletion if needed, though sync() should handle non-published/inactive
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events): void
    {
        $events->listen(
            'eloquent.saved: Modules\Cms\Models\Content',
            [self::class, 'onModelSaved']
        );
        $events->listen(
            'eloquent.saved: Modules\Library\Models\Category',
            [self::class, 'onModelSaved']
        );
        $events->listen(
            'eloquent.saved: Modules\Library\Models\Tag',
            [self::class, 'onModelSaved']
        );
    }
}
