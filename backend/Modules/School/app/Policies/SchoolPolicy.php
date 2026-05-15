<?php

namespace Modules\School\Policies;

use Modules\System\Models\User;
use Modules\School\Models\Institution\School;

class SchoolPolicy
{
    /**
     * Determine if the user can view any schools.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view schools', 'manage schools']);
    }

    /**
     * Determine if the user can view the school.
     */
    public function view(User $user, School $school): bool
    {
        return $user->hasAnyPermission(['view schools', 'manage schools']);
    }

    /**
     * Determine if the user can create schools.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['create schools', 'manage schools']);
    }

    /**
     * Determine if the user can update the school.
     */
    public function update(User $user, School $school): bool
    {
        return $user->hasAnyPermission(['edit schools', 'manage schools']);
    }

    /**
     * Determine if the user can delete the school.
     */
    public function delete(User $user, School $school): bool
    {
        return $user->hasAnyRole(['super']);
    }
}
