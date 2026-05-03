<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Models\User;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Foundation (Roles, Permissions, Settings, Languages, Tasks)
        $this->call(\Modules\Core\Database\Seeders\FoundationSeeder::class);

        // 2. Global Infrastructure (Tags, Global Media Folders, etc.)
        $this->call(\Modules\Core\Database\Seeders\InfrastructureSeeder::class);

        // 3. Create Default Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@kdua.net'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('super-admin');

        // 4. Module Seeders
        $this->call(\Modules\Cms\Database\Seeders\CmsDatabaseSeeder::class);
        $this->call(\Modules\School\Database\Seeders\SchoolDatabaseSeeder::class);

        // 5. Studio Structure & Themes
        app(\Modules\Cms\Services\ThemeService::class)->scanThemes();
        $this->call(\Modules\Cms\Database\Seeders\StudioSeeder::class);

        // 6. Sample Data (Optional, for development)
        if (app()->environment('local', 'development', 'testing')) {
            $this->call(\Modules\Core\Database\Seeders\SampleDataSeeder::class);
        }

        // 5. Sync Media Files
        $this->command->info('Scanning for media files...');
        $mediaService = new \Modules\Cms\Services\MediaService;
        $stats = $mediaService->scan();
        $this->command->info("Media sync: Scanned {$stats['scanned']} files, added {$stats['added']} new files.");

        $this->command->info('Full database reorganization and seeding completed successfully!');
    }
}
