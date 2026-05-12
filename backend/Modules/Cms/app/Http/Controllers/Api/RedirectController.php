<?php

namespace Modules\Cms\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Cms\Models\Redirect;
use Modules\Core\Http\Controllers\Api\BaseApiController;

class RedirectController extends BaseApiController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:manage redirects');
    }
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Redirect::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? $searchRaw : '';
            $query->where(function ($q) use ($search) {
                $q->where('from_url', 'like', "%{$search}%")
                    ->orWhere('to_url', 'like', "%{$search}%");
            });
        }

        $redirects = $query->latest()->paginate(20);

        return $this->paginated($redirects, 'Redirects retrieved successfully');
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'from_url' => 'required|string|unique:redirects,from_url',
            'to_url' => 'required|string',
            'type' => 'required|in:301,302,307,308',
            'is_active' => 'boolean',
        ]);

        // Ensure from_url starts with /
        if (is_string($validated['from_url']) && ! str_starts_with($validated['from_url'], '/')) {
            $validated['from_url'] = '/'.$validated['from_url'];
        }

        $redirect = Redirect::create($validated);

        return $this->success($redirect, 'Redirect created successfully', 201);
    }

    public function show(Redirect $redirect): \Illuminate\Http\JsonResponse
    {
        return $this->success($redirect, 'Redirect retrieved successfully');
    }

    public function update(Request $request, Redirect $redirect): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'from_url' => 'sometimes|required|string|unique:redirects,from_url,'.$redirect->id,
            'to_url' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:301,302,307,308',
            'is_active' => 'boolean',
        ]);

        // Ensure from_url starts with /
        if (isset($validated['from_url']) && is_string($validated['from_url']) && ! str_starts_with($validated['from_url'], '/')) {
            $validated['from_url'] = '/'.$validated['from_url'];
        }

        $redirect->update($validated);

        return $this->success($redirect, 'Redirect updated successfully');
    }

    public function destroy(Redirect $redirect): \Illuminate\Http\JsonResponse
    {
        $redirect->delete();

        return $this->success(null, 'Redirect deleted successfully');
    }

    public function statistics(): \Illuminate\Http\JsonResponse
    {
        $stats = [
            'total' => Redirect::count(),
            'active' => Redirect::where('is_active', true)->count(),
            'total_hits' => Redirect::sum('hits'),
            'top_redirects' => Redirect::where('is_active', true)
                ->orderBy('hits', 'desc')
                ->limit(10)
                ->get(['from_url', 'to_url', 'hits', 'type']),
        ];

        return $this->success($stats, 'Redirect statistics retrieved successfully');
    }
}
