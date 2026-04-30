<?php

namespace Modules\School\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\School\Models\Lms\Course;
use Modules\School\Models\Lms\Lesson;
use Modules\School\Models\Lms\Topic;
use Modules\School\Models\Lms\TopicContent\RichText;
use Modules\School\Models\Lms\TopicContent\Video;
use Modules\School\Models\Institution\School;
use Modules\Core\Models\User;

class LmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $school = School::first();
        $author = User::whereHas('roles', fn($q) => $q->where('name', 'super_admin'))->first() ?? User::first();

        if (!$school || !$author) return;

        // 1. Create a Sample Course
        $course = Course::create([
            'school_id' => $school->id,
            'title' => 'Dasar-Dasar Pemrograman Web',
            'slug' => 'dasar-pemrograman-web',
            'summary' => 'Belajar HTML, CSS, dan JavaScript dari nol.',
            'description' => 'Kursus ini dirancang untuk pemula yang ingin terjun ke dunia web development.',
            'level' => 'beginner',
            'status' => 'published',
            'author_id' => $author->id,
        ]);

        // 2. Create Lessons
        $lesson1 = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Pengenalan HTML',
            'order' => 1,
        ]);

        $lesson2 = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Styling dengan CSS',
            'order' => 2,
        ]);

        // 3. Create Topics with Polymorphic Content
        
        // Topic 1: RichText
        $text = RichText::create([
            'value' => '<h1>Selamat Datang!</h1><p>HTML adalah bahasa standar untuk membuat halaman web.</p>'
        ]);
        Topic::create([
            'lesson_id' => $lesson1->id,
            'title' => 'Apa itu HTML?',
            'order' => 1,
            'topicable_type' => RichText::class,
            'topicable_id' => $text->id,
        ]);

        // Topic 2: Video
        $video = Video::create([
            'value' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration' => 212,
        ]);
        Topic::create([
            'lesson_id' => $lesson1->id,
            'title' => 'Struktur Dasar Dokumen HTML',
            'order' => 2,
            'topicable_type' => Video::class,
            'topicable_id' => $video->id,
        ]);
    }
}
