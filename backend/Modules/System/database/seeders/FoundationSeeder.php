<?php

namespace Modules\System\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\System\Models\User;
use Modules\System\Models\RedisSetting;
use Modules\System\Models\Setting;
use Modules\System\Models\Language;
use Modules\System\Models\Permission;
use Modules\System\Models\Role;
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

        // 5. Security Officer
        $securityOfficer = Role::firstOrCreate(['name' => 'security-officer', 'guard_name' => 'web']);
        $securityOfficer->syncPermissions([
            'view settings',
            'view logs',
            'view security logs',
            'manage security operations',
            'manage security logs',
            'manage security ip-lists',
            'manage security integrity',
            'manage security maintenance',
        ]);

        $this->assignSecurityOfficerFromEnv($securityOfficer);
    }

    protected function assignSecurityOfficerFromEnv(Role $securityOfficer): void
    {
        $emailsRaw = env('SECURITY_OFFICER_EMAILS', '');
        if (! is_string($emailsRaw) || trim($emailsRaw) === '') {
            return;
        }

        $emails = collect(explode(',', $emailsRaw))
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '' && filter_var($v, FILTER_VALIDATE_EMAIL))
            ->values();

        if ($emails->isEmpty()) {
            return;
        }

        $users = User::query()
            ->whereIn('email', $emails->all())
            ->get(['id', 'email']);

        foreach ($users as $user) {
            $user->syncRoles([$securityOfficer->name]);
        }

        $assignedEmails = $users->pluck('email')->all();
        $missingEmails = array_values(array_diff($emails->all(), $assignedEmails));

        if ($this->command) {
            if (! empty($assignedEmails)) {
                $this->command->info('Assigned security-officer role to: '.implode(', ', $assignedEmails));
            }
            if (! empty($missingEmails)) {
                $this->command->warn('SECURITY_OFFICER_EMAILS not found: '.implode(', ', $missingEmails));
            }
        }
    }

    protected function seedSettings(): void
    {
        $settings = [
            // System Settings
            ['key' => 'app_name', 'value' => 'JA-Platform', 'group' => 'system', 'type' => 'string'],
            ['key' => 'license_type', 'value' => 'pro', 'group' => 'system', 'type' => 'string'],
            ['key' => 'maintenance_mode', 'value' => '0', 'group' => 'system', 'type' => 'boolean'],
            ['key' => 'timezone', 'value' => 'Asia/Jakarta', 'group' => 'general', 'type' => 'string'],
            ['key' => 'log_retention_days', 'value' => '90', 'group' => 'monitoring', 'type' => 'integer'],

            // Security
            ['key' => 'enable_registration', 'value' => '0', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'require_email_verification', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'enable_2fa', 'value' => '0', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'two_factor_method', 'value' => 'authenticator', 'group' => 'security', 'type' => 'string'],
            ['key' => 'two_factor_enforced_roles', 'value' => '["admin", "system-admin", "super"]', 'group' => 'security', 'type' => 'json'],
            ['key' => 'password_min_length', 'value' => '8', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'password_require_uppercase', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'password_require_lowercase', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'password_require_number', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'password_require_symbol', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'session_lifetime', 'value' => '120', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'single_session_enabled', 'value' => '0', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'max_concurrent_sessions', 'value' => '3', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'login_attempts_limit', 'value' => '5', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'block_duration_minutes', 'value' => '30', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'security_alert_blocked_ip_threshold', 'value' => '3', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'security_alert_suspicious_ip_threshold', 'value' => '10', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'security_alert_window_minutes', 'value' => '60', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'enable_captcha', 'value' => '0', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'captcha_method', 'value' => 'slider', 'group' => 'security', 'type' => 'string'],
            ['key' => 'captcha_on_login', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'captcha_on_register', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'captcha_on_contact', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'captcha_on_forgot_password', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'shield_protection_mode', 'value' => 'off', 'group' => 'security', 'type' => 'string'],
            ['key' => 'shield_protection_difficulty', 'value' => '4', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'shield_log_verification_success', 'value' => '0', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'shield_enable_ip_intelligence', 'value' => '0', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'shield_allowed_countries', 'value' => '[]', 'group' => 'security', 'type' => 'json'],
            ['key' => 'admin_dashboard_slug', 'value' => 'ja-dash', 'group' => 'security', 'type' => 'string'],
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
            Language::updateOrCreate(['code' => $language['code']], $language);
        }
    }

    protected function seedScheduledTasks(): void
    {
        $this->call(ScheduledTaskSeeder::class);
    }

    protected function seedRedisSettings(): void
    {
        $settings = [
            ['key' => 'redis_host', 'value' => env('REDIS_HOST', '127.0.0.1')],
            ['key' => 'redis_port', 'value' => env('REDIS_PORT', '6379')],
        ];

        foreach ($settings as $setting) {
            RedisSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
