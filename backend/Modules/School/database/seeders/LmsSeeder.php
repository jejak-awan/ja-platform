<?php

namespace Modules\School\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\School\Models\Lms\Course;
use Modules\School\Models\Lms\Section;
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
        $unit = $school ? \Illuminate\Support\Facades\DB::table('sch_ins_levels')->where('school_id', $school->id)->first() : null;
        $author = User::whereHas('roles', fn($q) => $q->where('name', 'super_admin'))->first() ?? User::first();

        if (!$school || !$unit || !$author) return;

        $workspaceId = $unit->id;

        // 1. Create a Sample Course
        $course = Course::updateOrCreate(
            ['slug' => 'dasar-pemrograman-web', 'workspace_id' => $workspaceId],
            [
                'school_id' => $school->id,
                'workspace_id' => $workspaceId,
                'title' => 'Dasar-Dasar Pemrograman Web',
                'summary' => 'Belajar HTML, CSS, dan JavaScript dari nol.',
                'description' => 'Kursus ini dirancang untuk pemula yang ingin terjun ke dunia web development.',
                'level' => 'beginner',
                'status' => 'published',
                'author_id' => $author->id,
            ]
        );

        // 2. Create Section
        $section = Section::updateOrCreate(
            ['course_id' => $course->id, 'workspace_id' => $workspaceId, 'title' => 'Bab 1: Dasar HTML'],
            ['order' => 1, 'workspace_id' => $workspaceId]
        );

        // 3. Create Lessons
        $lesson1 = Lesson::updateOrCreate(
            [
                'workspace_id' => $workspaceId,
                'section_id' => $section->id,
                'course_id' => $course->id,
                'slug' => 'pengenalan-html-css',
            ],
            [
                'workspace_id' => $workspaceId,
                'title' => 'Pengenalan HTML & CSS',
                'summary' => 'Dasar-dasar struktur web dan styling.',
                'order' => 1,
            ]
        );

        $lesson2 = Lesson::updateOrCreate(
            ['workspace_id' => $workspaceId, 'section_id' => $section->id, 'course_id' => $course->id, 'title' => 'Styling dengan CSS'],
            ['order' => 2, 'slug' => 'styling-dengan-css', 'workspace_id' => $workspaceId]
        );

        // 4. Create Topics with Polymorphic Content
        
        // Topic 1: RichText
        $text = RichText::create([
            'value' => '<h1>Selamat Datang!</h1><p>HTML adalah bahasa standar untuk membuat halaman web.</p>'
        ]);
        Topic::updateOrCreate(
            ['workspace_id' => $workspaceId, 'lesson_id' => $lesson1->id, 'title' => 'Apa itu HTML?'],
            [
                'workspace_id' => $workspaceId,
                'order' => 1,
                'topicable_type' => RichText::class,
                'topicable_id' => $text->id,
            ]
        );

        // Topic 2: Video
        $video = Video::create([
            'value' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration' => 212,
        ]);
        Topic::updateOrCreate(
            ['workspace_id' => $workspaceId, 'lesson_id' => $lesson1->id, 'title' => 'Struktur Dasar Dokumen HTML'],
            [
                'workspace_id' => $workspaceId,
                'order' => 2,
                'topicable_type' => Video::class,
                'topicable_id' => $video->id,
            ]
        );
    }
}
