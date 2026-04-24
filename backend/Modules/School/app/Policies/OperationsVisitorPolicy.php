<?php

namespace Modules\School\Policies;

use Modules\Core\Models\User;

/**
 * Visitor book ({@see OperationController} operations/visitors/*).
 */
class OperationsVisitorPolicy
{
    /** @return list<string> */
    private static function permissions(): array
    {
        return [
            'view visitors',
            'manage visitors',
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
