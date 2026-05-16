<?php

declare(strict_types=1);

namespace Modules\School\Policies;

use Modules\System\Models\User;

/**
 * Attendance records under {@see OperationController} (operations/attendance/*).
 */
class OperationsAttendancePolicy
{
    /** @return list<string> */
    private function viewPermissions(): array
    {
        return [
            'view attendance',
            'manage attendance',
            'view students',
            'manage students',
        ];
    }

    /** @return list<string> */
    private function mutatePermissions(): array
    {
        return [
            'manage attendance',
            'manage students',
        ];
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission($this->viewPermissions());
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->hasAnyPermission($this->viewPermissions());
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission($this->mutatePermissions());
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->hasAnyPermission($this->mutatePermissions());
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->hasAnyPermission($this->mutatePermissions());
    }
}
