<?php

namespace Modules\School\Policies;

use Modules\Core\Models\User;

/**
 * Violations, achievements, counseling ({@see OperationController}).
 */
class OperationsStudentAffairsPolicy
{
    /** @return list<string> */
    private static function permissions(): array
    {
        return [
            'view student affairs',
            'manage student affairs',
            'view students',
            'manage students',
        ];
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(self::permissions());
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->hasAnyPermission(self::permissions());
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(self::permissions());
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->hasAnyPermission(self::permissions());
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->hasAnyPermission(self::permissions());
    }
}
