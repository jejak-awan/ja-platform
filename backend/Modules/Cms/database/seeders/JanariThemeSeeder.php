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
        $cat = Category::updateOrCreate(['slug' => 'news-announcement'], [
            'name' => 'News & Announcements',
            'slug' => 'news-announcement',
            'author_id' => $user->id,
            'is_active' => true
        ]);

        $news = [
            [
                'title' => 'Siswa PPLG Raih Juara 1 Hackathon Nasional 2026',
                'excerpt' => 'Prestasi membanggakan kembali ditorehkan oleh siswa jurusan PPLG dalam ajang kompetisi coding tingkat nasional.',
                'image' => '/assets/themes/janari/news-placeholder.png',
                'category' => 'PRESTASI'
            ],
            [
                'title' => 'Workshop AI & Machine Learning bersama Google Developer Expert',
                'excerpt' => 'Meningkatkan kompetensi siswa di bidang kecerdasan buatan melalui workshop intensif.',
                'image' => '/assets/themes/janari/news-placeholder.png',
                'category' => 'AKADEMIK'
            ],
            [
                'title' => 'Pendaftaran Peserta Didik Baru (PPDB) 2026 Resmi Dibuka',
                'excerpt' => 'Segera daftarkan diri Anda dan raih masa depan gemilang bersama kami.',
                'image' => '/assets/themes/janari/news-placeholder.png',
                'category' => 'PPDB'
            ]
        ];

        foreach ($news as $item) {
            Content::updateOrCreate(['slug' => Str::slug($item['title'])], [
                'title' => $item['title'],
                'excerpt' => $item['excerpt'],
                'type' => 'post',
                'status' => 'published',
                'featured_image' => $item['image'],
                'category_id' =>  $cat->id,
                'author_id' => $user->id,
                'published_at' => now(),
                'meta' => ['category_label' => $item['category']]
            ]);
        }
    }

    private function seedMajors($user)
    {
        $cat = Category::updateOrCreate(['slug' => 'academic-programs'], [
            'name' => 'Academic Programs',
            'slug' => 'academic-programs',
            'author_id' => $user->id,
            'is_active' => true
        ]);

        $programs = [
            ['title' => 'PPLG', 'icon' => 'Monitor', 'desc' => 'Software engineering, Web & Mobile development, Game development.'],
            ['title' => 'TJKT', 'icon' => 'Cpu', 'desc' => 'Cyber security, Cloud computing, & Computer networking.'],
            ['title' => 'DKV', 'icon' => 'Layers', 'desc' => 'Graphic design, Videography, Motion graphics, & Branding.'],
            ['title' => 'AKL', 'icon' => 'Briefcase', 'desc' => 'Accounting, Financial literacy, & Modern business management.'],
        ];

        foreach ($programs as $item) {
            Content::updateOrCreate(['slug' => Str::slug($item['title'])], [
                'title' => $item['title'],
                'excerpt' => $item['desc'],
                'type' => 'post',
                'status' => 'published',
                'category_id' => $cat->id,
                'author_id' => $user->id,
                'published_at' => now(),
                'meta' => ['program_icon' => $item['icon']]
            ]);
        }
    }

    private function seedStats($user)
    {
        $cat = Category::updateOrCreate(['slug' => 'school-stats'], [
            'name' => 'School Stats',
            'slug' => 'school-stats',
            'author_id' => $user->id,
            'is_active' => true
        ]);

        $stats = [
            ['label' => 'Siswa Aktif', 'value' => '1.200+', 'raw' => 1200, 'suffix' => '+'],
            ['label' => 'Partner Industri', 'value' => '85+', 'raw' => 85, 'suffix' => '+'],
            ['label' => 'Lulusan Terserap', 'value' => '92%', 'raw' => 92, 'suffix' => '%'],
            ['label' => 'Prestasi Juara', 'value' => '450+', 'raw' => 450, 'suffix' => '+'],
        ];

        foreach ($stats as $item) {
            Content::updateOrCreate(['slug' => Str::slug($item['label'])], [
                'title' => $item['label'],
                'type' => 'post',
                'status' => 'published',
                'category_id' => $cat->id,
                'author_id' => $user->id,
                'published_at' => now(),
                'meta' => [
                    'stat_value' => $item['value'],
                    'stat_raw' => $item['raw'],
                    'stat_suffix' => $item['suffix']
                ]
            ]);
        }
    }

    private function seedTestimonials($user)
    {
        $cat = Category::updateOrCreate(['slug' => 'testimonials'], [
            'name' => 'Testimonials',
            'slug' => 'testimonials',
            'author_id' => $user->id,
            'is_active' => true
        ]);

        $testimonials = [
            ['name' => 'Dr. Ahmad Fauzi', 'role' => 'Praktisi Industri (Telkom)', 'body' => 'Lulusan dari sekolah ini memiliki kompetensi teknis yang sangat relevan dengan kebutuhan industri masa kini.'],
            ['name' => 'Siska Amelia', 'role' => 'Alumni (Google SWE)', 'body' => 'Berkat bimbingan guru-guru yang hebat, saya bisa mencapai cita-cita saya bekerja di industri teknologi global.'],
        ];

        foreach ($testimonials as $item) {
            Content::updateOrCreate(['slug' => Str::slug($item['name'])], [
                'title' => $item['name'],
                'body' => $item['body'],
                'excerpt' => $item['role'],
                'type' => 'post',
                'status' => 'published',
                'category_id' => $cat->id,
                'author_id' => $user->id,
                'published_at' => now(),
            ]);
        }
    }

    private function seedPartners($user)
    {
        $cat = Category::updateOrCreate(['slug' => 'industry-partners'], [
            'name' => 'Industry Partners',
            'slug' => 'industry-partners',
            'author_id' => $user->id,
            'is_active' => true
        ]);

        for ($i = 1; $i <= 6; $i++) {
            Content::updateOrCreate(['slug' => "partner-brand-$i"], [
                'title' => "Partner Brand $i",
                'type' => 'post',
                'status' => 'published',
                'category_id' => $cat->id,
                'author_id' => $user->id,
                'published_at' => now(),
            ]);
        }
    }
}
