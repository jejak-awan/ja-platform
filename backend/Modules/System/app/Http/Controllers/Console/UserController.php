<?php

namespace Modules\System\Http\Controllers\Console;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\System\Helpers\IpHelper;
use Modules\System\Models\User;
use Modules\Security\Rules\StrongPassword;

class UserController extends \Modules\System\Http\Controllers\BaseApiController
{
    /**
     * List users with filters.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = User::with(['roles', 'permissions']);

        // Soft deletes filter
        if ($request->has('trashed')) {
            $trashed = $request->input('trashed');
            if ($trashed === 'only') {
                $query->onlyTrashed();
            } elseif ($trashed === 'with') {
                $query->withTrashed();
            }
        }

        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? $searchRaw : '';
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request): void {
                $q->where('name', $request->input('role'));
            });
        }

        // Verification filter
        if ($request->has('verified')) {
            if ($request->input('verified') == 1) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        // Recent filter (registered in last 7 days)
        if ($request->has('recent') && $request->input('recent') == 1) {
            $query->where('created_at', '>=', now()->subDays(7));
        }

        // Active filter (logged in within last 30 days)
        if ($request->has('active') && $request->input('active') == 1) {
            $query->whereNotNull('last_login_at')
                ->where('last_login_at', '>=', now()->subDays(30));
        }

        $perPageRaw = $request->input('per_page', 20);
        $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : 20;
        $users = $query->latest()->paginate($perPage);

        // Ensure roles and permissions are always arrays (not null)
        $users->getCollection()->transform(function ($user) {
            /** @var User $user */
            // Ensure roles is always a collection (will be serialized as array in JSON)
            if (! $user->relationLoaded('roles')) {
                $user->setRelation('roles', collect([]));
            }
            // Ensure permissions includes inherited ones
            // getAllPermissions() returns a collection of all permissions (direct + inherited)
            $user->setRelation('permissions', $user->getAllPermissions());

