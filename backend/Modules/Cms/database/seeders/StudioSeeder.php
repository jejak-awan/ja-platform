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
use Modules\Core\Models\Tag;
use Modules\Core\Models\User;

class StudioSeeder extends Seeder
{
    /**
     * Run the studio structure seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@kdua.net')->first();
        if (! $admin) {
            return;
        }

        // 1. Categories
        $categories = [
            ['name' => 'Uncategorized', 'slug' => 'uncategorized', 'description' => 'Default category'],
            ['name' => 'Tutorials', 'slug' => 'tutorials', 'description' => 'Helpful guides and walkthroughs'],
            ['name' => 'News', 'slug' => 'news', 'description' => 'Latest updates and announcements'],
            ['name' => 'Design', 'slug' => 'design', 'description' => 'UI/UX and design inspiration'],
        ];

        foreach ($categories as $cat) {
            // Include soft-deleted rows so we update instead of inserting duplicate slugs (unique index).
            $category = Category::withTrashed()->updateOrCreate(
                ['slug' => $cat['slug']],
                array_merge($cat, ['author_id' => $admin->id])
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
            $page = Content::updateOrCreate(['slug' => $p['slug']], array_merge($p, [
                'type' => 'page',
                'status' => 'published',
                'author_id' => $admin->id,
                'published_at' => now(),
            ]));
            $pageMap[$p['slug']] = $page->id;
        }

        // 3. Header Menu (reuse existing active header menu if available)
        $mainMenu = Menu::withTrashed()
            ->where('location', 'header')
            ->orderByDesc('is_active')
            ->orderBy('id')
            ->first();

        if (! $mainMenu) {
            $mainMenu = Menu::create([
                'name' => 'Header Primary Navigation',
                'slug' => 'menu-header-primary',
                'location' => 'header',
                'is_active' => true,
            ]);
        } elseif ($mainMenu->trashed()) {
            $mainMenu->restore();
        }

        // Cleanup: Remove existing items to avoid duplicates from previous seeder versions
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

        // 4. Default public contact form (slug must match theme setting contact_form_slug, default "contact")
        /** @var Form $contactForm */
        $contactForm = Form::withTrashed()->updateOrCreate(['slug' => 'contact'], [
            'name' => 'Formulir Kontak Publik',
            'description' => 'Formulir untuk pertanyaan umum, PPDB, dan kerja sama. Tanpa unggah berkas — arahkan pengunjung ke email/WA di blok informasi.',
            'success_message' => 'Terima kasih! Pesan Anda telah terkirim. Tim kami akan menghubungi Anda segera.',
            'redirect_url' => null,
            'is_active' => true,
            'author_id' => $admin->id,
            'settings' => [
                'email_notifications' => true,
                'notification_email' => $admin->email,
            ],
        ]);
        if ($contactForm->trashed()) {
            $contactForm->restore();
        }

        $fieldDefs = [
            [
                'name' => 'first_name',
                'label' => 'Nama Depan',
                'type' => 'text',
                'placeholder' => 'Nama depan',
                'help_text' => null,
                'options' => null,
                'validation_rules' => ['string', 'max:120'],
                'is_required' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'last_name',
                'label' => 'Nama Belakang',
                'type' => 'text',
                'placeholder' => 'Nama belakang',
                'help_text' => null,
                'options' => null,
                'validation_rules' => ['string', 'max:120'],
                'is_required' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'placeholder' => 'nama@email.com',
                'help_text' => null,
                'options' => null,
                'validation_rules' => ['max:255'],
                'is_required' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'message',
                'label' => 'Pesan',
                'type' => 'textarea',
                'placeholder' => 'Tuliskan pertanyaan atau pesan Anda…',
                'help_text' => null,
                'options' => null,
                'validation_rules' => ['string', 'max:5000'],
                'is_required' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($fieldDefs as $def) {
            FormField::updateOrCreate(
                [
                    'form_id' => $contactForm->id,
                    'name' => $def['name'],
                ],
                array_merge($def, ['form_id' => $contactForm->id])
            );
        }

        $this->command->info('Studio structure seeded successfully!');
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
