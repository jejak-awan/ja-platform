<?php

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Models\RedisSetting;

class RedisController extends BaseApiController
{
    /**
     * Get all Redis settings.
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $settings = RedisSetting::orderBy('group')->orderBy('key')->get();

        $grouped = $settings->groupBy('group')->map(function (\Illuminate\Support\Collection $items) {
            /** @var \Illuminate\Support\Collection<int, \Modules\Core\Models\RedisSetting> $items */
            return $items->map(function (\Modules\Core\Models\RedisSetting $item) {
                return [
                    'id' => $item->id,
                    'key' => $item->key,
                    'value' => $this->presentSettingValue($item),
                    'type' => $item->type,
                    'description' => $item->description,
                    'is_encrypted' => $item->is_encrypted,
                ];
            })->values()->all();
        })->toArray();

        return $this->success($grouped, 'Redis settings retrieved successfully');
    }

    private function presentSettingValue(RedisSetting $item): mixed
    {
        if ($item->is_encrypted || $this->isSensitiveKey($item->key)) {
            return '';
        }

        return $item->value;
    }

    private function isSensitiveKey(string $key): bool
    {
        return (bool) preg_match('/(password|secret|token|api[_-]?key)/i', $key);
    }

    /**
     * Update Redis settings.
     */
    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $settings = $request->input('settings');
        if (is_array($settings)) {
            foreach ($settings as $settingData) {
                if (is_array($settingData) && isset($settingData['key'])) {
                    $key = is_string($settingData['key']) ? $settingData['key'] : '';
                    if ($key) {
                        RedisSetting::setValue($key, $settingData['value'] ?? null);
                    }
                }
            }
        }

        // Clear config cache to apply new settings
        Artisan::call('config:clear');

        return $this->success(null, 'Redis settings updated successfully');
    }

    /**
     * Test Redis connection.
     */
    public function testConnection(): \Illuminate\Http\JsonResponse
    {
        try {
            $start = microtime(true);
            $redis = Redis::connection();

            // Check if Redis requires authentication
            try {
                $pong = $redis->ping();
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'NOAUTH') || str_contains($e->getMessage(), 'Authentication required')) {
                    return $this->error('Redis authentication required. Please configure REDIS_PASSWORD in your .env file.', 401, [], 'REDIS_AUTH_REQUIRED');
                }
                throw $e;
            }

            $duration = round((microtime(true) - $start) * 1000, 2);

            if ($pong) {
                return $this->success([
                    'connected' => true,
                    'response_time' => $duration.'ms',
                    'message' => 'Redis connection successful',
                ], 'Connection test passed');
            }

            return $this->error('Redis connection failed', 500);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (str_contains($message, 'NOAUTH') || str_contains($message, 'Authentication required')) {
                return $this->error('Redis authentication required. Please configure REDIS_PASSWORD in your .env file.', 401, [], 'REDIS_AUTH_REQUIRED');
            }

            return $this->error('Redis connection error: '.$message, 500);
        }
    }

    /**
     * Get Redis server info.
     */
    public function info(): \Illuminate\Http\JsonResponse
    {
        try {
            $redis = Redis::connection();

            // Check if Redis requires authentication
            try {
                $redis->ping();
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'NOAUTH') || str_contains($e->getMessage(), 'Authentication required')) {
                    return $this->error('Redis authentication required. Please configure REDIS_PASSWORD in your .env file.', 401, [], 'REDIS_AUTH_REQUIRED');
                }
                throw $e;
            }

            $info = $redis->info();

            // Get some key metrics (Redis returns flat array)
            $stats = [
                'version' => $info['redis_version'] ?? 'Unknown',
                'uptime_days' => isset($info['uptime_in_days']) ? $info['uptime_in_days'].' days' : 'Unknown',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'used_memory' => $info['used_memory_human'] ?? 'Unknown',
                'total_keys' => $this->getTotalKeys(),
                'hits' => $info['keyspace_hits'] ?? 0,
                'misses' => $info['keyspace_misses'] ?? 0,
                'hit_rate' => $this->calculateHitRate($info),
                'total_commands' => $info['total_commands_processed'] ?? 0,
                'operations_per_sec' => $info['instantaneous_ops_per_sec'] ?? 0,
            ];

            return $this->success($stats, 'Redis info retrieved successfully');
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (str_contains($message, 'NOAUTH') || str_contains($message, 'Authentication required')) {
                return $this->error('Redis authentication required. Please configure REDIS_PASSWORD in your .env file.', 401, [], 'REDIS_AUTH_REQUIRED');
            }

            return $this->error('Failed to retrieve Redis info: '.$message, 500);
        }
    }

    /**
     * Flush Redis cache.
     */
    public function flushCache(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $type = $request->input('type', 'all'); // all, cache, config, route, view

            switch ($type) {
                case 'cache':
                    Artisan::call('cache:clear');
                    break;
                case 'config':
                    Artisan::call('config:clear');
                    break;
                case 'route':
                    Artisan::call('route:clear');
                    break;
                case 'view':
                    Artisan::call('view:clear');
                    break;
                case 'all':
                default:
                    Artisan::call('cache:clear');
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    Redis::connection()->flushdb();
                    break;
            }

            return $this->success(null, 'Cache cleared successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to flush cache: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get cache statistics.
     */
    public function cacheStats(): \Illuminate\Http\JsonResponse
    {
        try {
            $redis = Redis::connection('cache');

            // Check if Redis requires authentication
            try {
                $redis->ping();
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'NOAUTH') || str_contains($e->getMessage(), 'Authentication required')) {
                    return $this->error('Redis authentication required. Please configure REDIS_PASSWORD in your .env file.', 401, [], 'REDIS_AUTH_REQUIRED');
                }
                throw $e;
            }

            $keys = $redis->keys('*');

            $prefixRaw = config('database.redis.options.prefix');
            $prefix = is_string($prefixRaw) ? $prefixRaw : null;

            $stats = [
                'total_keys' => count($keys),
                'cache_size' => $this->getCacheSize($redis, $keys, $prefix),
                'expired_keys' => $this->getExpiredKeysCount($redis),
                'top_keys' => $this->getTopKeys($redis, $keys, 10, $prefix),
            ];

            return $this->success($stats, 'Cache statistics retrieved successfully');
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (str_contains($message, 'NOAUTH') || str_contains($message, 'Authentication required')) {
                return $this->error('Redis authentication required. Please configure REDIS_PASSWORD in your .env file.', 401, [], 'REDIS_AUTH_REQUIRED');
            }

            return $this->error('Failed to retrieve cache stats: '.$message, 500);
        }
    }

    /**
     * Helper: Get total keys count.
     * Helper: Get total keys count.
     */
    private function getTotalKeys(): int
    {
        $count = 0;
        // Try to count keys from both default and cache connections
        try {
            /** @var array<string> $keys */
            $keys = Redis::connection('default')->keys('*');
            $count += count($keys);
        } catch (\Exception $e) {
        }

        try {
            /** @var array<string> $keysCache */
            $keysCache = Redis::connection('cache')->keys('*');
            $count += count($keysCache);
        } catch (\Exception $e) {
        }

        return $count;
    }

    /**
     * Helper: Calculate hit rate.
     *
     * @param  array<string, mixed>  $info
     */
    private function calculateHitRate(array $info): string
    {
        $hitsRaw = $info['keyspace_hits'] ?? 0;
        $hits = is_numeric($hitsRaw) ? (int) $hitsRaw : 0;
        $missesRaw = $info['keyspace_misses'] ?? 0;
        $misses = is_numeric($missesRaw) ? (int) $missesRaw : 0;
        $total = $hits + $misses;

        if ($total === 0) {
            return '0%';
        }

        return round(($hits / $total) * 100, 2).'%';
    }

    /**
     * Helper: Get cache size.
     *
     * @param  \Illuminate\Redis\Connections\Connection  $redis
     * @param  array<string>  $keys
     */
    private function getCacheSize($redis, array $keys, ?string $prefix = null): string
    {
        try {
            $size = 0;
            foreach (array_slice($keys, 0, 100) as $key) { // Sample first 100 keys
                // Strip prefix if present to avoid double prefixing by the client
                $lookupKey = ($prefix && str_starts_with($key, $prefix))
                    ? substr($key, strlen($prefix))
                    : $key;

                $value = $redis->get($lookupKey);
                $size += strlen(is_string($value) ? $value : '');
            }

            return $this->formatBytes($size);
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Helper: Format bytes to human readable.
     *
     * @param  int|float  $bytes
     */
    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min((int) $pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }

    /**
     * Helper: Get expired keys count.
     *
     * @param  \Illuminate\Redis\Connections\Connection  $redis
     */
    private function getExpiredKeysCount($redis): int
    {
        try {
            $info = $redis->info();

            return isset($info['expired_keys']) ? (int) $info['expired_keys'] : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Helper: Get top keys.
     *
     * @param  \Illuminate\Redis\Connections\Connection  $redis
     * @param  array<string>  $keys
     * @return array<int, array<string, mixed>>
     */
    private function getTopKeys($redis, array $keys, int $limit = 10, ?string $prefix = null): array
    {
        $topKeys = [];

        foreach (array_slice($keys, 0, min(count($keys), 100)) as $key) {
            try {
                // Strip prefix for lookup
                $lookupKey = ($prefix && str_starts_with($key, $prefix))
                    ? substr($key, strlen($prefix))
                    : $key;

                $ttl = $redis->ttl($lookupKey);
                $value = $redis->get($lookupKey);
                $size = strlen(is_string($value) ? $value : '');

                $topKeys[] = [
                    'key' => $key, // Show full key for display
                    'size' => $this->formatBytes($size),
                    'ttl' => $ttl > 0 ? $ttl.'s' : ($ttl === -1 ? 'Never' : 'Expired'),
                    'size_bytes' => $size,
                ];
            } catch (\Exception $e) {
                continue;
            }
        }

        // Sort by size (descending)
        usort($topKeys, function ($a, $b) {
            return $b['size_bytes'] <=> $a['size_bytes'];
        });

        // Remove size_bytes helper key
        return array_map(function ($item) {
            unset($item['size_bytes']);

            return $item;
        }, array_slice($topKeys, 0, $limit));
    }

    /**
     * Warm up cache (optimize).
     */
    public function warmCache(): \Illuminate\Http\JsonResponse
    {
        try {
            $basePath = base_path();
            $php = PHP_BINARY;

            // In some web environments, PHP_BINARY might point to php-fpm or cgi
            if (str_contains($php, 'fpm') || str_contains($php, 'cgi')) {
                $php = 'php';
            }

            $commands = [
                'config:cache',
                'route:cache',
                'view:cache',
            ];

            $executedSuccessfully = true;
            $outputs = [];

            foreach ($commands as $cmd) {
                $output = [];
                $resultCode = 0;
                // Run via shell to ensure fresh process and bypass OPcache issues in Laravel 12
                @exec("$php $basePath/artisan $cmd 2>&1", $output, $resultCode);

                if ($resultCode !== 0) {
                    $executedSuccessfully = false;
                    $outputs[$cmd] = implode("\n", $output);
                    break;
                }
            }

            if ($executedSuccessfully) {
                return $this->success(null, 'Cache warmed successfully via CLI');
            }

            // Fallback to Artisan::call if CLI execution failed
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            \Illuminate\Support\Facades\Artisan::call('config:cache');
            \Illuminate\Support\Facades\Artisan::call('route:cache');
            \Illuminate\Support\Facades\Artisan::call('view:cache');

            return $this->success(null, 'Cache warmed successfully via fallback');
        } catch (\Throwable $e) {
            return $this->error('Failed to warm cache: '.$e->getMessage(), 500);
        }
    }
}
