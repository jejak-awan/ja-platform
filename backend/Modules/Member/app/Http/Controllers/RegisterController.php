<?php

namespace Modules\Member\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Member\Models\Member;
use Modules\Security\Rules\StrongPassword;
use Modules\System\Http\Controllers\BaseApiController;
use Modules\System\Models\Role;
use Modules\System\Models\Setting;
use Modules\System\Models\User;

class RegisterController extends BaseApiController
{
    public function register(Request $request): JsonResponse
    {
        // Check if registration is enabled in settings
        $registrationEnabled = Setting::get('enable_registration', true);
        if (! $registrationEnabled) {
            return $this->error('Registration is currently disabled.', 403, [], 'REGISTRATION_DISABLED');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:srv_auth_users,email',
            'password' => ['required', 'confirmed', 'min:8', new StrongPassword],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make((string) $validated['password']),
        ]);

        // Assign default role (member)
        $memberRole = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
        $user->assignRole($memberRole);

        // Create member profile
        Member::create([
            'user_id' => $user->id,
            'points' => 0,
            'tier' => 'bronze',
        ]);

        // Send email verification
        $user->sendEmailVerificationNotification();

        $token = $user->createToken('member-token')->plainTextToken;

        return $this->success([
            'user' => $user->load('roles'),
            'token' => $token,
        ], 'Registration successful. Please verify your email address.', 201);
    }
}
