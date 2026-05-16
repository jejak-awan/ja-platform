<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\System\Database\Seeders\SystemDatabaseSeeder;
use Modules\Layout\Database\Seeders\LayoutDatabaseSeeder;
use Modules\Cms\Database\Seeders\CmsDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SystemDatabaseSeeder::class);
        $this->call(LayoutDatabaseSeeder::class);
        $this->call(CmsDatabaseSeeder::class);

        // Call School module seeder if it exists
        if (class_exists(\Modules\School\Database\Seeders\SchoolDatabaseSeeder::class)) {
            $this->call(\Modules\School\Database\Seeders\SchoolDatabaseSeeder::class);
        }

        // Link Super Admin to Primary School Unit (Legacy backup logic)
        $this->linkSuperAdminToSchool();
    }

    private function linkSuperAdminToSchool()
    {
        $admin = \Modules\System\Models\User::where('email', env('SUPER_ADMIN_EMAIL', 'super@jejakawan.com'))->first();
        $primarySchool = \Illuminate\Support\Facades\DB::table('sch_ins_schools')->where('npsn', '10000001')->first();
        $primaryUnit = $primarySchool
            ? \Illuminate\Support\Facades\DB::table('sch_ins_levels')->where('school_id', $primarySchool->id)->first()
            : null;

        if ($admin && $primarySchool && $primaryUnit) {
            \Modules\School\Models\HR\Staff::withoutGlobalScopes()->updateOrCreate(
                ['user_id' => $admin->id],
                [
                    'school_id' => $primarySchool->id,
                    'workspace_id' => $primaryUnit->id,
                    'full_name' => $admin->name,
                    'ptk_type' => 'Kepala Sekolah',
                    'employment_status' => 'PNS',
                ]
            );
        }
    }
}
