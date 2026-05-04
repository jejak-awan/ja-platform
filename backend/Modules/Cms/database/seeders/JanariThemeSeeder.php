<?php

namespace Modules\Cms\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Cms\Models\Category;
use Modules\Cms\Models\Content;
use Modules\Core\Models\User;

class JanariThemeSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) return;

        $this->seedNews($user);
        $this->seedMajors($user);
        $this->seedStats($user);
        $this->seedTestimonials($user);
        $this->seedPartners($user);
    }

    private function seedNews($user)
    {
        Category::updateOrCreate(['slug' => 'news-announcement'], [
            'name' => 'News & Announcements',
            'slug' => 'news-announcement',
            'author_id' => $user->id,
            'is_active' => true
        ]);
        // No sample news for production
    }

    private function seedMajors($user)
    {
        Category::updateOrCreate(['slug' => 'academic-programs'], [
            'name' => 'Academic Programs',
            'slug' => 'academic-programs',
            'author_id' => $user->id,
            'is_active' => true
        ]);
        // No sample programs for production
    }

    private function seedStats($user)
    {
        Category::updateOrCreate(['slug' => 'school-stats'], [
            'name' => 'School Stats',
            'slug' => 'school-stats',
            'author_id' => $user->id,
            'is_active' => true
        ]);
        // No sample stats for production
    }

    private function seedTestimonials($user)
    {
        Category::updateOrCreate(['slug' => 'testimonials'], [
            'name' => 'Testimonials',
            'slug' => 'testimonials',
            'author_id' => $user->id,
            'is_active' => true
        ]);
        // No sample testimonials for production
    }

    private function seedPartners($user)
    {
        Category::updateOrCreate(['slug' => 'industry-partners'], [
            'name' => 'Industry Partners',
            'slug' => 'industry-partners',
            'author_id' => $user->id,
            'is_active' => true
        ]);
        // No sample partners for production
    }
}
