<?php

namespace Modules\Cms\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Cms\Models\Category;
use Modules\Cms\Models\Form;
use Modules\Cms\Models\FormField;
use Modules\Cms\Models\Menu;
use Modules\Cms\Models\MenuItem;
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

        // 2. Tags
        $tags = ['CMS', 'SaaS', 'Vue.js', 'Laravel', 'UI/UX', 'Premium'];
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

        $menuItems = [
            ['title' => 'Home', 'url' => '/', 'sort_order' => 1],
            ['title' => 'Blog', 'url' => '/blog', 'sort_order' => 2],
            ['title' => 'Services', 'url' => '/services', 'sort_order' => 3],
            ['title' => 'About', 'url' => '/about', 'sort_order' => 4],
            ['title' => 'Contact', 'url' => '/contact', 'sort_order' => 5],
        ];

        foreach ($menuItems as $item) {
            $menuItem = MenuItem::withTrashed()->updateOrCreate(
                [
                    'menu_id' => $mainMenu->id,
                    'title' => $item['title'],
                ],
                array_merge($item, ['menu_id' => $mainMenu->id])
            );
            if ($menuItem->trashed()) {
                $menuItem->restore();
            }
        }

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
}
