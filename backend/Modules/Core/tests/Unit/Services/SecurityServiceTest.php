<?php

namespace Modules\Core\Tests\Unit\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Models\IpList;
use Modules\Core\Models\SecurityLog;
use Modules\Core\Models\User;
use Modules\Core\Services\SecurityService;
use Tests\TestCase;

class SecurityServiceTest extends TestCase
{
    protected SecurityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->service = new SecurityService;
    }

    /**
     * Test localhost is protected and never blocked.
     */
    public function test_localhost_is_protected(): void
    {
        $this->assertTrue($this->service->isProtectedIp('127.0.0.1'));
        $this->assertTrue($this->service->isProtectedIp('::1'));
        $this->assertTrue($this->service->isProtectedIp('localhost'));
    }

    /**
     * Test regular IP is not protected.
     */
    public function test_regular_ip_not_protected(): void
    {
        $this->assertFalse($this->service->isProtectedIp('192.168.1.1'));
        $this->assertFalse($this->service->isProtectedIp('8.8.8.8'));
    }

    /**
     * Test failed login increments counter.
     */
    public function test_record_failed_login_increments_counter(): void
    {
        $email = 'test@example.com';
        $ip = '192.168.1.100';

        $result = $this->service->recordFailedLogin($email, $ip);

        $this->assertEquals(1, $result['ip_attempts']);
        $this->assertEquals(1, $result['email_attempts']);
        $this->assertFalse($result['ip_blocked']);
        $this->assertFalse($result['account_locked']);
    }

    /**
     * Test multiple failed logins trigger blocking.
     */
    public function test_multiple_failed_logins_trigger_blocking(): void
    {
        $email = 'test@example.com';
        $ip = '10.0.0.99'; // Non-localhost IP

        // Simulate max failed attempts
        $maxAttempts = $this->service->getMaxFailedAttempts();

        for ($i = 1; $i <= $maxAttempts; $i++) {
            $result = $this->service->recordFailedLogin($email, $ip);
        }

        // After max attempts, should be blocked
        $this->assertTrue($result['ip_blocked'] || $result['account_locked']);
    }

    /**
     * Test successful login clears security cache.
     */
    public function test_successful_login_clears_cache(): void
    {
        $user = User::factory()->create();
        $ip = '192.168.1.100';

        // Record some failed attempts first
        $this->service->recordFailedLogin($user->email, $ip);
        $this->service->recordFailedLogin($user->email, $ip);

        $this->assertGreaterThan(0, $this->service->getFailedAttempts($ip));

        // Successful login should clear cache
        $this->service->recordSuccessfulLogin($user, $ip);

        $this->assertEquals(0, $this->service->getFailedAttempts($ip));
    }

    /**
     * Test account locking and unlocking.
     */
    public function test_account_lock_and_unlock(): void
    {
        $email = 'testlock@example.com';

        $this->assertFalse($this->service->isAccountLocked($email));

        $this->service->lockAccount($email, 'Test lock');

        $this->assertTrue($this->service->isAccountLocked($email));

        $this->service->unlockAccount($email);

        $this->assertFalse($this->service->isAccountLocked($email));
    }

    /**
     * Test get account lockout remaining time.
     */
    public function test_get_account_lockout_remaining(): void
    {
        $email = 'testtime@example.com';

        // No lock - should be 0
        $this->assertEquals(0, $this->service->getAccountLockoutRemaining($email));

        $this->service->lockAccount($email);

        // Should have some remaining time
        $remaining = $this->service->getAccountLockoutRemaining($email);
        $this->assertGreaterThan(0, $remaining);
    }

    /**
     * Test temporary IP blocking.
     */
    public function test_temporary_ip_blocking(): void
    {
        $ip = '10.0.0.50';

        $this->assertFalse($this->service->isIpBlocked($ip));

        $duration = $this->service->blockIpTemporarily($ip, 'Test block');

        $this->assertGreaterThan(0, $duration);
        $this->assertTrue($this->service->isIpBlocked($ip));
    }

    /**
     * Test protected IP cannot be blocked.
     */
    public function test_protected_ip_cannot_be_blocked(): void
    {
        $ip = '127.0.0.1';

        $duration = $this->service->blockIpTemporarily($ip, 'Test');

        $this->assertEquals(0, $duration);
        $this->assertFalse($this->service->isIpBlocked($ip));
    }

    /**
     * Test get block info.
     */
    public function test_get_block_info(): void
    {
        $ip = '10.0.0.60';

        $info = $this->service->getBlockInfo($ip);

        $this->assertArrayHasKey('is_blocked', $info);
        $this->assertArrayHasKey('remaining_seconds', $info);
        $this->assertArrayHasKey('offense_count', $info);
        $this->assertArrayHasKey('failed_attempts', $info);
    }

    /**
     * Test permanent IP blocking and unblocking.
     */
    public function test_permanent_block_and_unblock(): void
    {
        $ip = '10.0.0.70';

        $result = $this->service->blockIpPermanently($ip, 'Test permanent block');

        $this->assertTrue($result);
        $this->assertTrue($this->service->isIpBlocked($ip));

        $this->service->unblockIp($ip);

        $this->assertFalse($this->service->isIpBlocked($ip));
    }

    /**
     * Test whitelist management.
     */
    public function test_whitelist_management(): void
    {
        $ip = '172.16.0.1';

        $this->service->addToWhitelist($ip, 'Test whitelist');

        $this->assertTrue(IpList::isWhitelisted($ip));

        // Whitelisted IP cannot be blocked
        $duration = $this->service->blockIpTemporarily($ip);
        $this->assertEquals(0, $duration);

        $this->service->removeFromWhitelist($ip);

        $this->assertFalse(IpList::isWhitelisted($ip));
    }

    /**
     * Test get security statistics.
     */
    public function test_get_security_stats(): void
    {
        $stats = $this->service->getSecurityStats(30);

        $this->assertArrayHasKey('total_events', $stats);
        $this->assertArrayHasKey('failed_logins', $stats);
        $this->assertArrayHasKey('successful_logins', $stats);
        $this->assertArrayHasKey('blocked_ips', $stats);
        $this->assertArrayHasKey('suspicious_activities', $stats);
        $this->assertArrayHasKey('recent_events', $stats);
    }

    /**
     * Test record suspicious activity.
     */
    public function test_record_suspicious_activity(): void
    {
        $this->service->recordSuspiciousActivity('Test suspicious activity', null, ['test' => 'data']);

        $log = SecurityLog::where('event_type', 'suspicious_activity')
            ->where('description', 'Test suspicious activity')
            ->first();

        $this->assertNotNull($log);
    }

    /**
     * Test get remaining block time for non-blocked IP.
     */
    public function test_get_remaining_block_time_non_blocked(): void
    {
        $ip = '192.168.99.99';

        $remaining = $this->service->getRemainingBlockTime($ip);

        $this->assertEquals(0, $remaining);
    }

    /**
     * Test lockout duration getter.
     */
    public function test_get_lockout_duration(): void
    {
        $duration = $this->service->getLockoutDuration();

        $this->assertIsInt($duration);
        $this->assertGreaterThan(0, $duration);
    }

    /**
     * Test max failed attempts getter.
     */
    public function test_get_max_failed_attempts(): void
    {
        $max = $this->service->getMaxFailedAttempts();

        $this->assertIsInt($max);
        $this->assertGreaterThan(0, $max);
    }

    /**
     * Test whitelist prevents blocking
     */
    public function test_whitelist_prevents_blocking(): void
    {
        $ip = '10.0.0.150';
        $this->service->addToWhitelist($ip);

        // Should return false for block query
        $this->assertFalse($this->service->isIpBlocked($ip));

        // Should return false for permanent block attempt
        $this->assertFalse($this->service->blockIpPermanently($ip));

        // Should return false for global blacklist
        $this->assertFalse($this->service->isIpInGlobalBlacklist($ip));
    }

    /**
     * Test blocked operations on protected IPs
     */
    public function test_protected_ip_block_prevention(): void
    {
        $ip = '127.0.0.1';
        $this->assertFalse($this->service->blockIpPermanently($ip));
        $this->assertFalse($this->service->isIpInGlobalBlacklist($ip));
    }

    /**
     * Test get remaining block time actual calculation
     */
    public function test_get_remaining_block_time_calculation(): void
    {
        $ip = '10.0.0.160';
        Cache::put('security:block_until:'.$ip, now()->addMinutes(5)->toIso8601String());

        $remaining = $this->service->getRemainingBlockTime($ip);
        $this->assertGreaterThan(250, $remaining);
        $this->assertLessThanOrEqual(300, $remaining);

        // Test past expiration returns 0
        Cache::put('security:block_until:'.$ip, now()->subMinutes(5)->toIso8601String());
        $this->assertEquals(0, $this->service->getRemainingBlockTime($ip));
    }

    /**
     * Test retrieving blocklists and whitelists
     */
    public function test_get_blocklist_and_whitelist(): void
    {
        IpList::create(['ip_address' => '10.0.0.201', 'type' => 'blocklist', 'reason' => 'test']);
        IpList::create(['ip_address' => '10.0.0.202', 'type' => 'whitelist', 'reason' => 'test']);

        $blocklist = $this->service->getBlocklist();
        $this->assertGreaterThanOrEqual(1, $blocklist->count());
        $this->assertEquals('10.0.0.201', $blocklist->first()->ip_address);

        $whitelist = $this->service->getWhitelist();
        $this->assertGreaterThanOrEqual(1, $whitelist->count());
        $this->assertEquals('10.0.0.202', $whitelist->first()->ip_address);
    }

    /**
     * Test specific logging functions
     */
    public function test_specific_logging_functions(): void
    {
        $ip = '10.0.0.210';
        $this->service->recordGlobalBlacklistHit($ip, 'DNSBL Hit');
        $this->service->recordCountryBlock($ip, 'Geo Hit');

        $this->assertNotNull(SecurityLog::where('event_type', 'global_blacklist_blocked')->first());
        $this->assertNotNull(SecurityLog::where('event_type', 'country_blocked')->first());
    }

    /**
     * Test PoW Shield verified check and record
     */
    public function test_shield_verified_record_and_check(): void
    {
        $ip = '10.0.0.220';
        $userAgent = 'TestAgent';

        $this->assertFalse($this->service->isShieldVerified($ip, $userAgent));

        $this->service->recordShieldVerification($ip, $userAgent);

        $this->assertTrue($this->service->isShieldVerified($ip, $userAgent));

        // Also test with logging enabled
        \Modules\Core\Models\Setting::set('shield_log_verification_success', true);
        $this->service->recordShieldVerification($ip, $userAgent);
        $this->assertNotNull(SecurityLog::where('event_type', 'shield_verified')->first());
        \Modules\Core\Models\Setting::where('key', 'shield_log_verification_success')->delete();
        Cache::tags(['settings'])->flush();
    }

    /**
     * Test PoW Shield nonces and verification
     */
    public function test_shield_nonce_and_verification(): void
    {
        $ip = '10.0.0.230';

        // Test base generation
        $nonce = $this->service->generateShieldNonce($ip);
        $this->assertNotEmpty($nonce);

        // Test validation failures
        $this->assertFalse($this->service->verifyShieldSolution('', '123', $ip)); // hits ! $decoded branch
        $this->assertFalse($this->service->verifyShieldSolution('invalid_base64_!@#', '123', $ip));
        $this->assertFalse($this->service->verifyShieldSolution(base64_encode('too:few:parts'), '123', $ip));

        // Test parts with wrong IP
        $decoded = base64_decode($nonce);
        $wrongIpNonce = base64_encode(str_replace($ip, '10.0.0.999', $decoded));
        $this->assertFalse($this->service->verifyShieldSolution($wrongIpNonce, '123', $ip));

        // Test invalid signature
        $parts = explode(':', $decoded);
        $parts[4] = 'invalid_sig';
        $invalidSigNonce = base64_encode(implode(':', $parts));
        $this->assertFalse($this->service->verifyShieldSolution($invalidSigNonce, '123', $ip));

        // Test expired TTL
        $parts = explode(':', $decoded);
        $parts[1] = (string) (time() - 700);

        // Re-sign to test TTL specifically
        $extData = "{$ip}:{$parts[1]}:{$parts[2]}:{$parts[3]}";
        $signature = hash_hmac('sha256', $extData, config('app.key'));
        $parts[4] = $signature;
        $expiredNonce = base64_encode(implode(':', $parts));

        $this->assertFalse($this->service->verifyShieldSolution($expiredNonce, '123', $ip));

        // Test Legacy Format verification
        $legacyData = "{$ip}:".time().':'.bin2hex(random_bytes(16));
        $legacySignature = hash_hmac('sha256', $legacyData, config('app.key'));
        $legacyNonce = base64_encode("{$legacyData}:{$legacySignature}");

        // Should fail because solution is wrong, but reach PoW hash check
        $this->assertFalse($this->service->verifyShieldSolution($legacyNonce, 'wrong_solution', $ip));

        // Test correct New Format fails on wrong solution
        $this->assertFalse($this->service->verifyShieldSolution($nonce, 'wrong_solution', $ip));

        // Mock a real PoW win (Difficulty 1 for speed)
        \Modules\Core\Models\Setting::set('shield_protection_difficulty', 1);
        $easyNonce = $this->service->generateShieldNonce($ip);

        $solution = 0;
        while (true) {
            $hash = hash('sha256', $easyNonce.$solution);
            if (str_starts_with($hash, '0')) {
                break;
            }
            $solution++;
        }

        $this->assertTrue($this->service->verifyShieldSolution($easyNonce, (string) $solution, $ip));

        \Modules\Core\Models\Setting::where('key', 'shield_protection_difficulty')->delete();
        Cache::tags(['settings'])->flush();
    }

    /**
     * Test PoW Tracking and Dynamic Difficulty
     */
    public function test_shield_difficulty_scaling(): void
    {
        \Modules\Core\Models\Setting::set('shield_protection_difficulty', 4);

        $this->assertEquals(4, $this->service->getShieldDifficulty());

        // Track 600 attempts (triggers +1)
        Cache::put('security:shield:attempts_per_minute', 600, 60);
        $this->assertEquals(5, $this->service->getShieldDifficulty());

        // Track 2100 attempts (triggers +2)
        Cache::put('security:shield:attempts_per_minute', 2100, 60);
        $this->assertEquals(6, $this->service->getShieldDifficulty());

        // Check track method
        Cache::forget('security:shield:attempts_per_minute');
        $this->service->trackShieldAttempt();
        $this->service->trackShieldAttempt();
        $this->assertEquals(2, Cache::get('security:shield:attempts_per_minute'));

        // Check Shield Stats
        $stats = $this->service->getShieldStats();
        $this->assertArrayHasKey('verifications', $stats);
        $this->assertArrayHasKey('isScaling', $stats);

        \Modules\Core\Models\Setting::where('key', 'shield_protection_difficulty')->delete();
        Cache::tags(['settings'])->flush();
    }

    /**
     * Test Global Blacklist DNSBL
     */
    public function test_global_blacklist_dnsbl(): void
    {
        // IP 127.0.0.2 is the standard test string for DNSBL returning a positive SBL hit
        $blacklistedIp = '127.0.0.2';

        // Protected IP should return false immediately
        $this->assertFalse($this->service->isIpInGlobalBlacklist('127.0.0.1'));

        // Trigger checkDnsbl to cover the lines.
        // Whether it returns true or false depends on active network/DNS during CLI tests.
        $this->assertIsBool($this->service->isIpInGlobalBlacklist($blacklistedIp));

        // We test caching mechanism
        Cache::put('security:global_blacklist:192.168.1.5', true, 3600);
        $this->assertTrue($this->service->isIpInGlobalBlacklist('192.168.1.5'));

        // Test invalid IP
        $this->assertFalse($this->service->isIpInGlobalBlacklist('invalid.ip.format'));
    }
}
