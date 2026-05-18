<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Cms\Database\Seeders\CmsDatabaseSeeder;
use Modules\Layout\Database\Seeders\LayoutDatabaseSeeder;
use Modules\School\Database\Seeders\SchoolDatabaseSeeder;
use Modules\School\Models\HR\Staff;
use Modules\System\Database\Seeders\SystemDatabaseSeeder;
use Modules\System\Models\User;

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
        if (class_exists(SchoolDatabaseSeeder::class)) {
            $this->call(SchoolDatabaseSeeder::class);
        }

        // Link Super Admin to Primary School Unit (Legacy backup logic)
        $this->linkSuperAdminToSchool();
    }

    private function linkSuperAdminToSchool()
    {
        $admin = User::where('email', env('SUPER_ADMIN_EMAIL', 'super@jejakawan.com'))->first();
        $primarySchool = DB::table('sch_ins_schools')->where('npsn', '10000001')->first();
        $primaryUnit = $primarySchool
            ? DB::table('sch_ins_levels')->where('school_id', $primarySchool->id)->first()
            : null;

        if ($admin && $primarySchool && $primaryUnit) {
            Staff::withoutGlobalScopes()->updateOrCreate(
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
