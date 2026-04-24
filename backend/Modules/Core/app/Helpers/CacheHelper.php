<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Cache Helper Class
 * Provides convenient methods for caching operations including
 * content, media, categories, statistics, and API responses.
 * Supports tagged cache for grouped invalidation (Redis/Memcached only).
 * Falls back to regular cache for other drivers.
 */
class CacheHelper
{
    /**
     * Cache key prefixes
     */
    const PREFIX_CONTENT = 'content:';

    const PREFIX_MEDIA = 'media:';

    const PREFIX_CATEGORY = 'category:';

    const PREFIX_TAG = 'tag:';

    const PREFIX_USER = 'user:';

    const PREFIX_SETTINGS = 'settings:';

    const PREFIX_STATISTICS = 'statistics:';

    const PREFIX_API = 'api:';

    /**
     * Default cache TTL (Time To Live) in seconds
     */
    const TTL_SHORT = 300;      // 5 minutes

    const TTL_MEDIUM = 1800;    // 30 minutes

    const TTL_LONG = 3600;      // 1 hour

    const TTL_VERY_LONG = 86400; // 24 hours

    /**
     * Check if current cache driver supports tags
     */
    protected static function supportsTagging(): bool
    {
        $driver = config('cache.default');

        return in_array($driver, ['redis', 'memcached', 'redis_failover']);
    }

    /**
     * Get cache key with prefix
     */
    public static function key(string $prefix, string $key): string
    {
        return $prefix.$key;
    }

