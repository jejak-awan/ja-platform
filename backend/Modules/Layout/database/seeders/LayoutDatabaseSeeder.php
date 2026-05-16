<?php

namespace Modules\Layout\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Layout\Models\Theme;

class LayoutDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            ThemeSeeder::class,
            MenuLocationStandardizationSeeder::class,
        ]);
    }
}
