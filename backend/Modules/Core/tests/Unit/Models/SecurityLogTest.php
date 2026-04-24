<?php

namespace Modules\Core\Tests\Unit\Models;

use Modules\Core\Models\SecurityLog;
use Modules\Core\Models\User;
use Tests\TestCase;

class SecurityLogTest extends TestCase
{
    public function test_user_relationship()
    {
        $user = User::factory()->create();
        $log = SecurityLog::create([
            'user_id' => $user->id,
            'event_type' => 'test_event',
        ]);

        $this->assertInstanceOf(User::class, $log->user);
        $this->assertEquals($user->id, $log->user->id);
    }

    public function test_default_descriptions_are_generated()
    {
        $events = [
            'login_failed' => 'Failed login attempt',
            'login_success' => 'Successful login',
            'login_blocked' => 'Login blocked due to too many failed attempts',
            'ip_blocked' => 'IP address blocked',
            'ip_unblocked' => 'IP address unblocked',
            'suspicious_activity' => 'Suspicious activity detected',
            'malicious_scanner_blocked' => 'Blocked malicious scanner',
            'malicious_extension_blocked' => 'Blocked malicious extension',
            'global_blacklist_blocked' => 'Blocked by global blacklist (Spamhaus)',
            'country_blocked' => 'Blocked by geolocation policy',
            'password_changed' => 'Password changed',
            'permission_denied' => 'Permission denied',
        ];

        foreach ($events as $type => $expected) {
            $log = SecurityLog::log($type);
            $this->assertEquals($expected, $log->description);
        }

        // Test fallback generated description
        $fallback = SecurityLog::log('magic_unknown_string');
        $this->assertEquals('Magic unknown string', $fallback->description);
    }
}
