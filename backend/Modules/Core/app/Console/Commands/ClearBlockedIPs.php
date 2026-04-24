<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearBlockedIPs extends Command
{
    protected $signature = 'security:clear-blocked-ips {ip? : Specific IP address to unblock (optional)}';

    protected $description = 'Clear blocked IP addresses and failed login attempts from cache';

    public function handle(): int
    {
        /** @var string|null $ip */
        $ip = $this->argument('ip');

        if ($ip) {
            // Clear specific IP
            Cache::forget("blocked_ip_{$ip}");
            Cache::forget("failed_login_attempts_{$ip}");
            $this->info("Cleared blocked status and failed attempts for IP: {$ip}");
        } else {
            // Clear all blocked IPs (this is a bit tricky with cache, but we can try)
            $this->info('Clearing all blocked IPs and failed login attempts...');
            $this->warn('Note: Cache keys are dynamic, so this will clear common patterns.');

            // Clear cache entirely (be careful with this in production)
            Cache::flush();
            $this->info('Cache cleared successfully!');
        }

        return 0;
    }
}
