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

        // 3. Create Default Super Admin User (credentials from .env)
        $superEmail = env('SUPER_ADMIN_EMAIL', 'super@jejakawan.com');
        $superPassword = env('SUPER_ADMIN_PASSWORD', 'ChangeMeOnFirstLogin!');
        $admin = User::firstOrCreate(
            ['email' => $superEmail],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($superPassword),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('super');
        $this->command->info("Super Admin created: {$superEmail}");

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

        // 6. Link Admin to Primary School Unit (Dynamic ID)
        $this->command->info('Linking Admin to Primary School Unit...');
        $primarySchool = \Illuminate\Support\Facades\DB::table('sch_ins_schools')->where('npsn', '10000001')->first();
        $primaryUnit = $primarySchool
            ? \Illuminate\Support\Facades\DB::table('sch_ins_levels')->where('school_id', $primarySchool->id)->first()
            : null;

        if ($primarySchool && $primaryUnit) {
            \Modules\School\Models\HR\Staff::updateOrCreate(
                ['user_id' => $admin->id],
                [
                    'school_id' => $primarySchool->id,
                    'workspace_id' => $primaryUnit->id,
                    'full_name' => $admin->name,
                    'ptk_type' => 'Kepala Sekolah',
                    'employment_status' => 'PNS',
                ]
            );
            $this->command->info("Admin linked to School #{$primarySchool->id}, Unit #{$primaryUnit->id}");
        } else {
            $this->command->warn('Primary school/unit not found. Skipping staff link.');
        }

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
            $mediaService = new \Modules\Core\Services\MediaService;
            $stats = $mediaService->scan();
            $this->command->info("Media sync completed: {$stats['added']} new files linked.");
        } catch (\Exception $e) {
            $this->command->warn('Media sync failed: ' . $e->getMessage());
        }

        $this->command->info('Level 9 Database Refinement completed successfully!');
    }
}
