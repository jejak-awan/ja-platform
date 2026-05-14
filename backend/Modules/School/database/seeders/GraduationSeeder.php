<?php
 
namespace Modules\School\Database\Seeders;
 
use Illuminate\Database\Seeder;
use Modules\School\Models\Operations\GraduationSetting;
 
class GraduationSeeder extends Seeder
{
    public function run(): void
    {
        GraduationSetting::updateOrCreate(
            ['graduation_year' => 2026, 'workspace_id' => 1],
            [
                'is_open' => true,
                'announcement_date' => '2026-06-15 10:00:00',
                'subjects' => [
                    ['name' => 'Bahasa Indonesia', 'min_score' => 75],
                    ['name' => 'Matematika', 'min_score' => 70],
                    ['name' => 'Bahasa Inggris', 'min_score' => 75],
                    ['name' => 'Produktif RPL', 'min_score' => 80],
                ],
                'config' => [
                    'allow_download_skl' => true,
                    'require_all_subjects' => true,
                ]
            ]
        );
    }
}
