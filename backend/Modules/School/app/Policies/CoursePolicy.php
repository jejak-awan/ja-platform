<?php

declare(strict_types=1);

namespace Modules\School\Policies;

use Modules\System\Models\User;
use Modules\School\Models\Lms\Course;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAtLeastRole('admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Course $course): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Course $course): bool
    {
        return true;
    }

    public function delete(User $user, Course $course): bool
    {
        return true;
    }
}
