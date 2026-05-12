<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Core\Models\Tag;
use Modules\Core\Models\User;

class InfrastructureSeeder extends Seeder
{
    /**
     * Run the infrastructure seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', env('SUPER_ADMIN_EMAIL', 'super@jejakawan.com'))->first();
        if (! $admin) {
            return;
        }

        // 1. Standard Global Tags
        $tags = ['Education', 'Technology', 'School', 'LMS', 'CMS', 'Announcement'];
        foreach ($tags as $tag) {
            $slug = Str::slug($tag);
            $tagModel = Tag::withTrashed()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $tag,
                    'author_id' => $admin->id,
                ]
            );
            if ($tagModel->trashed()) {
                $tagModel->restore();
            }
        }

        // 2. Standard Media Folders
        $folders = [
            ['name' => 'Logos', 'slug' => 'logos', 'module' => 'Core'],
            ['name' => 'CMS Content', 'slug' => 'cms-content', 'module' => 'Cms'],
            ['name' => 'Students', 'slug' => 'students', 'module' => 'School'],
            ['name' => 'Staff', 'slug' => 'staff', 'module' => 'School'],
            ['name' => 'Documents', 'slug' => 'documents', 'module' => 'Core'],
        ];

        foreach ($folders as $folder) {
            \Modules\Core\Models\MediaFolder::withTrashed()->updateOrCreate(
                ['slug' => $folder['slug']],
                array_merge($folder, [
                    'author_id' => $admin->id,
                    'is_shared' => true,
                    'sort_order' => 0,
                ])
            );
        }

        $this->command->info('Global infrastructure seeded successfully!');
    }
}
