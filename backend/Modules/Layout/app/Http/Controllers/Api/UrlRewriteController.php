<?php

namespace Modules\Layout\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Layout\Models\UrlRewrite;
use Modules\System\Http\Controllers\BaseApiController;

class UrlRewriteController extends BaseApiController
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = UrlRewrite::query();

        if ($request->has('module_scope')) {
            $query->where('module_scope', $request->input('module_scope'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search): void {
                $q->where('source_path', 'like', "%{$search}%")
                    ->orWhere('target_path', 'like', "%{$search}%");
            });
        }

        $rewrites = $query->latest()->paginate(20);

        return $this->success($rewrites, 'URL rewrites retrieved successfully');
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'source_path' => 'required|string|unique:lay_url_rewrites,source_path',
            'target_path' => 'required|string',
            'status_code' => 'required|integer',
            'module_scope' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $rewrite = UrlRewrite::create($validated);

        return $this->success($rewrite, 'URL rewrite created successfully', 201);
    }

    public function show(UrlRewrite $urlRewrite): \Illuminate\Http\JsonResponse
    {
        return $this->success($urlRewrite, 'URL rewrite retrieved successfully');
    }

    public function update(Request $request, UrlRewrite $urlRewrite): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'source_path' => 'sometimes|required|string|unique:lay_url_rewrites,source_path,' . $urlRewrite->id,
            'target_path' => 'sometimes|required|string',
            'status_code' => 'sometimes|required|integer',
            'is_active' => 'boolean',
        ]);

        $urlRewrite->update($validated);

        return $this->success($urlRewrite, 'URL rewrite updated successfully');
    }

    public function destroy(UrlRewrite $urlRewrite): \Illuminate\Http\JsonResponse
    {
        $urlRewrite->delete();
        return $this->success(null, 'URL rewrite deleted successfully');
    }

    public function statistics(): \Illuminate\Http\JsonResponse
    {
        $stats = [
            'total' => UrlRewrite::count(),
            'active' => UrlRewrite::where('is_active', true)->count(),
            'total_hits' => UrlRewrite::sum('hits'),
            'top_rewrites' => UrlRewrite::where('is_active', true)
                ->orderBy('hits', 'desc')
                ->limit(10)
                ->get(),
        ];

        return $this->success($stats, 'URL rewrite statistics retrieved successfully');
    }
}
