<?php

namespace Modules\School\Policies;

use Modules\System\Models\User;
use Modules\School\Models\Academic\TeachingJournal;

class AcademicPolicy
{
    /** @return list<string> */
    private static function viewPermissions(): array
    {
        return [
            'view academic',
            'manage academic',
            'manage curriculum',
            'manage schedule',
            'grade assignments',
            'input journal',
            'manage question bank',
        ];
    }

    /** @return list<string> */
    private static function writePermissions(mixed $model): array
    {
        if ($model instanceof TeachingJournal) {
            return [
                'manage academic',
                'manage curriculum',
                'manage schedule',
                'input journal',
                'manage question bank',
            ];
        }

        return [
            'manage academic',
            'manage curriculum',
            'manage schedule',
            'grade assignments',
            'manage question bank',
        ];
    }

    /** @return list<string> */
    private static function deletePermissions(mixed $model): array
    {
        if ($model instanceof TeachingJournal) {
            return [
                'manage academic',
                'manage curriculum',
                'manage schedule',
                'input journal',
            ];
        }

        return [
            'manage academic',
            'manage curriculum',
            'manage schedule',
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
        return $user->hasAnyPermission(self::writePermissions(null));
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->hasAnyPermission(self::writePermissions($model));
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->hasAnyPermission(self::deletePermissions($model));
    }
}
