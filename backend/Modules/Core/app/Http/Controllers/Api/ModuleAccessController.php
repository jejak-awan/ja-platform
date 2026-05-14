<?php

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Core\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Manage module-scoped access (RBAC) without granting Core governance.
 *
 * Scope strategy (phase 1):
 * - CMS roles must be prefixed: `cms:...`
 * - School roles are treated as "non-core, non-cms" roles (legacy)
 */
class ModuleAccessController extends BaseApiController
{
    private const MODULES = ['cms', 'school'];

    public function roles(Request $request, string $module): \Illuminate\Http\JsonResponse
    {
        if (! in_array($module, self::MODULES, true)) {
            return $this->notFound('Module');
        }

        $roles = $this->queryScopedRoles($module)->with('permissions')->orderBy('name')->get();

        return $this->success($roles, 'Module roles retrieved successfully');
    }

    public function users(Request $request, string $module): \Illuminate\Http\JsonResponse
    {
        if (! in_array($module, self::MODULES, true)) {
            return $this->notFound('Module');
        }

        $perPageRaw = $request->input('per_page', 20);
        $perPage = min(max(is_numeric($perPageRaw) ? (int) $perPageRaw : 20, 1), 100);

        $query = User::query()->with('roles')->orderByDesc('id');

        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? $searchRaw : '';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($perPage);
        $paginator->getCollection()->transform(function (User $user) use ($module) {
            /** @var \Illuminate\Support\Collection<int, Role> $roles */
            $roles = $user->roles;
            $scoped = $roles->filter(fn (Role $r) => $this->roleMatchesModule($r, $module))->values();
            $user->setRelation('roles', $scoped);
            $user->setRelation('permissions', $user->getAllPermissions());

            return $user;
        });

        return $this->paginated($paginator, 'Module users retrieved successfully');
    }

    public function updateUserRoles(Request $request, string $module, User $user): \Illuminate\Http\JsonResponse
    {
        if (! in_array($module, self::MODULES, true)) {
            return $this->notFound('Module');
        }

        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'string',
        ]);

        $requestedNames = array_values(array_filter($validated['roles'], fn ($v) => is_string($v) && $v !== ''));

        $allowed = $this->queryScopedRoles($module)->whereIn('name', $requestedNames)->get()->pluck('name')->all();

        // Keep non-module roles intact; replace only module-scoped roles.
        $existing = $user->getRoleNames()->filter(fn ($name) => is_string($name))->values()->all();
        $kept = array_values(array_filter($existing, function (string $name) use ($module) {
            $role = Role::where('name', $name)->first();
            if (! $role) {
                return false;
            }
            return ! $this->roleMatchesModule($role, $module);
        }));

        /** @var array<int, string> $kept */
        $kept = array_values(array_filter($kept, fn ($v) => $v !== ''));
        /** @var array<int, string> $allowed */
        $allowed = array_values(array_filter($allowed, fn ($v) => is_string($v) && $v !== ''));

        $user->syncRoles(array_values(array_unique(array_merge($kept, $allowed))));
        $user->load('roles');
        $user->setRelation('permissions', $user->getAllPermissions());

        return $this->success($user, 'Module roles updated successfully');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Role>
     */
    private function queryScopedRoles(string $module): \Illuminate\Database\Eloquent\Builder
    {
        if ($module === 'cms') {
            return Role::query()->where('name', 'like', 'cms:%');
        }

        // School: legacy role names (no prefix) but must exclude core + cms.
        return Role::query()->where(function ($q) {
            $q->where('name', 'not like', 'cms:%')
                ->whereNotIn('name', [
                    'super',
                    'system-admin',
                    'security-officer',
                    'admin',
                    'editor',
                    'operator',
                    'member',
                ]);
        });
    }

    private function roleMatchesModule(Role $role, string $module): bool
    {
        if ($module === 'cms') {
            return str_starts_with($role->name, 'cms:');
        }

        return ! str_starts_with($role->name, 'cms:')
            && ! in_array($role->name, ['super', 'system-admin', 'security-officer', 'admin', 'editor', 'operator', 'member'], true);
    }
}

