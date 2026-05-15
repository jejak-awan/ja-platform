<?php

namespace Modules\System\Http\Controllers\Console;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Cms\Models\Tag;
use Modules\System\Http\Controllers\BaseApiController;

/**
 * @OA\Tag(name="Tags")
 */
class TagController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/tags",
     *     summary="List all tags",
     *     tags={"Tags"},
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name or slug",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="usage",
     *         in="query",
     *         description="Filter by usage (used, unused, media)",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"used", "unused", "media"})
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Results per page",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Tags retrieved successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Tag::orderBy('name');

        /** @var \Modules\System\Models\User|null $user */
        $user = $request->user();

        // Admin/Manager can see all, others see own + global
        if ($user && ! $user->can('manage tags')) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('author_id')->orWhere('author_id', $user->id);
            });
        } elseif (! $user) {
            // Public/Guest sees only global tags
            $query->whereNull('author_id');
        }

        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? $searchRaw : '';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $usageRaw = $request->input('usage');
        if ($request->has('usage') && is_string($usageRaw) && in_array($usageRaw, ['used', 'unused', 'media'])) {
            if ($usageRaw === 'used') {
                $query->has('contents');
            } elseif ($usageRaw === 'media') {
                $query->has('media');
            } else {
                $query->doesntHave('contents')->doesntHave('media');
            }
        }

        if ($request->has('per_page')) {
            $perPageRaw = $request->get('per_page', 20);
            $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : 20;
            $tags = $query->withCount('contents')->paginate($perPage);

            return $this->success($tags, 'Tags retrieved successfully');
        }

        // Caching removed to support dynamic per-user scoping
        $tags = $query->get();

        return $this->success($tags, 'Tags retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/tags",
     *     summary="Create a new tag",
     *     tags={"Tags"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "slug"},
     *
     *             @OA\Property(property="name", type="string", example="Laravel"),
     *             @OA\Property(property="slug", type="string", example="laravel"),
     *             @OA\Property(property="description", type="string", example="Posts about Laravel framework")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Tag created successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:tags,slug',
            'description' => 'nullable|string',
            'author_id' => 'nullable|exists:users,id',
        ]);

        /** @var \Modules\System\Models\User|null $user */
        $user = $request->user();

        // Assign author logic
        if ($user && $user->can('manage tags')) {
            // Admin can assign author or leave null (Global)
        } elseif ($user) {
            // Regular user forces ownership
            $validated['author_id'] = $user->id;
        }

        $tag = Tag::create($validated);

        $this->clearTagCaches();

        return $this->success($tag, 'Tag created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/tags/{tag}",
     *     summary="Get tag details",
     *     tags={"Tags"},
     *
     *     @OA\Parameter(
     *         name="tag",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Tag retrieved successfully"
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function show(Tag $tag): \Illuminate\Http\JsonResponse
    {
        // Scope check
        /** @var \Modules\System\Models\User|null $user */
        $user = request()->user();
        if ($user && ! $user->can('manage tags')) {
            if ($tag->author_id && $tag->author_id !== $user->id) {
                return $this->forbidden('You do not have permission to view this tag');
            }
        }

        return $this->success($tag->load('contents'), 'Tag retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/tags/{tag}",
     *     summary="Update a tag",
     *     tags={"Tags"},
     *
     *     @OA\Parameter(
     *         name="tag",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="slug", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Tag updated successfully"
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function update(Request $request, Tag $tag): \Illuminate\Http\JsonResponse
    {
        /** @var \Modules\System\Models\User|null $user */
        $user = request()->user();
        // Ownership check
        if ($user && ! $user->can('manage tags')) {
            if ($tag->author_id && $tag->author_id !== $user->id) {
                return $this->forbidden('You do not have permission to update this tag');
            }
            unset($request['author_id']);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:tags,slug,'.$tag->id,
            'description' => 'nullable|string',
            'author_id' => 'nullable|exists:users,id',
        ]);

        $tag->update($validated);

        $this->clearTagCaches();

        return $this->success($tag, 'Tag updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/tags/{tag}",
     *     summary="Delete a tag",
     *     tags={"Tags"},
     *
     *     @OA\Parameter(
     *         name="tag",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Tag deleted successfully"
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function destroy(Tag $tag): \Illuminate\Http\JsonResponse
    {
        /** @var \Modules\System\Models\User|null $user */
        $user = request()->user();
        // Ownership check
        if ($user && ! $user->can('manage tags')) {
            if ($tag->author_id && $tag->author_id !== $user->id) {
                return $this->forbidden('You do not have permission to delete this tag');
            }
            if (is_null($tag->author_id)) {
                return $this->forbidden('You do not have permission to delete global tags');
            }
        }

        $tag->delete();

        $this->clearTagCaches();

        return $this->success(null, 'Tag deleted successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/tags/bulk-delete",
     *     summary="Bulk delete tags",
     *     tags={"Tags"},
     *
     *     @OA\RequestBody(
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Bulk deletion processed"
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function bulkDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tags,id',
        ]);

        /** @var array<int> $ids */
        $ids = $validated['ids'];
        $query = Tag::whereIn('id', $ids);

        /** @var \Modules\System\Models\User|null $user */
        $user = $request->user();

        // Scope deletion
        if ($user && ! $user->can('manage tags')) {
            $query->where('author_id', $user->id);
            // This implicitly prevents deleting Global tags (author_id is null)
        }

        $count = $query->delete();

        $this->clearTagCaches();

        return $this->success(['deleted_count' => $count], 'Tags deleted successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/tags/statistics",
     *     summary="Get tag statistics",
     *     tags={"Tags"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Statistics retrieved successfully"
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function statistics(Request $request): \Illuminate\Http\JsonResponse
    {
        /** @var \Modules\System\Models\User|null $user */
        $user = $request->user();

        $cacheKey = 'tags_statistics';
        if ($user && ! $user->can('manage settings')) {
            $cacheKey .= '_u'.$user->id;
        }

        $stats = Cache::remember($cacheKey, now()->addHours(1), function () use ($user) {
            $query = Tag::query();

            // Scope if not manager
            if ($user && ! $user->can('manage settings')) {
                $query->where('author_id', $user->id);
            }

            $tags = (clone $query)->withCount(['contents' => function ($q) use ($user) {
                if ($user && ! $user->can('manage content')) {
                    $q->where('author_id', $user->id);
                }
            }])->get();

            return [
                'total_tags' => $tags->count(),
                'used_tags' => $tags->filter(fn ($tag) => $tag->contents_count > 0)->count(),
                'unused_tags' => $tags->filter(fn ($tag) => $tag->contents_count === 0)->count(),
                'total_usage' => $tags->sum('contents_count'),
                'most_used' => $tags->sortByDesc('contents_count')->take(5)->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'usage_count' => $tag->contents_count,
                ])->values(),
            ];
        });

        return $this->success($stats, 'Tag statistics retrieved successfully');
    }

    protected function clearTagCaches(): void
    {
        Cache::forget('tags_all');
        Cache::forget('tags_statistics');
    }
}
