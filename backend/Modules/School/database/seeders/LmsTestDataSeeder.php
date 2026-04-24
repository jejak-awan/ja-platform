<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\School\Models\Lms\Course;
use Modules\School\Models\Lms\Section;
use Modules\School\Models\Lms\Lesson;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Academic\Subject;
use Modules\Core\Models\User;
use Illuminate\Support\Str;

class LmsTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $school = School::first() ?? School::create(['name' => 'Default School']);
        $subject = Subject::first() ?? Subject::create(['name' => 'Computer Science', 'school_id' => $school->id]);
        $author = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first() ?? User::first();

        if (!$author) {
            $this->command->error('No user found for authoring courses.');
            return;
        }

        // --- Demo Course 1: UI/UX Design Fundamentals ---
        $course1 = Course::create([
            'school_id' => $school->id,
            'subject_id' => $subject->id,
            'author_id' => $author->id,
            'title' => 'UI/UX Design Fundamentals',
            'slug' => 'ui-ux-design-fundamentals',
            'description' => 'Master the basics of User Interface and User Experience design. Learn how to create stunning, human-centered designs from scratch.',
            'thumbnail' => '/assets/themes/janari/news-placeholder.png',
            'status' => 'published',
            'level' => 'beginner',
        ]);

        $section1 = Section::create([
            'course_id' => $course1->id,
            'title' => 'Introduction to Design Thinking',
            'sort_order' => 1,
        ]);

        Lesson::create([
            'section_id' => $section1->id,
            'title' => 'What is UI vs UX?',
            'slug' => 'what-is-ui-vs-ux',
            'type' => 'video',
            'video_url' => 'https://www.youtube.com/watch?v=zHAa-m16NGk',
            'content' => 'In this lesson, we explore the fundamental differences between User Interface (UI) and User Experience (UX).',
            'duration' => 10,
            'sort_order' => 1,
            'is_preview' => true,
        ]);

        Lesson::create([
            'section_id' => $section1->id,
            'title' => 'Design Principles Overview',
            'slug' => 'design-principles',
            'type' => 'text',
            'content' => '### Core Design Principles\n\n1. **Contrast**: Making elements stand out.\n2. **Repetition**: Consistency across the interface.\n3. **Alignment**: Creating visual order.\n4. **Proximity**: Grouping related items.',
            'duration' => 15,
            'sort_order' => 2,
        ]);

        // --- Demo Course 2: Advanced Laravel Architectures ---
        $course2 = Course::create([
            'school_id' => $school->id,
            'subject_id' => $subject->id,
            'author_id' => $author->id,
            'title' => 'Advanced Laravel Architectures',
            'slug' => 'advanced-laravel-architectures',
            'description' => 'Deep dive into Design Patterns, SOLID principles, and Scalable Architectures using the Laravel Framework.',
            'thumbnail' => '/assets/themes/janari/news-placeholder.png',
            'status' => 'published',
            'level' => 'advanced',
        ]);

        $section2 = Section::create([
            'course_id' => $course2->id,
            'title' => 'Service Pattern & Repository',
            'sort_order' => 1,
        ]);

        Lesson::create([
            'section_id' => $section2->id,
            'title' => 'Implementing the Service Layer',
            'slug' => 'implementing-service-layer',
            'type' => 'text',
            'content' => 'The Service Layer pattern helps in keeping your controllers thin and business logic reusable.',
            'duration' => 20,
            'sort_order' => 1,
        ]);
    }
}
