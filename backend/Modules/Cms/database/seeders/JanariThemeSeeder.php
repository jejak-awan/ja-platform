<?php

namespace Modules\Cms\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Modules\Cms\Models\Category;
use Modules\Cms\Models\Content;
use Modules\Cms\Models\Theme;
use Modules\System\Models\User;

class JanariThemeSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) return;

        $this->seedThemeSettings();
        $this->seedCategories($user);
        $this->seedEssentialContent($user);
    }

    private function seedThemeSettings()
    {
        $theme = Theme::withoutGlobalScopes()->whereIn('slug', ['janari', 'janari-education'])->first();
        if (!$theme) return;

        $manifestPath = base_path('../frontend/src/modules/Cms/views/themes/janari/theme.json');
        
        if (File::exists($manifestPath)) {
            $manifest = json_decode(File::get($manifestPath), true);
            $settings = $theme->settings ?? [];

            if (isset($manifest['settings_schema'])) {
                foreach ($manifest['settings_schema'] as $key => $schema) {
                    if (isset($schema['default']) && !isset($settings[$key])) {
                        $settings[$key] = $schema['default'];
                    }
                }
            }

            $settings['site_title'] = $settings['site_title'] ?? 'SEKOLAHK2ID';
            $settings['hero_title'] = $settings['hero_title'] ?? 'SEKOLAHK2ID';
            $settings['hero_subtitle'] = $settings['hero_subtitle'] ?? 'Mencetak Generasi Unggul Siap Kerja, Kuliah, dan Berwirausaha';
            $settings['brand_logo'] = $settings['brand_logo'] ?? '/logo.png';
            $settings['brand_favicon'] = $settings['brand_favicon'] ?? '/favicon.ico';

            $theme->update(['settings' => $settings]);
        }
    }

    private function seedCategories($user)
    {
        $categories = [
            'news-announcement' => 'News & Announcements',
            'academic-programs' => 'Academic Programs',
            'school-stats' => 'School Stats',
            'testimonials' => 'Testimonials',
            'industry-partners' => 'Industry Partners',
        ];

        foreach ($categories as $slug => $name) {
            Category::withTrashed()->withoutGlobalScopes()->updateOrCreate(
                ['slug' => $slug, 'workspace_id' => null],
                [
                    'name' => $name,
                    'author_id' => $user->id,
                    'is_active' => true
                ]
            );
        }
    }

    private function seedEssentialContent($user)
    {
        // 1. Essential Home Page Record (Global)
        Content::withTrashed()->withoutGlobalScopes()->updateOrCreate(
            ['slug' => 'home', 'workspace_id' => null],
            [
                'title' => 'Home',
                'type' => 'page',
                'status' => 'published',
                'author_id' => $user->id,
                'body' => 'Welcome to JA-Platform. This is a clean production foundation.',
            ]
        );
    }
}
