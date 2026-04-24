<?php

namespace Modules\Cms\Services;

use Modules\Core\Models\SearchIndex;
use Modules\Core\Models\SearchQuery;

class SearchService
{
    /**
     * Search the index
     *
     * @param  string  $query
     * @param  array<string, mixed>  $filters
     * @param  int  $limit
     * @return array{results: \Illuminate\Support\Collection<int, array<string, mixed>>, total: int, query: string, suggestions: array<int, array{text: string, type: string, url?: string|null}>, is_loose?: bool}
     */
    public function search($query, $filters = [], $limit = 20): array
    {
        if (empty(trim($query))) {
            return [
                'results' => collect(),
                'total' => 0,
                'query' => (string) $query,
                'suggestions' => [],
            ];
        }

        $searchQuery = SearchIndex::query();
        $isLoose = false;
        $suggestions = [];

        // 1. Try Strict Search (AND)
        $this->applySearchLogic($searchQuery, $query, true);
        $this->applyFilters($searchQuery, $filters);

        $results = $searchQuery->orderByDesc('relevance_score')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        // 2. Fallback to Loose Search (OR) if no results
        if ($results->isEmpty()) {
            $isLoose = true;
            $searchQuery = SearchIndex::query();
            $this->applySearchLogic($searchQuery, $query, false);
            $this->applyFilters($searchQuery, $filters);

            $results = $searchQuery->orderByDesc('relevance_score')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            // 3. If still empty, get suggestions
            if ($results->isEmpty()) {
                $suggestions = $this->getSuggestions($query, 5, $filters);
            }
        }

        // Log search query
        SearchQuery::log($query, $results->count(), $filters);

        return [
            'results' => $results->map(function ($index) {
                /** @var SearchIndex $index */
                /** @var array<string, mixed> $mapped */
                $mapped = [
                    'id' => $index->id,
                    'type' => (string) $index->type,
                    'title' => (string) $index->title,
                    'excerpt' => is_scalar($index->excerpt) ? (string) $index->excerpt : null,
                    'url' => is_scalar($index->url) ? (string) $index->url : null,
                    'searchable_type' => (string) $index->searchable_type,
                    'searchable_id' => (int) $index->searchable_id,
                    'relevance_score' => $index->getAttribute('relevance_score'),
                ];

                return $mapped;
            }),
            'total' => $results->count(),
            'query' => (string) $query,
            'is_loose' => $isLoose,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<SearchIndex>  $queryBuilder
     * @param  string  $query
     */
    protected function applySearchLogic($queryBuilder, $query, bool $strict = true): void
    {
        if (config('database.default') === 'mysql' || config('database.default') === 'mariadb') {
            $prepared = $this->prepareSearchQuery($query, $strict);
            if ($prepared) {
                $queryBuilder->whereRaw(
                    'MATCH(title, content) AGAINST(? IN BOOLEAN MODE)',
                    [$prepared]
                );
            }
        } else {
            // Fallback for SQLite/PostgreSQL (use ILIKE for PostgreSQL case-insensitivity)
            $driver = config('database.default');
            $operator = $driver === 'pgsql' ? 'ILIKE' : 'like';
            
            // For PostgreSQL, if it's not strict (loose search), apply fuzzy wildcard
            if ($driver === 'pgsql' && !$strict) {
                $chars = str_split(preg_replace('/[^a-zA-Z0-9]/', '', $query));
                $fuzzyQuery = '%' . implode('%', $chars) . '%';
                
                $queryBuilder->where(function ($q) use ($fuzzyQuery, $operator) {
                    $q->where('title', $operator, $fuzzyQuery)
                        ->orWhere('content', $operator, $fuzzyQuery);
                });
                return;
            }

            $terms = explode(' ', $query);
            $queryBuilder->where(function ($q) use ($terms, $strict, $operator) {
                foreach ($terms as $term) {
                    if ($strict) {
                        $q->where(function ($sub) use ($term, $operator) {
                            $sub->where('title', $operator, "%{$term}%")
                                ->orWhere('content', $operator, "%{$term}%");
                        });
                    } else {
                        $q->orWhere('title', $operator, "%{$term}%")
                            ->orWhere('content', $operator, "%{$term}%");
                    }
                }
            });
        }
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<SearchIndex>  $queryBuilder
     * @param  array<string, mixed>  $filters
     */
    protected function applyFilters($queryBuilder, $filters): void
    {
        if (isset($filters['types']) && is_array($filters['types'])) {
            $types = array_values(array_filter($filters['types'], fn ($type) => is_string($type) && $type !== ''));
            if (! empty($types)) {
                $queryBuilder->whereIn('type', $types);
            }
        } elseif (isset($filters['type']) && is_string($filters['type'])) {
            $queryBuilder->where('type', $filters['type']);
        }
        if (isset($filters['date_from']) && is_string($filters['date_from'])) {
            $queryBuilder->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to']) && is_string($filters['date_to'])) {
            $queryBuilder->whereDate('created_at', '<=', $filters['date_to']);
        }
    }

    /**
     * Search by specific type
     *
     * @param  string  $query
     * @param  string  $type
     * @return array{results: \Illuminate\Support\Collection<int, array<string, mixed>>, total: int, query: string, suggestions: array<int, array{text: string, type: string}>, is_loose?: bool}
     */
    public function searchByType($query, $type, int $limit = 20): array
    {
        return $this->search((string) $query, ['type' => $type], $limit);
    }

    /**
     * Get search suggestions
     *
     * @param  string  $query
     * @param  int  $limit
     * @param  array<string, mixed>  $filters
     * @return array<int, array{text: string, type: string, url: string|null}>
     */
    public function getSuggestions($query, $limit = 5, array $filters = []): array
    {
        if (empty(trim($query))) {
            return [];
        }

        $queryClean = trim($query);
        $driver = config('database.default');

        // 1. Primary: Case-Insensitive search (ILIKE for PostgreSQL, LIKE for MySQL)
        if ($driver === 'pgsql') {
            // PostgreSQL: Use ILIKE (native case-insensitive)
            $suggestionQuery = SearchIndex::query();
            $suggestionQuery->where(function ($q) use ($queryClean) {
                $q->where('title', 'ILIKE', "%{$queryClean}%")
                    ->orWhere('content', 'ILIKE', "%{$queryClean}%");
            });
            $this->applyFilters($suggestionQuery, $filters);
            $suggestions = $suggestionQuery
                ->select('title', 'type', 'url')
                ->distinct()
                ->limit($limit)
                ->get();
        } else {
            // MySQL/MariaDB: Use LOWER + LIKE
            $queryLower = mb_strtolower($queryClean, 'UTF-8');
            $suggestionQuery = SearchIndex::query();
            $suggestionQuery->where(function ($q) use ($queryLower) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$queryLower}%"])
                    ->orWhereRaw('LOWER(content) LIKE ?', ["%{$queryLower}%"]);
            });
            $this->applyFilters($suggestionQuery, $filters);
            $suggestions = $suggestionQuery
                ->select('title', 'type', 'url')
                ->distinct()
                ->limit($limit)
                ->get();
        }

        // 2. Fuzzy/Typo Tolerance fallback (Aggressive Fuzzy Wildcard)
        if ($suggestions->isEmpty()) {
            if ($driver === 'pgsql') {
                // PostgreSQL: Ultra-loose matching (e.g., "lorm" -> "%l%o%r%m%")
                $chars = str_split(preg_replace('/[^a-zA-Z0-9]/', '', $queryClean));
                $fuzzyQuery = '%' . implode('%', $chars) . '%';
                
                if (count($chars) >= 2) {
                    $suggestionQuery = SearchIndex::query();
                    $suggestionQuery->where(function ($q) use ($fuzzyQuery) {
                        $q->where('title', 'ILIKE', $fuzzyQuery)
                            ->orWhere('content', 'ILIKE', $fuzzyQuery);
                    });
                    $this->applyFilters($suggestionQuery, $filters);
                    $suggestions = $suggestionQuery
                        ->select('title', 'type', 'url')
                        ->limit($limit)
                        ->get();
                }
            } else {
                // MySQL: SOUNDEX fallback
                $suggestionQuery = SearchIndex::query();
                $suggestionQuery->whereRaw('SOUNDEX(title) = SOUNDEX(?)', [$queryClean]);
                $this->applyFilters($suggestionQuery, $filters);
                $suggestions = $suggestionQuery
                    ->select('title', 'type', 'url')
                    ->distinct()
                    ->limit($limit)
                    ->get();
            }
        }

        /** @var array<int, array{text: string, type: string, url: string|null}> $result */
        $result = $suggestions->map(function ($index) {
            /** @var SearchIndex $index */
            return [
                'text' => (string) $index->title,
                'type' => (string) $index->type,
                'url' => is_scalar($index->url) ? (string) $index->url : null,
            ];
        })->toArray();

        return $result;
    }

    /**
     * Prepare query for MySQL FullText
     *
     * @param  string  $query
     */
    protected function prepareSearchQuery($query, bool $strict = true): string
    {
        // Prepare query for MySQL FULLTEXT search
        $terms = explode(' ', trim($query));
        $prepared = [];

        foreach ($terms as $term) {
            $term = trim($term);
            if (strlen($term) >= 2) {
                // Strict: +term* (must contain term)
                // Loose: term* (optional)
                $prefix = $strict ? '+' : '';
                $prepared[] = "{$prefix}{$term}*";
            }
        }

        return implode(' ', $prepared);
    }

    /**
     * Reindex all searchable items
     *
     * @return array{contents: int, categories: int, tags: int}
     */
    public function reindexAll(): array
    {
        // Reindex all content
        $contents = \Modules\Cms\Models\Content::where('status', 'published')->get();
        foreach ($contents as $content) {
            /** @var \Modules\Cms\Models\Content $content */
            SearchIndex::index($content, [
                'title' => $content->title,
                'content' => trim(strip_tags(($content->intro ?? '').' '.($content->body ?? ''))),
                'excerpt' => $content->excerpt,
                'url' => $content->type === 'page'
                    ? url('/'.$content->slug)
                    : url('/blog/'.$content->slug),
                'type' => $content->type,
            ]);
        }

        // Reindex categories
        $categories = \Modules\Cms\Models\Category::where('is_active', true)->get();
        foreach ($categories as $category) {
            /** @var \Modules\Cms\Models\Category $category */
            SearchIndex::index($category, [
                'title' => $category->name,
                'content' => $category->description ?? '',
                'url' => url('/category/'.$category->slug),
                'type' => 'category',
            ]);
        }

        // Reindex tags
        $tags = \Modules\Cms\Models\Tag::all();
        foreach ($tags as $tag) {
            SearchIndex::index($tag, [
                'title' => $tag->name,
                'content' => $tag->description ?? '',
                'url' => url('/tag/'.$tag->slug),
                'type' => 'tag',
            ]);
        }

        return [
            'contents' => $contents->count(),
            'categories' => $categories->count(),
            'tags' => $tags->count(),
        ];
    }
}
