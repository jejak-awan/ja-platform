<?php

namespace Modules\Library\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Library\Models\Tag;
use Modules\System\Http\Controllers\BaseApiController;

class TagController extends BaseApiController
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Tag::orderBy('name');

        /** @var \Modules\System\Models\User|null $user */
        $user = $request->user();

        // Scope by workspace
        // ScopedByWorkspace trait handles this automatically if active workspace is set.

        // Admin/Manager can see all, others see own + global
        if ($user && ! $user->can('manage tags')) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('author_id')->orWhere('author_id', $user->id);
            });
        } elseif (! $user) {
            $query->whereNull('author_id');
        }

        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? $searchRaw : '';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->has('per_page')) {
            $perPageRaw = $request->get('per_page', 20);
            $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : 20;
            $tags = $query->paginate($perPage);

            return $this->success($tags, 'Tags retrieved successfully');
        }

        $tags = $query->get();

        return $this->success($tags, 'Tags retrieved successfully');
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string',
            'type' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        /** @var \Modules\System\Models\User|null $user */
        $user = $request->user();

        $type = $validated['type'] ?? 'content';
        $slug = $validated['slug'];

        // Check uniqueness in Library
        $exists = Tag::where('type', $type)
            ->where('slug', $slug)
            ->exists();

        if ($exists) {
            return $this->validationError(['slug' => ['Tag with this slug and type already exists']], 'Validation error');
        }

        if ($user) {
            $validated['author_id'] = $user->id;
        }

        $tag = Tag::create($validated);

        return $this->success($tag, 'Tag created successfully', 201);
    }

    public function show(Tag $tag): \Illuminate\Http\JsonResponse
    {
        return $this->success($tag, 'Tag retrieved successfully');
    }

    public function update(Request $request, Tag $tag): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string',
            'type' => 'sometimes|required|string',
            'metadata' => 'nullable|array',
        ]);

        $tag->update($validated);

        return $this->success($tag, 'Tag updated successfully');
    }

    public function bulkDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:lib_tags,id',
        ]);

        $ids = $validated['ids'];
        $query = Tag::whereIn('id', $ids);

        /** @var \Modules\System\Models\User|null $user */
        $user = $request->user();

        if ($user && ! $user->can('manage tags')) {
            $query->where('author_id', $user->id);
        }

        $count = $query->delete();

        return $this->success(['deleted_count' => $count], 'Tags deleted successfully');
    }

    public function statistics(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Tag::query();
        /** @var \Modules\System\Models\User|null $user */
        $user = $request->user();

        if ($user && ! $user->can('manage tags')) {
            $query->where('author_id', $user->id);
        }

        $stats = [
            'total_tags' => $query->count(),
            'types' => (clone $query)->select('type', \Illuminate\Support\Facades\DB::raw('count(*) as count'))->groupBy('type')->get(),
        ];

        return $this->success($stats, 'Tag statistics retrieved successfully');
    }

    public function destroy(Tag $tag): \Illuminate\Http\JsonResponse
    {
        $tag->delete();
        return $this->success(null, 'Tag deleted successfully');
    }
}
