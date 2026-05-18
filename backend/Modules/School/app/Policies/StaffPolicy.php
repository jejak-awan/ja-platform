<?php

declare(strict_types=1);

namespace Modules\School\Policies;

use Modules\School\Models\HR\Staff;
use Modules\System\Models\User;

class StaffPolicy
{
    /**
     * Determine if the user can view any staff.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view staff', 'manage staff']);
    }

    /**
     * Determine if the user can view the staff member.
     */
    public function view(User $user, Staff $staff): bool
    {
        // Staff can view their own data
        if ($user->id === $staff->user_id) {
            return true;
        }

        return $user->hasAnyPermission(['view staff', 'manage staff']);
    }

    /**
     * Determine if the user can create staff members.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['create staff', 'manage staff']);
    }

    /**
     * Determine if the user can update the staff member.
     */
    public function update(User $user, Staff $staff): bool
    {
        return $user->hasAnyPermission(['edit staff', 'manage staff']);
    }

    /**
     * Determine if the user can delete the staff member.
     */
    public function delete(User $user, Staff $staff): bool
    {
        return $user->hasAnyPermission(['delete staff', 'manage staff']);
    }
}