    /**
     * Cache API response
     */
    public static function rememberApiResponse(string $key, int $ttl, \Closure $callback): mixed
    {
        $cacheKey = self::key(self::PREFIX_API, $key);

        return Cache::remember($cacheKey, $ttl, function () use ($callback): mixed {
            try {
                return $callback();
            } catch (\Exception $e) {
                Log::channel('jobs')->error('Cache callback error: '.$e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Cache content data
     */
    public static function rememberContent(int $id, int $ttl, \Closure $callback): mixed
    {
        $cacheKey = self::key(self::PREFIX_CONTENT, (string) $id);

        if (self::supportsTagging()) {
            /** @var mixed $result */
            $result = Cache::tags(['content'])->remember($cacheKey, $ttl, function () use ($callback): mixed {
                return $callback();
            });

            return $result;
        }

        /** @var mixed $result */
        $result = Cache::remember($cacheKey, $ttl, function () use ($callback): mixed {
            return $callback();
        });

        return $result;
    }

    /**
     * Cache media data
     */
    public static function rememberMedia(int $id, int $ttl, \Closure $callback): mixed
    {
        $cacheKey = self::key(self::PREFIX_MEDIA, (string) $id);

        if (self::supportsTagging()) {
            /** @var mixed $result */
            $result = Cache::tags(['media'])->remember($cacheKey, $ttl, function () use ($callback): mixed {
                return $callback();
            });

            return $result;
        }

        /** @var mixed $result */
        $result = Cache::remember($cacheKey, $ttl, function () use ($callback): mixed {
            return $callback();
        });

        return $result;
    }

    /**
     * Cache category data
     */
    public static function rememberCategory(int $id, int $ttl, \Closure $callback): mixed
    {
        $cacheKey = self::key(self::PREFIX_CATEGORY, (string) $id);

        if (self::supportsTagging()) {
            /** @var mixed $result */
            $result = Cache::tags(['categories'])->remember($cacheKey, $ttl, function () use ($callback): mixed {
                return $callback();
            });

            return $result;
        }

        /** @var mixed $result */
        $result = Cache::remember($cacheKey, $ttl, function () use ($callback): mixed {
            return $callback();
        });

        return $result;
    }

    /**
     * Cache statistics
     */
    public static function rememberStatistics(string $type, int $ttl, \Closure $callback): mixed
    {
        $cacheKey = self::key(self::PREFIX_STATISTICS, $type);

        /** @var mixed $result */
        $result = Cache::remember($cacheKey, $ttl, function () use ($callback): mixed {
            return $callback();
        });

        return $result;
    }

    /**
     * Invalidate content cache
     */
    public static function invalidateContent(int $id): void
    {
        $cacheKey = self::key(self::PREFIX_CONTENT, (string) $id);
        Cache::forget($cacheKey);

        if (self::supportsTagging()) {
            try {
                Cache::tags(['content'])->flush();
            } catch (\Exception $e) {
                Log::channel('jobs')->warning('Tagged cache flush failed: '.$e->getMessage());
            }
        }
    }

    /**
     * Invalidate media cache
     */
    public static function invalidateMedia(int $id): void
    {
        $cacheKey = self::key(self::PREFIX_MEDIA, (string) $id);
        Cache::forget($cacheKey);

        if (self::supportsTagging()) {
            try {
                Cache::tags(['media'])->flush();
            } catch (\Exception $e) {
                Log::channel('jobs')->warning('Tagged cache flush failed: '.$e->getMessage());
            }
        }
    }

    /**
     * Invalidate category cache
     */
    public static function invalidateCategory(int $id): void
    {
        $cacheKey = self::key(self::PREFIX_CATEGORY, (string) $id);
        Cache::forget($cacheKey);

        if (self::supportsTagging()) {
            try {
                Cache::tags(['categories'])->flush();
            } catch (\Exception $e) {
                Log::channel('jobs')->warning('Tagged cache flush failed: '.$e->getMessage());
            }
        }
    }

    /**
     * Invalidate all content-related cache
     */
    public static function invalidateAllContent(): void
    {
        if (self::supportsTagging()) {
            try {
                Cache::tags(['content'])->flush();

                return;
            } catch (\Exception $e) {
                Log::channel('jobs')->warning('Tagged cache flush failed: '.$e->getMessage());
            }
        }

        // Fallback: clear prefixed keys (less efficient)
        // For non-tagging drivers, we just log a warning
        Log::channel('jobs')->info('Content cache invalidation requested (non-tagging driver)');
    }

    /**
     * Invalidate all media-related cache
     */
    public static function invalidateAllMedia(): void
    {
        if (self::supportsTagging()) {
            try {
                Cache::tags(['media'])->flush();

                return;
            } catch (\Exception $e) {
                Log::channel('jobs')->warning('Tagged cache flush failed: '.$e->getMessage());
            }
        }

        Log::channel('jobs')->info('Media cache invalidation requested (non-tagging driver)');
    }

    /**
     * Invalidate all category-related cache
     */
    public static function invalidateAllCategories(): void
    {
        if (self::supportsTagging()) {
            try {
                Cache::tags(['categories'])->flush();

                return;
            } catch (\Exception $e) {
                Log::channel('jobs')->warning('Tagged cache flush failed: '.$e->getMessage());
            }
        }

        Log::channel('jobs')->info('Category cache invalidation requested (non-tagging driver)');
    }

    /**
     * Invalidate statistics cache
     */
    public static function invalidateStatistics(?string $type = null): void
    {
        if ($type) {
            $cacheKey = self::key(self::PREFIX_STATISTICS, $type);
            Cache::forget($cacheKey);
        } else {
            // Clear common statistics keys instead of full flush
            $statKeys = ['dashboard', 'content', 'users', 'analytics'];
            foreach ($statKeys as $key) {
                Cache::forget(self::key(self::PREFIX_STATISTICS, $key));
            }
        }
    }

    /**
     * Invalidate API response cache
     */
    public static function invalidateApiResponse(string $key): void
    {
        $cacheKey = self::key(self::PREFIX_API, $key);
        Cache::forget($cacheKey);
    }

    /**
     * Clear all cache
     */
    public static function clearAll(): void
    {
        Cache::flush();
    }

    /**
     * Check if cache is available
     */
    public static function isAvailable(): bool
    {
        try {
            Cache::put('cache_test', 'test', 1);
            $result = Cache::get('cache_test') === 'test';
            Cache::forget('cache_test');

            return $result;
        } catch (\Exception $e) {
            Log::channel('jobs')->warning('Cache not available: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get cache statistics (if supported)
     *
     * @return array{driver: string|null, available: bool, supports_tagging: bool}
     */
    public static function getStats(): array
    {
        /** @var string|null $driver */
        $driver = config('cache.default');

        return [
            'driver' => $driver,
            'available' => self::isAvailable(),
            'supports_tagging' => self::supportsTagging(),
        ];
    }
}
