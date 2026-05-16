<?php

namespace Modules\Member\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Member\Models\Member;
use Modules\System\Http\Controllers\BaseApiController;

class MemberController extends BaseApiController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function profile(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return $this->unauthorized();
        }

        $member = Member::where('user_id', $user->id)->firstOrFail();

        return $this->success([
            'user' => $user,
            'member' => $member,
        ], 'Profile retrieved successfully');
    }

    public function updateProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
        ]);

        $user->update($validated);

        return $this->success($user, 'Profile updated successfully');
    }
}
