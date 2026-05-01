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
        $admin = User::where('email', 'admin@kdua.net')->first();
        if (! $admin) {
            return;
        }

        // 1. Standard Global Tags
        $tags = ['CMS', 'SaaS', 'Vue.js', 'Laravel', 'UI/UX', 'Premium', 'Education', 'Technology'];
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

        $this->command->info('Global infrastructure seeded successfully!');
    }
}
