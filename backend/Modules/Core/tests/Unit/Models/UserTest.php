<?php

namespace Modules\Core\Tests\Unit\Models;

use Modules\Core\Models\Setting;
use Modules\Core\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_relationships()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->media());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->activityLogs());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->notifications());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $user->twoFactorAuth());
    }

    public function test_user_preferences()
    {
        $user = User::factory()->create();
        $this->assertNull($user->getPreference('theme'));
        $this->assertEquals('dark', $user->getPreference('theme', 'dark'));

        $user->setPreference('theme', 'light');
        $this->assertEquals('light', $user->getPreference('theme'));

        $user->setPreference('notifications.email', true);
        $this->assertTrue($user->getPreference('notifications.email'));
    }

    public function test_requires_two_factor_enforcement()
    {
        $user = User::factory()->create();

        // Test hasTwoFactorEnabled fallback
        Setting::set('enable_2fa', false);
        $this->assertFalse($user->hasTwoFactorEnabled());

        Setting::set('enable_2fa', true);
        $this->assertFalse($user->hasTwoFactorEnabled()); // no TFA record yet

        // Disabled globally for enforcement
        Setting::set('enable_2fa', false);
        $this->assertFalse($user->requiresTwoFactor());

        // Enabled globally, but enforcement is 'no'
        Setting::set('enable_2fa', true);
        Setting::set('two_factor_enforced_roles', 'no');
        $this->assertFalse($user->requiresTwoFactor());

        // Enforcement is 'all'
        Setting::set('two_factor_enforced_roles', 'all');
        $this->assertTrue($user->requiresTwoFactor());

        // Enforcement is 'admin'
        Setting::set('two_factor_enforced_roles', 'admin');

        // User has no role
        $this->assertFalse($user->requiresTwoFactor());

        // Assign admin role (assuming role exists)
        if (! Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin', 'guard_name' => 'web']);
        }
        $user->assignRole('admin');
        $this->assertTrue($user->requiresTwoFactor());
    }

    public function test_role_ranks()
    {
        $userLow = User::factory()->create();
        $userHigh = User::factory()->create();

        if (! Role::where('name', 'member')->exists()) {
            Role::create(['name' => 'member', 'guard_name' => 'web']);
        }
        if (! Role::where('name', 'super-admin')->exists()) {
            Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        }

        $userLow->assignRole('member');
        $userHigh->assignRole('super-admin');

        $this->assertEquals(20, $userLow->getRoleRank());
        $this->assertEquals(100, $userHigh->getRoleRank());

        // Test isHigherThan
        $this->assertTrue($userHigh->isHigherThan($userLow));
        $this->assertFalse($userLow->isHigherThan($userHigh));

        // Test isAtLeastRole
        $this->assertTrue($userLow->isAtLeastRole('member'));
        $this->assertFalse($userLow->isAtLeastRole('admin'));
        $this->assertTrue($userHigh->isAtLeastRole('super-admin'));

        // Test unknown role check
        $this->assertFalse($userLow->isAtLeastRole('non_existent_role'));

        // Test user with no roles handled safely
        $userNone = User::factory()->create();
        $this->assertEquals(0, $userNone->getRoleRank());
    }
}
