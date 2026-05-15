<?php

namespace Modules\System\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\System\Models\User;
use Modules\System\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SystemDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Roles
        $roles = ['super', 'system-admin', 'admin', 'operator', 'member'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // 2. Create Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@ja-platform.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('super');

        // 3. Basic Settings
        $settings = [
            'app_name' => ['value' => 'JA-Platform', 'type' => 'string', 'group' => 'general'],
            'enable_registration' => ['value' => 'true', 'type' => 'boolean', 'group' => 'auth'],
            'enable_2fa' => ['value' => 'false', 'type' => 'boolean', 'group' => 'security'],
            'maintenance_mode' => ['value' => 'false', 'type' => 'boolean', 'group' => 'system'],
        ];

        foreach ($settings as $key => $data) {
            Setting::set($key, $data['value'], $data['type'], $data['group']);
        }
    }
}
