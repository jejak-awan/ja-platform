<?php

namespace Modules\Member\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\System\Http\Controllers\BaseApiController;
use Modules\System\Models\User;

class LoginController extends BaseApiController
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check((string) $request->password, (string) $user->password)) {
            return $this->validationError([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            return $this->error('Please verify your email address before logging in.', 403);
        }

        // Check if user has member role
        if (! $user->hasRole('member')) {
            return $this->forbidden('This account is not a member account.');
        }

        $token = $user->createToken('member-token')->plainTextToken;

        return $this->success([
            'user' => $user->load('roles'),
            'token' => $token,
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return $this->success(null, 'Logged out successfully');
    }
}
