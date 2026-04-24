<?php

namespace Modules\School\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\School\Models\Student\Student;

class StudentCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Student $student;

    /**
     * Create a new event instance.
     */
    public function __construct(Student $student)
    {
        $this->student = $student;
    }
}
