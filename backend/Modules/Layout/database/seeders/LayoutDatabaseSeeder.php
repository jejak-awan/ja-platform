<?php

declare(strict_types=1);

namespace Modules\Layout\Database\Seeders;

use Illuminate\Database\Seeder;

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
