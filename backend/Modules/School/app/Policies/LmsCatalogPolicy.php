<?php

namespace Modules\School\Policies;

use Modules\Core\Models\User;

/**
 * Question banks, exams, results, and questions (LMS catalog outside {@see LmsPolicy} courses).
 */
class LmsCatalogPolicy
{
    /** @return list<string> */
    private static function viewPermissions(): array
    {
        return [
            'view lms',
            'manage lms',
            'create lms',
            'edit lms',
            'delete lms',
            'manage question bank',
        ];
    }

    /** @return list<string> */
    private static function managePermissions(): array
    {
        return [
            'manage lms',
            'create lms',
            'edit lms',
            'delete lms',
            'manage question bank',
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
        return $user->hasAnyPermission(self::managePermissions());
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->hasAnyPermission(self::managePermissions());
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->hasAnyPermission(self::managePermissions());
    }
}
