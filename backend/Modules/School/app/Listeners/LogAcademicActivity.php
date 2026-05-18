<?php

namespace Modules\School\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Modules\School\Events\AttendanceMarked;

class LogAcademicActivity implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AttendanceMarked $event): void
    {
        Log::channel('school_audit')->info('Attendance marked', [
            'attendance_id' => $event->attendance->id,
            'student_id' => $event->attendance->student_id,
            'date' => $event->attendance->date,
            'status' => $event->attendance->status,
            'user' => auth()->id() ?? 'system',
        ]);
    }
}
