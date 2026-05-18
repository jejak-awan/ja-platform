<?php

declare(strict_types=1);

namespace Modules\School\Policies;

use Modules\School\Models\Student\Student;
use Modules\System\Models\User;

class StudentPolicy
{
    /**
     * Determine if the user can view any students.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view students', 'manage students']);
    }

    /**
     * Determine if the user can view the student.
     */
    public function view(User $user, Student $student): bool
    {
        // Students can view their own data
        if ($user->id === $student->user_id) {
            return true;
        }

        return $user->hasAnyPermission(['view students', 'manage students']);
    }

    /**
     * Determine if the user can create students.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['create students', 'manage students']);
    }

    /**
     * Determine if the user can update the student.
     */
    public function update(User $user, Student $student): bool
    {
        return $user->hasAnyPermission(['edit students', 'manage students']);
    }

    /**
     * Determine if the user can delete the student.
     */
    public function delete(User $user, Student $student): bool
    {
        return $user->hasAnyPermission(['delete students', 'manage students']);
    }
}
