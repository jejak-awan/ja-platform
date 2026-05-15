<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\System\Database\Seeders\SystemDatabaseSeeder;
use Modules\Cms\Database\Seeders\CmsDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SystemDatabaseSeeder::class,
            CmsDatabaseSeeder::class,
        ]);
    }
}
