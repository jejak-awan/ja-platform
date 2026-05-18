<?php

namespace Modules\System\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\System\Models\User;

class SystemDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Foundation (Roles, Permissions, Basic Settings, Languages)
        $this->call(FoundationSeeder::class);

        // 2. Create Super Admin
        $superEmail = env('SUPER_ADMIN_EMAIL', 'super@jejakawan.com');
        $superPassword = env('SUPER_ADMIN_PASSWORD', 'ChangeMeOnFirstLogin!');

        $superAdmin = User::firstOrCreate(
            ['email' => $superEmail],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($superPassword),
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('super');

        // 3. Infrastructure (Tags, Media Folders)
        $this->call(InfrastructureSeeder::class);
    }
}
