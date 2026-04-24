<?php

namespace Modules\School\Policies;

use Modules\Core\Models\User;

/**
 * Attendance records under {@see OperationController} (operations/attendance/*).
 */
class OperationsAttendancePolicy
{
    /** @return list<string> */
    private static function viewPermissions(): array
    {
        return [
            'view attendance',
            'manage attendance',
            'view students',
            'manage students',
        ];
    }

    /** @return list<string> */
    private static function mutatePermissions(): array
    {
        return [
            'manage attendance',
            'manage students',
        ];
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(self::viewPermissions());
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->hasAnyPermission(self::viewPermissions());
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(self::mutatePermissions());
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->hasAnyPermission(self::mutatePermissions());
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->hasAnyPermission(self::mutatePermissions());
    }
}
