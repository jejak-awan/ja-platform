<?php

namespace Modules\Cms\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Cms\Models\Content;
use Modules\Cms\Models\Theme;

class CmsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Theme
        Theme::firstOrCreate(
            ['slug' => 'default-modern'],
            [
                'name' => 'Default Modern Theme',
                'type' => 'frontend',
                'path' => 'themes/default-modern',
                'version' => '1.0.0',
                'is_active' => true,
                'settings' => [
                    'primary_color' => '#4F46E5',
                    'font_family' => 'Inter',
                ]
            ]
        );

        // 2. Create Sample Pages
        $pages = [
            [
                'title' => 'Welcome to JA-Platform',
                'slug' => 'home',
                'body' => '<h1>Welcome</h1><p>This is your new home page.</p>',
                'type' => 'page',
                'status' => 'published',
            ],
            [
                'title' => 'About Us',
                'slug' => 'about',
                'body' => '<h1>About</h1><p>We are a modular platform.</p>',
                'type' => 'page',
                'status' => 'published',
            ]
        ];

        foreach ($pages as $page) {
            Content::updateOrCreate(
                ['slug' => $page['slug'], 'type' => 'page'],
                array_merge($page, ['author_id' => 1])
            );
        }
    }
}