            return $user;
        });

        return $this->paginated($users, 'Users retrieved successfully');
    }

    /**
     * Get user statistics for dashboard cards.
     */
    public function stats(): \Illuminate\Http\JsonResponse
    {
        $total = User::count();
        $verified = User::whereNotNull('email_verified_at')->count();
        $unverified = User::whereNull('email_verified_at')->count();

        // Count by roles
        $roleCounts = [];
        $roles = \Spatie\Permission\Models\Role::all();
        foreach ($roles as $role) {
            $roleCounts[$role->name] = User::role($role->name)->count();
        }

        // Recent (last 7 days)
        $recent = User::where('created_at', '>=', now()->subDays(7))->count();

        // Active (logged in within last 30 days)
        $active = User::whereNotNull('last_login_at')
            ->where('last_login_at', '>=', now()->subDays(30))
            ->count();

        return $this->success([
            'total' => $total,
            'verified' => $verified,
            'unverified' => $unverified,
            'recent' => $recent,
            'active' => $active,
            'by_role' => $roleCounts,
        ], 'User statistics retrieved successfully');
    }

    /**
     * Create a new user.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $authUser = $request->user();
        /** @var User|null $authUser */
        if (! $authUser) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'min:8', new StrongPassword],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
            'location' => 'nullable|string|max:255',
            'avatar' => 'nullable|string',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['email_verified_at'] = now();

        $user = User::create($validated);

        if ($request->has('roles')) {
            $maxRequestedRank = 0;
            $rolesInput = $request->input('roles', []);
            $roles = \Spatie\Permission\Models\Role::whereIn('id', is_array($rolesInput) ? $rolesInput : [])->get();

            $roleRanks = User::getRoleRankMap();

            foreach ($roles as $role) {
                $rank = $roleRanks[(string) $role->name] ?? 0;
                if ($rank > $maxRequestedRank) {
                    $maxRequestedRank = $rank;
                }
            }

            if ($maxRequestedRank > $authUser->getRoleRank()) {
                return $this->forbidden('You cannot assign a role higher than your own rank');
            }

            $user->syncRoles(is_array($rolesInput) ? $rolesInput : []);
        }

        $user->load(['roles']);
        $user->setRelation('permissions', $user->getAllPermissions());

        return $this->success($user, 'User created successfully', 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): \Illuminate\Http\JsonResponse
    {
        $user->load(['roles']);
        $user->setRelation('permissions', $user->getAllPermissions());

        return $this->success($user, 'User retrieved successfully');
    }

    /**
     * Get the authenticated user's profile.
     */
    public function profile(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $user->load(['roles']);
        $user->setRelation('permissions', $user->getAllPermissions());

        return $this->success($user, 'Profile retrieved successfully');
    }

    /**
     * Get the user's login history.
     */
    public function loginHistory(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $perPageRaw = $request->input('per_page', 10);
        $perPage = min(max(is_numeric($perPageRaw) ? (int) $perPageRaw : 10, 1), 100);

        $history = \Modules\System\Models\LoginHistory::where('user_id', $user->id)
            ->orderBy('login_at', 'desc')
            ->paginate($perPage);

        return $this->paginated($history, 'Login history retrieved successfully');
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
            'location' => 'nullable|string|max:255',
            'avatar' => 'nullable|string',
        ]);

        $user->update($validated);

        $user->load(['roles']);
        $user->setRelation('permissions', $user->getAllPermissions());

        return $this->success($user, 'Profile updated successfully');
    }

    /**
     * Upload user avatar.
     */
    public function uploadAvatar(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $request->validate([
            'avatar' => 'required|image|max:2048', // 2MB max
        ]);

        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $file = $request->file('avatar');
        if (! ($file instanceof \Illuminate\Http\UploadedFile)) {
            return $this->error('Invalid avatar file', 400);
        }

        $path = $file->store('avatars', 'public');
        if ($path === false) {
            return $this->error('Failed to store avatar', 500);
        }

        $user->update(['avatar' => $path]);

        return $this->success([
            'avatar' => URL::to(Storage::url($path)),
            'user' => $user->load(['roles'])->setRelation('permissions', $user->getAllPermissions()),
        ], 'Avatar uploaded successfully');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', 'min:8', new StrongPassword],
        ]);

        $currentPassword = $request->input('current_password');
        $newPassword = $request->input('password');

        if (! is_string($currentPassword) || ! is_string($user->password) || ! Hash::check($currentPassword, $user->password)) {
            return $this->validationError(['current_password' => ['Current password is incorrect']], 'Current password is incorrect');
        }

        $user->update([
            'password' => Hash::make(is_string($newPassword) ? $newPassword : ''),
        ]);

        return $this->success(null, 'Password updated successfully');
    }

    /**
     * Get user preferences.
     */
    public function getPreferences(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        return $this->success([
            'dark_mode' => $user->getPreference('dark_mode', 'system'),
            'locale' => $user->getPreference('locale', 'en'),
        ], 'Preferences retrieved successfully');
    }

    /**
     * Update user preferences.
     */
    public function updatePreferences(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'dark_mode' => 'sometimes|string|in:light,dark,system',
            'locale' => 'sometimes|string|max:10',
        ]);

        foreach ($validated as $key => $value) {
            $user->setPreference($key, $value);
        }

        $user->save();

        return $this->success([
            'dark_mode' => $user->getPreference('dark_mode', 'system'),
            'locale' => $user->getPreference('locale', 'en'),
        ], 'Preferences updated successfully');
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): \Illuminate\Http\JsonResponse
    {
        $authUser = $request->user();
        /** @var User|null $authUser */
        if (! $authUser) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,'.$user->id,
            'password' => ['sometimes', 'nullable', 'min:8', new StrongPassword],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
            'location' => 'nullable|string|max:255',
            'avatar' => 'nullable|string',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'is_verified' => 'sometimes|boolean',
        ]);

        if (isset($validated['password'])) {
            $password = $validated['password'];
            $validated['password'] = Hash::make(is_string($password) ? $password : '');
        } else {
            unset($validated['password']);
        }

        if (isset($validated['is_verified'])) {
            $user->email_verified_at = $validated['is_verified'] ? now() : null;
            unset($validated['is_verified']);
        }

        $user->update($validated);

        // Guard: Hierarchy check
        // Allow if self OR if super (rank >= 100) OR if strictly higher rank
        if ($authUser->id !== $user->id && $authUser->getRoleRank() < 100 && ! $authUser->isHigherThan($user)) {
            return $this->forbidden(trans('features.users.messages.hierarchy_restriction'));
        }

        if ($request->has('roles')) {
            $maxRequestedRank = 0;
            $rolesInput = $request->input('roles', []);
            $roles = \Spatie\Permission\Models\Role::whereIn('id', is_array($rolesInput) ? $rolesInput : [])->get();

            $roleRanks = User::getRoleRankMap();

            foreach ($roles as $role) {
                $rank = $roleRanks[(string) $role->name] ?? 0;
                if ($rank > $maxRequestedRank) {
                    $maxRequestedRank = $rank;
                }
            }

            // Cannot assign role higher than own
            if ($maxRequestedRank > $authUser->getRoleRank()) {
                return $this->forbidden('You cannot assign a role higher than your own rank');
            }

            // Guard: cannot remove super role from the last super
            $isCurrentlySuperAdmin = $user->hasRole('super');
            $requestedSuperAdmin = $roles->contains('name', 'super');

            if ($isCurrentlySuperAdmin && ! $requestedSuperAdmin) {
                $superAdminCount = User::role('super')->count();
                if ($superAdminCount <= 1) {
                    return $this->validationError(['roles' => ['Cannot remove the last super role']], 'Cannot remove the last super role');
                }
            }

            $user->syncRoles(is_array($rolesInput) ? $rolesInput : []);
        }

        $user->load(['roles']);
        $user->setRelation('permissions', $user->getAllPermissions());

        return $this->success($user, 'User updated successfully');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $user): \Illuminate\Http\JsonResponse
    {
        $authUser = $request->user();
        /** @var User|null $authUser */
        if (! $authUser) {
            return $this->unauthorized();
        }

        // Prevent deleting yourself
        if ($user->id === $authUser->id) {
            return $this->validationError(['user' => ['You cannot delete your own account']], 'You cannot delete your own account');
        }

        // Prevent deleting users with higher or equal rank (unless super)
        if ($authUser->getRoleRank() < 100 && ! $authUser->isHigherThan($user)) {
            return $this->forbidden(trans('features.users.messages.hierarchy_restriction'));
        }

        // Prevent deleting the last super
        if ($user->hasRole('super')) {
            $superAdminCount = User::role('super')->count();
            if ($superAdminCount <= 1) {
                return $this->validationError(['user' => ['Cannot delete the last super account']], 'Cannot delete the last super account');
            }
        }

        // Delete avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return $this->success(null, 'User deleted successfully');
    }

    /**
     * Force logout a user from all devices by revoking all their tokens.
     * Admin-only action for security management.
     */
    public function forceLogout(Request $request, User $user): \Illuminate\Http\JsonResponse
    {
        $authUser = $request->user();
        /** @var User|null $authUser */
        if (! $authUser) {
            return $this->unauthorized();
        }

        // Guard: Hierarchy check
        if ($authUser->id !== $user->id && $authUser->getRoleRank() < 100 && ! $authUser->isHigherThan($user)) {
            return $this->forbidden(trans('features.users.messages.hierarchy_restriction'));
        }

        // Prevent force logging out yourself
        if ($user->id === $authUser->id) {
            return $this->validationError(
                ['user' => ['You cannot force logout your own account']],
                'You cannot force logout your own account'
            );
        }

        // Count and revoke all tokens
        $tokenCount = $user->tokens()->count();
        $user->tokens()->delete();

        // Log this security action
        \Modules\Security\Models\SecurityLog::log(
            'force_logout',
            $user,
            IpHelper::getClientIp($request),
            "Admin force logged out user from {$tokenCount} device(s)",
            [
                'admin_id' => $authUser->id,
                'admin_name' => $authUser->name,
                'revoked_sessions' => $tokenCount,
            ]
        );

        return $this->success([
            'revoked_sessions' => $tokenCount,
        ], "User logged out from {$tokenCount} device(s) successfully");
    }

    /**
     * Verify a user's email manually.
     */
    public function verify(Request $request, User $user): \Illuminate\Http\JsonResponse
    {
        $authUser = $request->user();
        /** @var User|null $authUser */
        if (! $authUser) {
            return $this->unauthorized();
        }

        // Guard: Hierarchy check
        if ($authUser->id !== $user->id && $authUser->getRoleRank() < 100 && ! $authUser->isHigherThan($user)) {
            return $this->forbidden(trans('features.users.messages.hierarchy_restriction'));
        }

        if ($user->email_verified_at) {
            return $this->error('User is already verified', 400);
        }

        $user->markEmailAsVerified();

        $user->load(['roles']);
        $user->setRelation('permissions', $user->getAllPermissions());

        return $this->success($user, 'User verified successfully');
    }

    /**
     * Restore a soft-deleted user.
     *
     * @param  int|string  $id
     */
    public function restore($id): \Illuminate\Http\JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        if (! $user->trashed()) {
            return $this->error('User is not deleted', 400);
        }

        $user->restore();

        return $this->success(null, 'User restored successfully');
    }

    /**
     * Permanently delete a user.
     *
     * @param  int|string  $id
     */
    public function forceDelete(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        $authUser = $request->user();
        /** @var User|null $authUser */
        if (! $authUser) {
            return $this->unauthorized();
        }

        // Prevent deleting yourself
        if ($user->id === $authUser->id) {
            return $this->validationError(['user' => ['You cannot delete your own account']], 'You cannot delete your own account');
        }

        // Prevent deleting users with higher or equal rank (unless super)
        if ($authUser->getRoleRank() < 100 && ! $authUser->isHigherThan($user)) {
            return $this->forbidden(trans('features.users.messages.hierarchy_restriction'));
        }

        // Prevent deleting the last super
        if ($user->hasRole('super')) {
            $superAdminCount = User::role('super')->count();
            if ($superAdminCount <= 1) {
                return $this->validationError(['user' => ['Cannot delete the last super account']], 'Cannot delete the last super account');
            }
        }

        // Delete avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->forceDelete();

        return $this->success(null, 'User permanently deleted');
    }

    /**
     * Handle bulk actions for users.
     */
    public function bulkAction(Request $request): \Illuminate\Http\JsonResponse
    {
        $authUser = $request->user();
        /** @var User|null $authUser */
        if (! $authUser) {
            return $this->unauthorized();
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
            'action' => 'required|in:delete,force_logout,verify,restore,force_delete',
        ]);

        $idsRaw = $request->input('ids');
        $ids = is_array($idsRaw) ? $idsRaw : [];
        $actionRaw = $request->input('action');
        $action = is_string($actionRaw) ? $actionRaw : '';
        $count = 0;
        $message = '';

        if ($action === 'delete') {
            // Filter out self-deletion and hierarchy protection
            $ids = array_filter($ids, function ($id) use ($authUser): bool {
                // Self deletion check
                if ($id == $authUser->id) {
                    return false;
                }

                /** @var User|null $target */
                $target = User::find(is_scalar($id) ? $id : null);
                if (! $target instanceof User) {
                    return false;
                }
                // Rank check
                return !($authUser->getRoleRank() < 100 && ! $authUser->isHigherThan($target));
            });

            // Prevent deleting the last super in bulk delete
            $superAdminsToDelete = User::whereIn('id', $ids)->role('super')->count();
            if ($superAdminsToDelete > 0) {
                $totalSuperAdmins = User::role('super')->count();
                if ($totalSuperAdmins - $superAdminsToDelete < 1) {
                    return $this->validationError(['ids' => ['Bulk action would leave the system without a super role']], 'Cannot delete the last super role');
                }
            }

            // Standard soft delete does NOT delete avatar
            $deleteResult = User::whereIn('id', $ids)->delete();
            $count = is_numeric($deleteResult) ? (int) $deleteResult : 0;
            $message = $count.' users moved to trash';
        } elseif ($action === 'force_delete') {
            // Filter out self-deletion and hierarchy protection
            $ids = array_filter($ids, function ($id) use ($authUser): bool {
                // Self deletion check
                if ($id == $authUser->id) {
                    return false;
                }

                /** @var User|null $target */
                $target = User::withTrashed()->find(is_scalar($id) ? $id : null);
                if (! $target instanceof User) {
                    return false;
                }
                // Rank check
                return !($authUser->getRoleRank() < 100 && ! $authUser->isHigherThan($target));
            });

            // Prevent deleting the last super in bulk delete
            $superAdminsToDelete = User::withTrashed()->whereIn('id', $ids)->role('super')->count();
            if ($superAdminsToDelete > 0) {
                $totalSuperAdmins = User::role('super')->count();
                if ($totalSuperAdmins - $superAdminsToDelete < 1) {
                    return $this->validationError(['ids' => ['Bulk action would leave the system without a super role']], 'Cannot delete the last super role');
                }
            }

            // Delete avatars
            $users = User::withTrashed()->whereIn('id', $ids)->get();
            foreach ($users as $user) {
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
            }

            $forceDeleteResult = User::withTrashed()->whereIn('id', $ids)->forceDelete();
            $count = is_numeric($forceDeleteResult) ? (int) $forceDeleteResult : 0;
            $message = $count.' users permanently deleted';
        } elseif ($action === 'restore') {
            $count = User::withTrashed()->whereIn('id', $ids)->restore();
            $message = "{$count} users restored successfully";
        } elseif ($action === 'force_logout') {
            // Filter out self-logout and hierarchy protection
            $ids = array_filter($ids, function ($id) use ($authUser): bool {
                if ($id == $authUser->id) {
                    return false;
                }

                /** @var User|null $target */
                $target = User::find(is_scalar($id) ? $id : null);
                if (! $target instanceof User) {
                    return false;
                }
                return !($authUser->getRoleRank() < 100 && ! $authUser->isHigherThan($target));
            });

            $users = User::whereIn('id', $ids)->get();
            foreach ($users as $user) {
                $user->tokens()->delete();
                $count++;
            }
            $message = "{$count} users force logged out successfully";
        } elseif ($action === 'verify') {
            // Filter hierarchy protection
            $ids = array_filter($ids, function ($id) use ($authUser): bool {
                /** @var User|null $target */
                $target = User::find(is_scalar($id) ? $id : null);
                if (! $target instanceof User) {
                    return false;
                }
                if ($authUser->id !== $id && $authUser->getRoleRank() < 100 && ! $authUser->isHigherThan($target)) {
                    return false;
                }

                return true;
            });

            $users = User::whereIn('id', $ids)->whereNull('email_verified_at')->get();
            foreach ($users as $user) {
                $user->markEmailAsVerified();
                $count++;
            }
            $message = "{$count} users verified successfully";
        }

        return $this->success(['count' => $count], $message);
    }
}
