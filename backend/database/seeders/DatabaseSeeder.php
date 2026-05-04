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
        $this->command->info('Starting Level 9 Foundation Seeding...');

        // 1. Foundation (Roles, Permissions, Settings, Languages)
        $this->call(\Modules\Core\Database\Seeders\FoundationSeeder::class);

        // 2. Global Infrastructure (Tags, Global Media Folders)
        $this->call(\Modules\Core\Database\Seeders\InfrastructureSeeder::class);

        // 3. Create Default Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@kdua.net'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('super');

        // 4. CMS Theme Registration & Activation (Required before module seeders)
        $this->command->info('Registering and Activating Themes...');
        $themeService = app(\Modules\Cms\Services\ThemeService::class);
        $themeService->scanThemes();
        
        $janari = \Modules\Cms\Models\Theme::where('slug', 'janari')->first();
        if ($janari) {
            $themeService->activateTheme($janari);
            $this->command->info('Theme "Janari" activated.');
        }

        // 5. Module Seeders
        $this->call(\Modules\Cms\Database\Seeders\CmsDatabaseSeeder::class);
        $this->call(\Modules\School\Database\Seeders\SchoolDatabaseSeeder::class);

        // 6. Link Admin to Primary School Unit (Unit ID 15 is our Level 9 Standard)
        $this->command->info('Linking Admin to School Unit 15...');
        \Modules\School\Models\HR\Staff::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'school_id' => 15,
                'school_unit_id' => 15,
                'full_name' => $admin->name,
                'ptk_type' => 'Kepala Sekolah',
                'employment_status' => 'PNS',
            ]
        );

        // 7. Studio Structure (Menus, Widgets)
        $this->call(\Modules\Cms\Database\Seeders\StudioSeeder::class);

        // 8. Sample Data (Disabled for Production Readiness)
        /*
        if (app()->environment('local', 'development', 'testing')) {
            $this->call(\Modules\Core\Database\Seeders\SampleDataSeeder::class);
        }
        */

        // 9. Sync Media Files (Final check)
        $this->command->info('Scanning filesystem for media files...');
        try {
            $mediaService = new \Modules\Cms\Services\MediaService;
            $stats = $mediaService->scan();
            $this->command->info("Media sync completed: {$stats['added']} new files linked.");
        } catch (\Exception $e) {
            $this->command->warn('Media sync failed: ' . $e->getMessage());
        }

        $this->command->info('Level 9 Database Refinement completed successfully!');
    }
}
