<?php

namespace Modules\System\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\System\Models\User;
use Modules\System\Models\RedisSetting;
use Modules\System\Models\Setting;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class FoundationSeeder extends Seeder
{
    /**
     * Run the general foundation seeds.
     */
    public function run(): void
    {
        // 1. Roles & Permissions (Spatie)
        $this->seedRolesAndPermissions();

        // 2. System Settings
        $this->seedSettings();

        // 3. Languages
        $this->seedLanguages();

        // 4. Scheduled Tasks
        $this->seedScheduledTasks();

        // 5. Redis Settings
        $this->seedRedisSettings();

        $this->command->info('Foundation seeded successfully!');
    }

    protected function seedRolesAndPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Core Identity & Profile
            'view profile', 'edit profile',
            
            // Media (Global Infrastructure)
            'view media', 'upload media', 'edit media', 'delete media', 'manage media',
            
            // Users & RBAC
            'view users', 'create users', 'edit users', 'delete users', 'verify users', 'manage users',
            'view roles', 'create roles', 'edit roles', 'delete roles',
            
            // System Governance
            'view settings', 'manage settings',
            'view system', 'manage system',
            'view logs', 'delete logs',
            'view activity logs',
            
            // Security Operations
            'manage security operations',
            'manage security logs',
            'manage security ip-lists',
            'manage security integrity',
            'manage security maintenance',
            'view security logs',
            
            // Infrastructure Services
            'view plugins', 'install plugins', 'manage plugins',
            'view redirects', 'manage redirects',
            'view scheduled tasks', 'manage scheduled tasks',
            'view backups', 'create backups', 'manage backups',
            'view analytics',
            
            // Module Governance (Generic)
            'manage module access',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 1. Super Admin (Total authority)
        $superAdmin = Role::firstOrCreate(['name' => 'super', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. System Admin (Infrastructure authority)
        $systemAdmin = Role::firstOrCreate(['name' => 'system-admin', 'guard_name' => 'web']);
        $systemAdmin->syncPermissions(Permission::whereIn('name', [
            'view users', 'create users', 'edit users', 'delete users', 'verify users', 'manage users',
            'view roles', 'create roles', 'edit roles', 'delete roles',
            'view settings', 'manage settings',
            'view system', 'manage system',
            'view logs', 'delete logs',
            'view backups', 'create backups', 'manage backups',
            'view scheduled tasks', 'manage scheduled tasks',
            'view plugins', 'manage plugins',
            'manage security operations',
            'manage security logs',
            'manage security ip-lists',
            'manage security integrity',
            'manage security maintenance',
            'view security logs',
            'manage module access',
        ])->get());

        // 3. Global Admin (Standard admin)
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminPermissions = Permission::whereNotIn('name', [
            'manage system', 'view security logs', 'manage backups', 'manage scheduled tasks', 'delete users',
            'manage security operations', 'manage security logs', 'manage security ip-lists', 'manage security integrity', 'manage security maintenance',
        ])->get();
        $admin->syncPermissions($adminPermissions);

        // 4. Global Member
        $member = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
        $member->syncPermissions(['view profile', 'edit profile', 'view media']);
    }

    protected function seedSettings(): void
    {
        $settings = [
            // System Settings
            ['key' => 'app_name', 'value' => 'JA-Platform', 'group' => 'system', 'type' => 'string'],
            ['key' => 'license_type', 'value' => 'pro', 'group' => 'system', 'type' => 'string'],
            ['key' => 'maintenance_mode', 'value' => '0', 'group' => 'system', 'type' => 'boolean'],
            ['key' => 'timezone', 'value' => 'Asia/Jakarta', 'group' => 'general', 'type' => 'string'],
            ['key' => 'enable_2fa', 'value' => '0', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'log_retention_days', 'value' => '90', 'group' => 'monitoring', 'type' => 'integer'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }

    protected function seedLanguages(): void
    {
        $languages = [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'flag' => '🇺🇸', 'is_default' => 0, 'is_active' => 1],
            ['code' => 'id', 'name' => 'Indonesian', 'native_name' => 'Bahasa Indonesia', 'flag' => '🇮🇩', 'is_default' => 1, 'is_active' => 1],
        ];

        foreach ($languages as $language) {
            DB::table('core_languages')->updateOrInsert(['code' => $language['code']], $language);
        }
    }

    protected function seedScheduledTasks(): void
    {
        $this->call(ScheduledTaskSeeder::class);
    }

    protected function seedRedisSettings(): void
    {
        $settings = [
            ['key' => 'redis_host', 'value' => env('REDIS_HOST', '127.0.0.1'), 'type' => 'string', 'group' => 'connection'],
            ['key' => 'redis_port', 'value' => env('REDIS_PORT', '6379'), 'type' => 'integer', 'group' => 'connection'],
        ];

        foreach ($settings as $setting) {
            RedisSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
