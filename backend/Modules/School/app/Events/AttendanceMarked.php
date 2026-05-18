<?php

declare(strict_types=1);

namespace Modules\School\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\School\Models\Academic\Attendance;

class AttendanceMarked
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Attendance $attendance) {}
}
