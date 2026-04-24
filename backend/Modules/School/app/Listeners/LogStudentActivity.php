<?php

namespace Modules\School\Listeners;

use Modules\School\Events\StudentCreated;
use Modules\School\Events\StudentDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogStudentActivity
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the StudentCreated event.
     */
    public function handleStudentCreated(StudentCreated $event): void
    {
        Log::channel('school_audit')->info('Student registered: ' . $event->student->nisn, [
            'student_id' => $event->student->id,
            'school_id' => $event->student->school_id,
            'name' => $event->student->full_name,
            'user' => auth()->id() ?? 'system'
        ]);
        
        // Clear related caches
        \Illuminate\Support\Facades\Cache::forget("school_stats_{$event->student->school_id}");
    }

    /**
     * Handle the StudentDeleted event.
     */
    public function handleStudentDeleted(StudentDeleted $event): void
    {
        Log::channel('school_audit')->info('Student deleted/removed: ' . $event->student->nisn, [
            'student_id' => $event->student->id,
            'school_id' => $event->student->school_id,
            'name' => $event->student->full_name,
            'user' => auth()->id() ?? 'system'
        ]);
        
        // Clear related caches
        \Illuminate\Support\Facades\Cache::forget("school_stats_{$event->student->school_id}");
    }
}
