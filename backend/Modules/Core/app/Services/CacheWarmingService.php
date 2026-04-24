<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Cms\Models\Category;
use Modules\Cms\Models\Content;
use Modules\Cms\Models\Media;
use Modules\Cms\Models\Tag;
use Modules\Core\Models\Language;

/**
 * Cache Warming Service
 * Pre-caches frequently accessed data to improve performance
 */
class CacheWarmingService
{
    /**
     * Cache key prefixes
     */
    const PREFIX_CONTENT = 'content:';

    const PREFIX_CONTENT_LIST = 'contents:list:';

    const PREFIX_CATEGORY = 'category:';

    const PREFIX_CATEGORY_LIST = 'categories:list';

    const PREFIX_TAG = 'tag:';

    const PREFIX_TAG_LIST = 'tags:list';

    const PREFIX_MEDIA = 'media:';

    const PREFIX_LANGUAGE = 'languages:active';

    const PREFIX_STATISTICS = 'statistics:';

    /**
     * Default TTL in seconds
     */
    const TTL_SHORT = 300;      // 5 minutes

    const TTL_MEDIUM = 1800;    // 30 minutes

    const TTL_LONG = 3600;      // 1 hour

    const TTL_VERY_LONG = 86400; // 24 hours

    /**
     * Warm all important caches
     *
     * @return array<string, int>
     */
    public function warmAll(): array
    {
        $results = [
            'content' => $this->warmContent(),
            'categories' => $this->warmCategories(),
            'tags' => $this->warmTags(),
            'media' => $this->warmMedia(),
            'languages' => $this->warmLanguages(),
            'statistics' => $this->warmStatistics(),
        ];

        return $results;
    }

    /**
     * Warm popular content cache
     */
    public function warmContent(int $limit = 50): int
    {
        $count = 0;

        try {
            // Cache published content list (paginated)
            $this->cacheContentList('published', 1, 20);
            $this->cacheContentList('published', 2, 20);
            $this->cacheContentList('published', 3, 20);
            $count += 3;

            // Cache most viewed content
            $popularContent = Content::where('status', 'published')
                ->orderBy('views', 'desc')
                ->limit($limit)
                ->get();

            foreach ($popularContent as $content) {
                $this->cacheContent($content);
                $count++;
            }

            // Cache recent content
            $recentContent = Content::where('status', 'published')
                ->orderBy('published_at', 'desc')
                ->limit(20)
                ->get();

            foreach ($recentContent as $content) {
                $this->cacheContent($content);
                $count++;
            }

            Log::channel('jobs')->info("Cache warmed: {$count} content items");
        } catch (\Exception $e) {
            Log::channel('jobs')->error('Failed to warm content cache: '.$e->getMessage());
        }

        return $count;
    }

