<?php

namespace Modules\Layout\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Layout\Models\UrlRewrite;
use Modules\System\Http\Controllers\BaseApiController;

class UrlRewriteController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = UrlRewrite::query();

        if ($request->has('module_scope')) {
            $query->where('module_scope', $request->input('module_scope'));
        }

        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? $searchRaw : '';
            $query->where(function ($q) use ($search): void {
                $searchStr = strtolower($search);
                $q->where(DB::raw('lower(source_path)'), 'like', "%{$searchStr}%")
                    ->orWhere(DB::raw('lower(target_path)'), 'like', "%{$searchStr}%");
            });
        }

        $rewrites = $query->latest()->paginate(20);

        return $this->success($rewrites, 'URL rewrites retrieved successfully');
    }

    public function store(Request $request): JsonResponse
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

    public function show(UrlRewrite $urlRewrite): JsonResponse
    {
        return $this->success($urlRewrite, 'URL rewrite retrieved successfully');
    }

    public function update(Request $request, UrlRewrite $urlRewrite): JsonResponse
    {
        $validated = $request->validate([
            'source_path' => 'sometimes|required|string|unique:lay_url_rewrites,source_path,'.$urlRewrite->id,
            'target_path' => 'sometimes|required|string',
            'status_code' => 'sometimes|required|integer',
            'is_active' => 'boolean',
        ]);

        $urlRewrite->update($validated);

        return $this->success($urlRewrite, 'URL rewrite updated successfully');
    }

    public function destroy(UrlRewrite $urlRewrite): JsonResponse
    {
        $urlRewrite->delete();

        return $this->success(null, 'URL rewrite deleted successfully');
    }

    public function statistics(): JsonResponse
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
