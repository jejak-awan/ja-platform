<?php

declare(strict_types=1);

namespace Modules\School\Policies;

use Modules\School\Models\Academic\TeachingJournal;
use Modules\System\Models\User;

class AcademicPolicy
{
    /** @return list<string> */
    private function viewPermissions(): array
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
    private function writePermissions(mixed $model): array
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
    private function deletePermissions(mixed $model): array
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
        return $user->hasAnyPermission($this->viewPermissions());
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->hasAnyPermission($this->viewPermissions());
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission($this->writePermissions(null));
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->hasAnyPermission($this->writePermissions($model));
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->hasAnyPermission($this->deletePermissions($model));
    }
}
