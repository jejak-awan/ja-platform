<?php

namespace Modules\Cms\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Cms\Models\Category;
use Modules\Cms\Models\Form;
use Modules\Cms\Models\FormField;
use Modules\Cms\Models\Menu;
use Modules\Cms\Models\MenuItem;
use Modules\Cms\Models\Content;
use Modules\Cms\Models\Tag;
use Modules\System\Models\User;

class StudioSeeder extends Seeder
{
    /**
     * Run the studio structure seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', env('SUPER_ADMIN_EMAIL', 'super@jejakawan.com'))->first();
        if (! $admin) {
            return;
        }

        // Always seed Global (null unit) data first for public landing page
        $this->command->info('Seeding global studio data (public landing)...');
        $this->seedUnitData($admin, null);

        // Then seed per-unit data
        $units = \Modules\School\Models\Institution\SchoolUnit::all();
        foreach ($units as $unit) {
            $this->command->info("Seeding data for unit: {$unit->name}");
            $this->seedUnitData($admin, $unit->id);
        }

        $this->command->info('Studio structure seeded successfully!');
    }

    /**
     * Seed data for a specific unit
     */
    private function seedUnitData(User $admin, ?int $unitId): void
    {
        // 1. Categories
        $categories = [
            ['name' => 'Uncategorized', 'slug' => 'uncategorized', 'description' => 'Default category'],
            ['name' => 'Tutorials', 'slug' => 'tutorials', 'description' => 'Helpful guides and walkthroughs'],
            ['name' => 'News', 'slug' => 'news', 'description' => 'Latest updates and announcements'],
            ['name' => 'Design', 'slug' => 'design', 'description' => 'UI/UX and design inspiration'],
        ];

        foreach ($categories as $cat) {
            $category = Category::withoutGlobalScopes()->withTrashed()->updateOrCreate(
                ['slug' => $cat['slug'], 'workspace_id' => $unitId],
                array_merge($cat, [
                    'author_id' => $admin->id,
                    'workspace_id' => $unitId
                ])
            );
            if ($category->trashed()) {
                $category->restore();
            }
        }

        // 2. CMS Pages (Wadah)
        $pages = [
            ['title' => 'Akademik', 'slug' => 'akademik'],
            ['title' => 'Jurusan', 'slug' => 'jurusan'],
            ['title' => 'Prestasi', 'slug' => 'prestasi'],
            ['title' => 'PPDB', 'slug' => 'ppdb'],
            ['title' => 'Kelulusan', 'slug' => 'graduation'],
            ['title' => 'Berita', 'slug' => 'blog'],
            ['title' => 'Kontak', 'slug' => 'contact'],
        ];

        $pageMap = [];
        foreach ($pages as $p) {
            $page = Content::withoutGlobalScopes()->updateOrCreate(
                ['slug' => $p['slug'], 'workspace_id' => $unitId],
                array_merge($p, [
                    'type' => 'page',
                    'status' => 'published',
                    'author_id' => $admin->id,
                    'workspace_id' => $unitId,
                    'published_at' => now(),
                ])
            );
            $pageMap[$p['slug']] = $page->id;
        }

        // 3. Header Menu
        $mainMenu = Menu::withoutGlobalScopes()->withTrashed()
            ->where('location', 'header')
            ->where('workspace_id', $unitId)
            ->first();

        if (! $mainMenu) {
            $mainMenu = Menu::create([
                'name' => 'Header Primary Navigation',
                'slug' => 'menu-header-primary',
                'location' => 'header',
                'is_active' => true,
                'workspace_id' => $unitId,
            ]);
        } elseif ($mainMenu->trashed()) {
            $mainMenu->restore();
        }

        // Cleanup items
        $mainMenu->items()->forceDelete();

        $menuItems = [
            ['title' => 'Beranda', 'url' => '/', 'sort_order' => 1, 'type' => 'custom'],
            [
                'title' => 'Akademik',
                'type' => 'page',
                'target_id' => $pageMap['akademik'] ?? null,
                'url' => '/akademik',
                'sort_order' => 2,
                'children' => [
                    [
                        'title' => 'Jurusan',
                        'type' => 'page',
                        'target_id' => $pageMap['jurusan'] ?? null,
                        'url' => '/jurusan',
                        'sort_order' => 1
                    ],
                    ['title' => 'Karir & BKK', 'url' => '/karir', 'sort_order' => 2, 'type' => 'custom'],
                ]
            ],
            [
                'title' => 'Prestasi',
                'type' => 'page',
                'target_id' => $pageMap['prestasi'] ?? null,
                'url' => '/prestasi',
                'sort_order' => 3
            ],
            [
                'title' => 'PPDB',
                'type' => 'page',
                'target_id' => $pageMap['ppdb'] ?? null,
                'url' => '/ppdb',
                'sort_order' => 4
            ],
            [
                'title' => 'Kelulusan',
                'type' => 'page',
                'target_id' => $pageMap['graduation'] ?? null,
                'url' => '/graduation',
                'sort_order' => 5
            ],
            [
                'title' => 'Berita',
                'type' => 'page',
                'target_id' => $pageMap['blog'] ?? null,
                'url' => '/blog',
                'sort_order' => 6
            ],
            [
                'title' => 'Kontak',
                'type' => 'page',
                'target_id' => $pageMap['contact'] ?? null,
                'url' => '/contact',
                'sort_order' => 7
            ],
        ];

        $this->seedMenuItems($mainMenu, $menuItems);

        // 4. Default public contact form
        $contactForm = Form::withoutGlobalScopes()->withTrashed()->updateOrCreate(['slug' => 'contact', 'workspace_id' => $unitId], [
            'name' => 'Formulir Kontak Publik',
            'description' => 'Formulir untuk pertanyaan umum, PPDB, dan kerja sama.',
            'success_message' => 'Terima kasih! Pesan Anda telah terkirim.',
            'is_active' => true,
            'author_id' => $admin->id,
            'workspace_id' => $unitId,
            'settings' => [
                'email_notifications' => true,
                'notification_email' => $admin->email,
            ],
        ]);
        if ($contactForm->trashed()) {
            $contactForm->restore();
        }

        $fieldDefs = [
            ['name' => 'first_name', 'label' => 'Nama Depan', 'type' => 'text', 'is_required' => true, 'sort_order' => 1],
            ['name' => 'last_name', 'label' => 'Nama Belakang', 'type' => 'text', 'is_required' => true, 'sort_order' => 2],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'is_required' => true, 'sort_order' => 3],
            ['name' => 'message', 'label' => 'Pesan', 'type' => 'textarea', 'is_required' => true, 'sort_order' => 4],
        ];

        foreach ($fieldDefs as $def) {
            FormField::updateOrCreate(
                ['form_id' => $contactForm->id, 'name' => $def['name']],
                array_merge($def, ['form_id' => $contactForm->id])
            );
        }
    }

    /**
     * Recursively seed menu items
     */
    private function seedMenuItems($menu, array $items, $parentId = null): void
    {
        foreach ($items as $itemData) {
            $children = $itemData['children'] ?? [];
            unset($itemData['children']);

            $type = $itemData['type'] ?? 'custom';
            $targetType = null;

            if ($type === 'page') {
                $targetType = 'Modules\Cms\Models\Content';
            } elseif ($type === 'category') {
                $targetType = 'Modules\Cms\Models\Category';
            }

            $menuItem = $menu->items()->create(array_merge($itemData, [
                'parent_id' => $parentId,
                'type' => $type,
                'target_type' => $targetType
            ]));

            if (!empty($children)) {
                $this->seedMenuItems($menu, $children, $menuItem->id);
            }
        }
    }
}
