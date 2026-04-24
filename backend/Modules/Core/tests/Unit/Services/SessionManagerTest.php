<?php

namespace Modules\Core\Tests\Unit\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Modules\Core\Models\User;
use Modules\Core\Services\SessionManager;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SessionManagerTest extends TestCase
{
    public function test_set_lifetime_for_user_invalid()
    {
        // Not a user object
        SessionManager::setLifetimeForUser(null);
        // We assert nothing threw, and session wasn't modified because the flow aborted early.
        $this->assertNull(Session::get('session_lifetime'));
    }

    public function test_set_lifetime_for_admin_user()
    {
        $admin = User::factory()->create();
        if (! Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin', 'guard_name' => 'web']);
        }
        $admin->assignRole('admin');

        Config::set('session.admin_lifetime', 120);

        SessionManager::setLifetimeForUser($admin);

        $this->assertEquals(120, config('session.lifetime'));
        $this->assertEquals(120, Session::get('session_lifetime'));
    }

    public function test_set_lifetime_for_regular_user()
    {
        $user = User::factory()->create();

        Config::set('session.user_lifetime', 480);

        SessionManager::setLifetimeForUser($user);

        $this->assertEquals(480, config('session.lifetime'));
        $this->assertEquals(480, Session::get('session_lifetime'));
    }

    public function test_get_lifetime()
    {
        Config::set('session.lifetime', 60);

        // Before setting in session
        $this->assertEquals(60, SessionManager::getLifetime());

        // After setting in session
        Session::put('session_lifetime', 300);
        $this->assertEquals(300, SessionManager::getLifetime());
    }
}
