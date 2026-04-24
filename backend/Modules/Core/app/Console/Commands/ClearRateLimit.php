<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class ClearRateLimit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rate-limit:clear 
                            {--ip= : Clear rate limit for specific IP address}
                            {--all : Clear all rate limits}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear rate limit for login attempts';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $ip = (string) $this->option('ip');
        $all = (bool) $this->option('all');

        if ($all) {
            // Clear all rate limit keys
            $this->clearAllRateLimits();
            $this->info('All rate limits cleared successfully!');

            return 0;
        }

        if ($ip) {
            // Clear rate limit for specific IP
            $this->clearRateLimitForIp($ip);
            $this->info("Rate limit cleared for IP: {$ip}");

            return 0;
        }

        // If no options provided, show help
        $this->error('Please specify --ip=<ip_address> or --all');
        $this->info('Examples:');
        $this->info('  php artisan rate-limit:clear --ip=127.0.0.1');
        $this->info('  php artisan rate-limit:clear --all');

        return 1;
    }

    /**
     * Clear rate limit for specific IP
     */
    protected function clearRateLimitForIp(string $ip): void
    {
        // Clear throttle rate limit
        $key = "throttle:5,1:{$ip}";
        RateLimiter::clear($key);

        // Also clear from cache (Laravel uses cache for rate limiting)
        Cache::forget($key);

        // Clear security service related caches
        Cache::forget("failed_login_attempts_{$ip}");
        Cache::forget("blocked_ip_{$ip}");

        $this->info("Cleared rate limit key: {$key}");
    }

    /**
     * Clear all rate limits
     */
    protected function clearAllRateLimits(): bool
    {
        $cleared = 0;

        // Try to clear common rate limit patterns
        // Note: Laravel doesn't support pattern-based cache clearing, so we'll clear common keys
        $commonKeys = [
            'throttle:5,1',
            'failed_login_attempts',
            'blocked_ip',
            'account_locked',
        ];

        // Clear cache using cache tags if available (Redis/Memcached)
        if (method_exists(Cache::getStore(), 'tags')) {
            try {
                Cache::tags(['rate_limit', 'throttle', 'security'])->flush();
                $this->info('Cleared rate limit cache using tags');
                $cleared++;
            } catch (\Exception $e) {
                $this->warn('Cache tags not supported, using alternative method');
            }
        }

        // Alternative: Clear entire cache if Redis/file cache
        $driver = config('cache.default');
        if (in_array($driver, ['redis', 'file', 'array'])) {
            if ($this->confirm('This will clear ALL application cache. Continue?', false)) {
                Cache::flush();
                $this->info('All cache cleared!');
                $cleared++;
            } else {
                $this->warn('Cache flush cancelled. Rate limits may still be active.');
                $this->info('You can clear specific IP rate limits using: php artisan rate-limit:clear --ip=<ip_address>');
            }
        } else {
            /** @var string $driverStr */
            $driverStr = is_string($driver) ? $driver : 'unknown';
            $this->warn("Cache driver '{$driverStr}' doesn't support bulk clearing.");
            $this->info('Please clear rate limits for specific IPs using: php artisan rate-limit:clear --ip=<ip_address>');
        }

        return $cleared > 0;
    }
}
