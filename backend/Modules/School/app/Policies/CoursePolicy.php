<?php

namespace Modules\School\Policies;

use Modules\Core\Models\User;
use Modules\School\Models\Lms\Course;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isAtLeastRole('admin')) {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Course $course)
    {
        return true;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Course $course)
    {
        return true;
    }

    public function delete(User $user, Course $course)
    {
        return true;
    }
}
