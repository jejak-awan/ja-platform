<?php

namespace Modules\School\Policies;

use Modules\Core\Models\User;
use Modules\School\Models\Lms\Course;

class LmsPolicy
{
    /**
     * Determine if the user can view any LMS resources.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view lms', 'manage lms']);
    }

    /**
     * Course enrollment (ability name matches controller authorize call).
     */
    public function enroll(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Teacher / admin course statistics.
     */
    public function viewStats(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine if the user can view the course.
     */
    public function view(User $user, Course $course): bool
    {
        return $user->hasAnyPermission(['view lms', 'manage lms']);
    }

    /**
     * Determine if the user can create courses.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['create lms', 'manage lms'])
            || $user->hasAnyRole(['guru', 'admin-kurikulum']);
    }

    /**
     * Determine if the user can update the course.
     */
    public function update(User $user, Course $course): bool
    {
        // Authors can update their own courses
        if ($user->id === $course->author_id) {
            return true;
        }

        return $user->hasAnyPermission(['edit lms', 'manage lms']);
    }

    /**
     * Determine if the user can delete the course.
     */
    public function delete(User $user, Course $course): bool
    {
        // Authors can delete their own courses
        if ($user->id === $course->author_id) {
            return true;
        }

        return $user->hasAnyPermission(['delete lms', 'manage lms']);
    }

    /**
     * Determine if the user can manage question banks.
     */
    public function manageQuestionBanks(User $user): bool
    {
        return $user->hasAnyPermission(['manage lms'])
            || $user->hasAnyRole(['guru', 'admin-kurikulum']);
    }

    /**
     * Determine if the user can manage exams.
     */
    public function manageExams(User $user): bool
    {
        return $user->hasAnyPermission(['manage lms'])
            || $user->hasAnyRole(['guru', 'admin-kurikulum']);
    }
}
