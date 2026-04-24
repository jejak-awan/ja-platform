<?php

namespace Modules\School\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\School\Models\Academic\Attendance;

class AttendanceMarked
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Attendance $attendance;

    /**
     * Create a new event instance.
     */
    public function __construct(Attendance $attendance)
    {
        $this->attendance = $attendance;
    }
}