    /**
     * Cache content list
     */
    private function cacheContentList(string $status, int $page, int $perPage): void
    {
        $key = self::PREFIX_CONTENT_LIST."{$status}:page:{$page}:per_page:{$perPage}";

        Cache::remember($key, self::TTL_MEDIUM, function () use ($status, $page, $perPage) {
            $query = Content::with(['author', 'category', 'tags']);

            if ($status === 'published') {
                $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
            } else {
                $query->where('status', $status);
            }

            return $query->orderBy('published_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);
        });
    }

    /**
     * Cache individual content
     */
    private function cacheContent(Content $content): void
    {
        $key = self::PREFIX_CONTENT.$content->id;

        Cache::remember($key, self::TTL_LONG, function () use ($content) {
            return $content->load(['author', 'category', 'tags', 'comments']);
        });

        // Also cache by slug
        if ($content->slug) {
            $slugKey = self::PREFIX_CONTENT."slug:{$content->slug}";
            Cache::remember($slugKey, self::TTL_LONG, function () use ($content) {
                return $content->load(['author', 'category', 'tags', 'comments']);
            });
        }
    }

    /**
     * Warm categories cache
     */
    public function warmCategories(): int
    {
        $count = 0;

        try {
            // Cache categories list
            $categories = Category::orderBy('sort_order')
                ->get();

            Cache::put(self::PREFIX_CATEGORY_LIST, $categories, self::TTL_VERY_LONG);
            $count++;

            // Cache individual categories
            foreach ($categories as $category) {
                $key = self::PREFIX_CATEGORY.$category->id;
                Cache::remember($key, self::TTL_VERY_LONG, function () use ($category) {
                    return $category->load('parent');
                });
                $count++;
            }

            // Cache category tree
            $treeKey = self::PREFIX_CATEGORY_LIST.':tree';
            Cache::remember($treeKey, self::TTL_VERY_LONG, function () {
                return Category::with('children')
                    ->whereNull('parent_id')
                    ->orderBy('sort_order')
                    ->get();
            });
            $count++;

            Log::channel('jobs')->info("Cache warmed: {$count} category items");
        } catch (\Exception $e) {
            Log::channel('jobs')->error('Failed to warm categories cache: '.$e->getMessage());
        }

        return $count;
    }

    /**
     * Warm tags cache
     */
    public function warmTags(): int
    {
        $count = 0;

        try {
            $tags = Tag::orderBy('name')
                ->get();

            Cache::put(self::PREFIX_TAG_LIST, $tags, self::TTL_VERY_LONG);
            $count++;

            // Cache popular tags
            $popularTags = Tag::withCount('contents')
                ->orderBy('contents_count', 'desc')
                ->limit(20)
                ->get();

            $popularKey = self::PREFIX_TAG_LIST.':popular';
            Cache::put($popularKey, $popularTags, self::TTL_MEDIUM);
            $count++;

            Log::channel('jobs')->info("Cache warmed: {$count} tag items");
        } catch (\Exception $e) {
            Log::channel('jobs')->error('Failed to warm tags cache: '.$e->getMessage());
        }

        return $count;
    }

    /**
     * Warm media cache
     */
    public function warmMedia(int $limit = 30): int
    {
        $count = 0;

        try {
            // Cache recent media
            $recentMedia = Media::orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            foreach ($recentMedia as $media) {
                $key = self::PREFIX_MEDIA.$media->id;
                Cache::remember($key, self::TTL_MEDIUM, function () use ($media) {
                    return $media->load(['folder', 'usages']);
                });
                $count++;
            }

            Log::channel('jobs')->info("Cache warmed: {$count} media items");
        } catch (\Exception $e) {
            Log::channel('jobs')->error('Failed to warm media cache: '.$e->getMessage());
        }

        return $count;
    }

    /**
     * Warm languages cache
     */
    public function warmLanguages(): int
    {
        $count = 0;

        try {
            $languages = Language::where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            Cache::put(self::PREFIX_LANGUAGE, $languages, self::TTL_VERY_LONG);
            $count++;

            Log::channel('jobs')->info("Cache warmed: {$count} language items");
        } catch (\Exception $e) {
            Log::channel('jobs')->error('Failed to warm languages cache: '.$e->getMessage());
        }

        return $count;
    }

    /**
     * Warm statistics cache
     */
    public function warmStatistics(): int
    {
        $count = 0;

        try {
            $stats = [
                'total_content' => Content::count(),
                'published_content' => Content::where('status', 'published')->count(),
                'draft_content' => Content::where('status', 'draft')->count(),
                'total_categories' => Category::count(),
                'total_tags' => Tag::count(),
                'total_media' => Media::count(),
                'total_users' => DB::table('users')->count(),
            ];

            Cache::put(self::PREFIX_STATISTICS.'overview', $stats, self::TTL_SHORT);
            $count++;

            Log::channel('jobs')->info("Cache warmed: {$count} statistics items");
        } catch (\Exception $e) {
            Log::channel('jobs')->error('Failed to warm statistics cache: '.$e->getMessage());
        }

        return $count;
    }

    /**
     * Warm cache for specific content type
     */
    public function warmByType(string $type, int $limit = 50): int
    {
        return match ($type) {
            'content' => $this->warmContent($limit),
            'categories' => $this->warmCategories(),
            'tags' => $this->warmTags(),
            'media' => $this->warmMedia($limit),
            'languages' => $this->warmLanguages(),
            'statistics' => $this->warmStatistics(),
            default => 0,
        };
    }

    /**
     * Get cache warming statistics
     *
     * @return array<string, int>
     */
    public function getStatistics(): array
    {
        $stats = [
            'content_cached' => $this->countCacheKeys(self::PREFIX_CONTENT),
            'categories_cached' => $this->countCacheKeys(self::PREFIX_CATEGORY),
            'tags_cached' => $this->countCacheKeys(self::PREFIX_TAG),
            'media_cached' => $this->countCacheKeys(self::PREFIX_MEDIA),
        ];

        return $stats;
    }

    /**
     * Count cache keys by prefix (Redis only)
     */
    private function countCacheKeys(string $prefix): int
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = \Illuminate\Support\Facades\Redis::connection();
                /** @var string $prefix */
                $prefix = config('cache.prefix', '');
                $pattern = $prefix.':'.$prefix.'*';
                $keys = $redis->keys($pattern);

                return count($keys);
            }
        } catch (\Exception $e) {
            Log::channel('jobs')->warning('Failed to count cache keys: '.$e->getMessage());
        }

        return 0;
    }
}
