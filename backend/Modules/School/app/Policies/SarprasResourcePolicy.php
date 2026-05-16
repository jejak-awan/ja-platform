<?php

declare(strict_types=1);

namespace Modules\School\Policies;

use Modules\System\Models\User;

/**
 * Sarpras / facility assets (land, buildings, rooms, school assets, maintenance tickets).
 */
class SarprasResourcePolicy
{
    /** @return list<string> */
    private function viewPermissions(): array
    {
        return [
            'view sarpras',
            'manage sarpras',
            'manage inventory',
            'manage logistics',
        ];
    }

    /** @return list<string> */
    private function mutatePermissions(): array
    {
        return [
            'manage sarpras',
            'manage inventory',
            'manage logistics',
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
