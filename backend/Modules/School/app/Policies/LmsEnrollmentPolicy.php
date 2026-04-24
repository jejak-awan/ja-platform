<?php

namespace Modules\School\Policies;

use Modules\Core\Models\User;
use Modules\School\Models\Lms\Enrollment;

class LmsEnrollmentPolicy
{
    public function update(User $user, Enrollment $enrollment): bool
    {
        return $user->hasAnyPermission(['view lms', 'manage lms', 'edit lms']);
    }
}
