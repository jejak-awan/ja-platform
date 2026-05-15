<?php

namespace Modules\Cms\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Cms\Services\SearchService;
use Modules\System\Http\Controllers\BaseApiController;
use Modules\Cms\Models\SearchQuery;

class SearchController extends BaseApiController
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'type' => 'nullable|in:post,page,category,tag',
            'types' => 'nullable|array',
            'types.*' => 'in:post,page,category,tag',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $query = is_string($request->input('q')) ? $request->input('q') : '';

        $filters = [];
        if ($request->filled('types') && is_array($request->input('types'))) {
            $filters['types'] = $request->input('types');
        } elseif ($request->has('type')) {
            $filters['types'] = [$request->input('type')];
        }
        if ($request->has('date_from')) {
            $filters['date_from'] = $request->input('date_from');
        }
        if ($request->has('date_to')) {
            $filters['date_to'] = $request->input('date_to');
        }

        $limitRaw = $request->input('limit', 20);
        $limit = is_numeric($limitRaw) ? (int) $limitRaw : 20;

        /** @var array<int, \Modules\Cms\Models\Content> $results */
        $results = $this->searchService->search($query, $filters, $limit);

        return $this->success($results, 'Search results retrieved successfully');
    }

    public function suggestions(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'limit' => 'nullable|integer|min:1|max:10',
            'types' => 'nullable|array',
            'types.*' => 'in:post,page,category,tag',
        ]);

        $query = is_string($request->input('q')) ? $request->input('q') : '';
        $limitRaw = $request->input('limit', 5);
        $limit = is_numeric($limitRaw) ? (int) $limitRaw : 5;
        $filters = [];
        if ($request->filled('types') && is_array($request->input('types'))) {
            $filters['types'] = $request->input('types');
        }

        $suggestions = $this->searchService->getSuggestions($query, $limit, $filters);

        return $this->success([
            'suggestions' => $suggestions,
        ], 'Search suggestions retrieved successfully');
    }

    public function popularQueries(Request $request): \Illuminate\Http\JsonResponse
    {
        $limitRaw = $request->input('limit', 10);
        $limit = is_numeric($limitRaw) ? (int) $limitRaw : 10;
        $daysRaw = $request->input('days', 30);
        $days = is_numeric($daysRaw) ? (int) $daysRaw : 30;

        $queries = SearchQuery::getPopularQueries($limit, $days);

        return $this->success($queries, 'Popular queries retrieved successfully');
    }

    public function noResultsQueries(Request $request): \Illuminate\Http\JsonResponse
    {
        $limitRaw = $request->input('limit', 10);
        $limit = is_numeric($limitRaw) ? (int) $limitRaw : 10;
        $daysRaw = $request->input('days', 30);
        $days = is_numeric($daysRaw) ? (int) $daysRaw : 30;

        $queries = SearchQuery::getNoResultsQueries($limit, $days);

        return $this->success($queries, 'No results queries retrieved successfully');
    }

    public function searchStats(Request $request): \Illuminate\Http\JsonResponse
    {
        $daysRaw = $request->input('days', 30);
        $days = is_numeric($daysRaw) ? (int) $daysRaw : 30;

        $stats = [
            'total_searches' => SearchQuery::where('searched_at', '>=', now()->subDays($days))->count(),
            'unique_queries' => SearchQuery::where('searched_at', '>=', now()->subDays($days))
                ->distinct('query')
                ->count('query'),
            'avg_results' => (float) SearchQuery::where('searched_at', '>=', now()->subDays($days))
                ->avg('results_count'),
            'zero_result_searches' => SearchQuery::where('searched_at', '>=', now()->subDays($days))
                ->where('results_count', 0)
                ->count(),
            'popular_queries' => SearchQuery::getPopularQueries(10, $days),
        ];

        return $this->success($stats, 'Search statistics retrieved successfully');
    }

    public function reindex(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */

        // Only allow admins to reindex
        if (! $user || ! $user->can('manage settings')) {
            return $this->forbidden('Unauthorized');
        }

        $result = $this->searchService->reindexAll();

        return $this->success([
            'indexed' => $result,
        ], 'Search index rebuilt successfully');
    }
}
