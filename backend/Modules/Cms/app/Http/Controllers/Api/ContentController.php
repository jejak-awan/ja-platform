<?php

namespace Modules\Cms\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Cms\Models\Content;
use Modules\Cms\Services\ContentService;
use Modules\System\Http\Controllers\BaseApiController;
use Modules\Cms\Services\CmsCacheService;

/**
 * @OA\Tag(name="Content")
 */
class ContentController extends BaseApiController
{
    protected ContentService $contentService;
    protected CmsCacheService $cacheService;

    public function __construct(ContentService $contentService, CmsCacheService $cacheService)
    {
        $this->contentService = $contentService;
        $this->cacheService = $cacheService;

        $this->middleware('auth:sanctum')->except(['index', 'show', 'related']);
        $this->middleware('permission:view content')->only(['stats', 'revisions']);
        $this->middleware('permission:create content')->only(['store', 'duplicate']);
        $this->middleware('permission:edit content')->only(['update', 'restore', 'bulkUpdate']);
        $this->middleware('permission:delete content')->only(['destroy', 'forceDelete']);
    }

    /**
     * Align SPA payloads with validation (booleans, media objects, tag id list, empty category).
     */
    private function normalizeContentWriteRequest(Request $request): void
    {
        $merge = [];

        if ($request->exists('comment_status')) {
            $cs = $request->input('comment_status');
            if (is_bool($cs)) {
                $merge['comment_status'] = $cs ? 'open' : 'closed';
            }
        }

        foreach (['featured_image', 'og_image'] as $field) {
            if (! $request->has($field)) {
                continue;
            }
            $v = $request->input($field);
            if (is_array($v)) {
                $url = $v['url'] ?? $v['path'] ?? $v['full_url'] ?? null;
                $merge[$field] = is_string($url) ? $url : null;
            }
        }

        if ($request->exists('category_id')) {
            $cid = $request->input('category_id');
            if ($cid === '' || $cid === false) {
                $merge['category_id'] = null;
            }
        }

        $tagsInput = $request->input('tags');
        if (is_array($tagsInput)) {
            $ids = [];
            foreach ($tagsInput as $id) {
                if (is_int($id) || (is_string($id) && ctype_digit($id))) {
                    $n = (int) $id;
                    if ($n > 0) {
                        $ids[] = $n;
                    }
                }
            }
            $merge['tags'] = $ids;
        }

        if ($merge !== []) {
            $request->merge($merge);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/content",
     *     summary="List published contents",
     *     tags={"Content"},
     *
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="category_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string", enum={"post", "page"})),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Contents retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $result = $this->contentService->getPublishedContents($request);

        if ($result['paginated']) {
            /** @var \Illuminate\Pagination\LengthAwarePaginator<int, mixed> $paginator */
            $paginator = $result['data'];

            return $this->paginated($paginator, 'Contents retrieved successfully');
        }

        return $this->success($result['data'], 'Contents retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/content/{slug}",
     *     summary="Get published content by slug",
     *     tags={"Content"},
     *
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Content details"
     *     ),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(\Illuminate\Http\Request $request, string $slug): \Illuminate\Http\JsonResponse
    {
        $content = Content::with(['author', 'category', 'tags', 'menuItems.menu', 'comments' => function ($q) {
            $q->where('status', 'approved')->latest();
        }])
            ->where('slug', $slug)
            ->first();

        // Graceful fallback for reserved page slugs if missing
        if (! $content) {
            $fallbackSlugs = ['home', 'about', 'blog', 'contact', 'agenda', 'page', 'search'];
            if (in_array($slug, $fallbackSlugs)) {
                return $this->success(null, ucfirst($slug).' content not found, using fallback');
            }
            abort(404);
        }

        // Check if content is published
        $isPublished = $content->status === 'published' &&
                       ($content->published_at === null || $content->published_at <= Carbon::now());

        // If not published, verify permissions
        if (! $isPublished) {
            /** @var \Modules\System\Models\User|null $user */
            $user = auth('sanctum')->user();

            if (! $user) {
                abort(404);
            }

            // Allow if user has manage/edit permission or is the author
            if (! $user->can('manage content') && ! $user->can('edit content') && $user->id !== $content->author_id) {
                abort(404);
            }
        }

        $content->increment('views');

        return $this->success($content, 'Content retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/content/{slug}/related",
     *     summary="Get related content",
     *     tags={"Content"},
     *
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="Related content retrieved successfully")
     * )
     */
    public function related(string $slug): \Illuminate\Http\JsonResponse
    {
        $related = $this->contentService->getRelatedContent($slug);

        return $this->success($related, 'Related content retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ja/contents/{content}/preview",
     *     summary="Preview content",
     *     tags={"Content"},
     *
     *     @OA\Parameter(name="content", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Content preview details"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function preview(Request $request, Content $content): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        // Allow preview for draft content if user is the author or admin
        if ($content->status === 'draft' && $content->author_id !== $user->id) {
            if (! $user->can('manage content')) {
                return $this->forbidden('Unauthorized to preview this content');
            }
        }

        $urlPrefix = $content->type === 'post' ? 'blog/' : '';
        $frontendUrl = config('app.frontend_url');

        return $this->success([
            'content' => $content->load(['author', 'category', 'tags', 'customFields.customField']),
            'preview_url' => rtrim(is_string($frontendUrl) ? $frontendUrl : '', '/').'/'.$urlPrefix.ltrim((string) $content->slug, '/'),
        ], 'Content preview retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ja/contents",
     *     summary="List all contents for admin",
     *     tags={"Content"},
     *
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="category_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="Contents retrieved successfully"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function adminIndex(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return $this->unauthorized();
        }

        $query = Content::with(['author', 'category', 'tags']);

        // Multi-tenancy scoping
        if (! $user->can('manage content') && ! $user->can('publish content')) {
            $query->where('author_id', $user->id);
        }

        if ($request->has('status') && $request->input('status') !== 'all') {
            if ($request->input('status') === 'trashed') {
                $query->onlyTrashed();
            } else {
                $query->where('status', $request->input('status'));
            }
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? $searchRaw : '';
            $query->where(function ($q) use ($search) {
                $searchStr = (string) $search;
                $q->where('title', 'like', "%{$searchStr}%")
                    ->orWhere('body', 'like', "%{$searchStr}%")
                    ->orWhere('excerpt', 'like', "%{$searchStr}%");
            });
        }

        // Create a limit for per_page to prevent abuse, e.g., max 100
        $perPageRaw = $request->input('per_page', 12);
        $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : 12;
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 12;
        }

        $contents = $query->latest()->paginate($perPage);

        return $this->paginated($contents, 'Contents retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ja/contents/{content}",
     *     summary="Display the specified content for admin",
     *     tags={"Content"},
     *
     *     @OA\Parameter(name="content", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Content details retrieved successfully"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function adminShow(Request $request, Content $content): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $content->load(['author', 'category', 'tags', 'allComments', 'customFields.customField.fieldGroup', 'lockedBy']);

        // Check if lock is active (e.g., within last 60 minutes)
        $isLocked = $content->locked_by !== null && $content->locked_at && $content->locked_at->diffInMinutes(now()) < 60;

        $content->lock_status = [
            'is_locked' => $isLocked,
            'locked_by' => $content->lockedBy,
            'locked_at' => $content->locked_at,
            'can_unlock' => $user->id === $content->locked_by || $user->can('manage content'),
        ];

        return $this->success($content, 'Content retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/contents/stats",
     *     summary="Get content statistics",
     *     tags={"Content"},
     *
     *     @OA\Response(response=200, description="Stats"),
     *     security={{"sanctum":{}}}
     * )
     * Get content statistics for dashboard cards.
     */
    public function stats(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('view content')) {
            return $this->forbidden('Unauthorized to view content statistics');
        }

        $userId = $user->id;
        $canManage = $user->can('manage content');
        $cacheKey = "content_stats_{$userId}_".($canManage ? 'all' : 'scoped');

        return Cache::remember($cacheKey, 300, function () use ($canManage, $user) {
            $query = Content::query();

            // Scope stats if not a content manager
            if (! $canManage) {
                $query->where('author_id', $user->id);
            }

            $stats = [
                'total' => (clone $query)->count(),
                'published' => (clone $query)->where('status', 'published')->count(),
                'pending' => (clone $query)->where('status', 'pending')->count(),
                'draft' => (clone $query)->where('status', 'draft')->count(),
                'archived' => (clone $query)->where('status', 'archived')->count(),
                'trashed' => (clone $query)->onlyTrashed()->count(),
            ];

            return $this->success($stats, 'Content statistics retrieved successfully');
        });
    }

    /**
     * @OA\Post(
     *     path="/api/v1/contents",
     *     summary="Create new content",
     *     tags={"Content"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"title", "status", "type"},
     *
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="status", type="string", enum={"draft", "pending", "published"}),
     *             @OA\Property(property="type", type="string", enum={"post", "page"}),
     *             @OA\Property(property="body", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Created"),
     *     security={{"sanctum":{}}}
     * )
     * Create new content.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $this->normalizeContentWriteRequest($request);

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string',
                'excerpt' => 'nullable|string',
                'intro' => 'nullable|string|max:500',
                'body' => 'nullable|string',
                'featured_image' => 'nullable|string',
                'featured_image_title' => 'nullable|string|max:120',
                'featured_image_caption' => 'nullable|string|max:255',
                'featured_image_position' => 'nullable|in:hero,inline-top,full-bleed',
                'status' => 'required|in:draft,pending,published,archived',
                'type' => 'required|in:post,page,custom,layout',
                'category_id' => 'nullable|exists:lib_categories,id',
                'tags' => 'nullable|array',
                'tags.*' => 'integer|exists:lib_tags,id',
                'published_at' => 'nullable|date',
                'meta' => 'nullable|array',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:255',
                'og_image' => 'nullable|string',
                'create_revision' => 'boolean',
                'custom_fields' => 'nullable|array',
                'is_featured' => 'boolean',
                'new_tags' => 'nullable|array',
                'new_tags.*' => 'string|max:50',
                'comment_status' => 'nullable|in:1,0,true,false,open,closed',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        // Handle slug generation and uniqueness
        if (! isset($validated['slug']) || empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['title']);
        }
        $validated['slug'] = $this->contentService->generateUniqueSlug($validated['slug']);

        // Approval Workflow: Authors cannot publish directly
        if (! $user->can('publish content')) {
            if ($validated['status'] === 'published') {
                $validated['status'] = 'pending';
            }
        }

        // Check for manual slug conflict
        $existing = Content::where('slug', $validated['slug'])->first();
        if ($existing) {
            return $this->validationError(['slug' => ['The slug has already been taken']], 'Slug conflict');
        }

        $createRevision = (bool) ($validated['create_revision'] ?? false);
        $content = $this->contentService->create($validated, (int) $user->id, $createRevision);

        $content->load(['author', 'category', 'tags']);
        $content->setRelation('permissions', $user->getAllPermissions()); // for convenience

        return $this->success($content, 'Content created successfully', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/contents/{content}",
     *     summary="Update content",
     *     tags={"Content"},
     *
     *     @OA\Parameter(name="content", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(type="object")),
     *
     *     @OA\Response(response=200, description="Updated"),
     *     security={{"sanctum":{}}}
     * )
     * Update the specified content.
     */
    public function update(Request $request, Content $content): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        // Check if content is locked by other
        if ($content->locked_by && $content->locked_by !== $user->id) {
            // Check if lock is still valid (60 mins)
            if ($content->locked_at && $content->locked_at->diffInMinutes(now()) < 60) {
                return $this->error('This content is currently being edited by another user', 423);
            }
        }

        // Basic permission check
        if ($content->author_id !== $user->id) {
            if (! $user->hasRole('super') && ! $user->hasRole('admin') && ! $user->can('manage content')) {
                return $this->forbidden('Unauthorized to update this content');
            }
        }

        $this->normalizeContentWriteRequest($request);

        try {
            $rules = [
                'title' => 'sometimes|required|string|max:255',
                'slug' => 'sometimes|nullable|string',
                'excerpt' => 'nullable|string',
                'intro' => 'nullable|string|max:500',
                'body' => 'nullable|string', // Allow null body for drafts
                'featured_image' => 'nullable|string',
                'featured_image_title' => 'nullable|string|max:120',
                'featured_image_caption' => 'nullable|string|max:255',
                'featured_image_position' => 'nullable|in:hero,inline-top,full-bleed',
                'status' => 'sometimes|required|in:draft,pending,published,archived',
                'type' => 'sometimes|required|in:post,page,custom,layout',
                'category_id' => 'nullable|exists:lib_categories,id',
                'tags' => 'nullable|array',
                'tags.*' => 'integer|exists:lib_tags,id',
                'published_at' => 'nullable|date',
                'meta' => 'nullable|array',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:255',
                'og_image' => 'nullable|string',
                'create_revision' => 'boolean',
                'revision_note' => 'nullable|string|max:500',
                'custom_fields' => 'nullable|array',
                'is_featured' => 'boolean',
                'new_tags' => 'nullable|array',
                'new_tags.*' => 'string|max:50',
                'comment_status' => 'nullable|in:1,0,true,false,open,closed',
            ];

            // Require non-empty body only when transitioning to published (partial PUT on published content must not 422)
            if ($request->input('status') === 'published' && $content->status !== 'published') {
                $rules['body'] = 'required|string|min:1';
            }

            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Content update validation failed', ['errors' => $e->errors(), 'input' => $request->all()]);

            return $this->validationError($e->errors());
        }

        // Ownership check
        if (! $user->can('manage content') && ! $user->can('publish content')) {
            if ($content->author_id !== $user->id) {
                return $this->forbidden('You can only update your own content');
            }
        }

        // Approval Workflow: Authors cannot publish directly
        if (isset($validated['status']) && $validated['status'] === 'published') {
            if (! $user->can('publish content')) {
                $validated['status'] = 'pending';
            }
        }

        // Hierarchy Check: Only super-admin or manager can edit if it belongs to someone with higher rank
        // (This is a simplified rank check for content)
        if ($user->can('manage content') && $user->can('publish content')) {
            // Publisher can edit anything
        } elseif ($content->author_id !== $user->id) {
            return $this->forbidden('Unauthorized to edit others content');
        }

        // Approval check: Cannot move back to draft if already published without manage permission
        if ($content->status === 'published' && isset($validated['status']) && $validated['status'] !== 'published') {
            if (! $user->can('manage content')) {
                return $this->forbidden('Unauthorized to unpublish content');
            }
        }

        $createRevision = (bool) ($validated['create_revision'] ?? false);
        $revisionNoteRaw = $request->input('revision_note');
        $revisionNote = is_string($revisionNoteRaw) ? $revisionNoteRaw : null;
        $content = $this->contentService->update($content, $validated, (int) $user->id, $createRevision, $revisionNote);

        $content->load(['author', 'category', 'tags']);

        return $this->success($content, 'Content updated successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/admin/ja/contents/{content}/toggle-featured",
     *     summary="Toggle featured status",
     *     tags={"Content"},
     *
     *     @OA\Parameter(name="content", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Status updated"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function toggleFeatured(Request $request, Content $content): \Illuminate\Http\JsonResponse
    {
        $isFeatured = $this->contentService->toggleFeatured($content);

        return $this->success(['is_featured' => $isFeatured], 'Content featured status updated');
    }

    /**
     * @OA\Post(
     *     path="/api/admin/ja/contents/autosave/{content?}",
     *     summary="Auto-save draft",
     *     tags={"Content"},
     *
     *     @OA\Parameter(name="content", in="path", required=false, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(type="object")),
     *
     *     @OA\Response(response=200, description="Draft auto-saved"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function autosave(Request $request, ?Content $content = null): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $this->normalizeContentWriteRequest($request);

        try {
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'slug' => 'sometimes|string',
                'excerpt' => 'nullable|string',
                'intro' => 'nullable|string|max:500',
                'body' => 'nullable|string',
                'featured_image' => 'nullable|string',
                'featured_image_title' => 'nullable|string|max:120',
                'featured_image_caption' => 'nullable|string|max:255',
                'featured_image_position' => 'nullable|in:hero,inline-top,full-bleed',
                'type' => 'sometimes|in:post,page,custom,layout',
                'category_id' => 'nullable|exists:lib_categories,id',
                'tags' => 'nullable|array',
                'tags.*' => 'integer|exists:lib_tags,id',
                'meta' => 'nullable|array',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:255',
                'og_image' => 'nullable|string',
                'custom_fields' => 'nullable|array',
                'comment_status' => 'nullable|in:1,0,true,false,open,closed',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        // Keep published content published on autosave; only new/unpublished content defaults to draft.
        if ($content && $content->status === 'published') {
            $validated['status'] = 'published';
        } else {
            $validated['status'] = 'draft';
        }

        if ($content) {
            // Update existing content
            // Check if content is locked by another user
            if ($this->contentService->isLockedByOther($content, $user->id)) {
                return $this->error('Content is currently being edited by another user', 423);
            }

            // Validate slug uniqueness if changed
            if (isset($validated['slug']) && $validated['slug'] !== $content->slug) {
                $exists = Content::where('slug', $validated['slug'])
                    ->where('id', '!=', $content->id)
                    ->exists();
                if ($exists) {
                    unset($validated['slug']); // Don't update slug if conflict
                }
            }

            // Use service update but without revisions
            $this->contentService->update($content, $validated, (int) $user->id, false);

            return $this->success([
                'id' => $content->id,
                'saved_at' => $content->updated_at,
            ], 'Draft auto-saved successfully');
        } else {
            // Create new draft
            if (! isset($validated['title']) || empty($validated['title'])) {
                return $this->error('Title is required for auto-save', 422);
            }

            // Generate slug if not provided
            if (! isset($validated['slug']) || empty($validated['slug'])) {
                $validated['slug'] = \Illuminate\Support\Str::slug($validated['title']);
            }

            // Ensure slug is unique for autosave check
            $existing = Content::withTrashed()->where('slug', $validated['slug'])->first();
            if ($existing) {
                return $this->validationError(['slug' => ['Slug already exists']], 'Slug conflict');
            }

            // Use service create
            $content = $this->contentService->create($validated, (int) $user->id, false);

            return $this->success([
                'id' => $content->id,
                'saved_at' => $content->created_at,
            ], 'Draft auto-saved successfully', 201);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/contents/{content}",
     *     summary="Delete content",
     *     tags={"Content"},
     *
     *     @OA\Parameter(name="content", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Deleted"),
     *     security={{"sanctum":{}}}
     * )
     * Remove the specified content.
     */
    public function destroy(Request $request, Content $content): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return $this->unauthorized();
        }

        if ($content->locked_by && $content->locked_by !== $user->id) {
            if ($content->locked_at && $content->locked_at->diffInMinutes(now()) < 60) {
                return $this->error('Cannot delete: Content is currently being edited by another user', 423);
            }
        }

        $this->contentService->delete($content);

        return $this->success(null, 'Content deleted successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/admin/ja/contents/{content}/duplicate",
     *     summary="Duplicate content",
     *     tags={"Content"},
     *
     *     @OA\Parameter(name="content", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=201, description="Content duplicated"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function duplicate(Request $request, Content $content): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $newContent = $this->contentService->duplicate($content, (int) $user->id);

        return $this->success($newContent->load(['author', 'category', 'tags']), 'Content duplicated successfully', 201);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/ja/contents/{content}/approve",
     *     summary="Approve pending content",
     *     tags={"Content"},
     *
     *     @OA\Parameter(name="content", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Content approved"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function approve(Request $request, Content $content): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('approve content')) {
            return $this->forbidden('You do not have permission to approve content');
        }

        if ($content->status !== 'pending') {
            return $this->error('Only pending content can be approved', 400);
        }

        $content->update([
            'status' => 'published',
            'published_at' => $content->published_at ?? now(),
        ]);

        app(CmsCacheService::class)->clearContentCaches($content->id);

        return $this->success($content->load('author'), 'Content approved and published successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/admin/ja/contents/{content}/reject",
     *     summary="Reject pending content",
     *     tags={"Content"},
     *
     *     @OA\Parameter(name="content", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Content rejected"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function reject(Request $request, Content $content): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('approve content')) {
            return $this->forbidden('You do not have permission to reject content');
        }

        if ($content->status !== 'pending') {
            return $this->error('Only pending content can be rejected', 400);
        }

        $content->update([
            'status' => 'draft',
        ]);

        return $this->success($content->load('author'), 'Content rejected and moved back to drafts');
    }

    /**
     * @OA\Post(
     *     path="/api/admin/ja/contents/bulk-action",
     *     summary="Bulk actions for contents",
     *     tags={"Content"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"action", "content_ids"},
     *
     *             @OA\Property(property="action", type="string", enum={"publish", "approve", "reject", "draft", "archive", "delete", "change_category", "restore", "force_delete"}),
     *             @OA\Property(property="content_ids", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="category_id", type="integer")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Bulk action completed"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function bulkAction(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        try {
            $validated = $request->validate([
                'action' => 'required|in:publish,approve,reject,draft,archive,delete,change_category,restore,force_delete',
                'content_ids' => 'required|array',
                'content_ids.*' => 'integer',
                'category_id' => 'required_if:action,change_category|exists:cms_categories,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        // Ownership and permission check for bulk actions
        $idsRaw = $validated['content_ids'];
        $ids = is_array($idsRaw) ? $idsRaw : [];
        $query = Content::withTrashed()->whereIn('id', $ids);

        if (! $user->can('manage content') && ! $user->can('publish content')) {
            $query->where('author_id', $user->id);

            // If author, they can't 'publish' or 'approve' or 'reject'
            if (in_array($validated['action'], ['publish', 'approve', 'reject'])) {
                return $this->forbidden('You do not have permission to perform this action');
            }
        }

        /** @var array<int, int|string> $contentIds */
        $contentIds = $query->pluck('id')->toArray();
        $categoryIdRaw = $validated['category_id'] ?? null;
        $categoryId = is_numeric($categoryIdRaw) ? (int) $categoryIdRaw : null;
        $action = is_string($validated['action']) ? $validated['action'] : '';

        $affected = $this->contentService->bulkAction(
            $action,
            $contentIds,
            $categoryId
        );

        return $this->success(['affected' => $affected], 'Bulk action completed successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/admin/ja/contents/{content}/lock",
     *     summary="Lock content for editing",
     *     tags={"Content"},
     *
     *     @OA\Parameter(name="content", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Content locked"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function lock(Request $request, Content $content): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if ($this->contentService->isLockedByOther($content, (int) $user->id)) {
            // Allow Admins/Super Admins to steal the lock
            if (! $user->hasRole('super') && ! $user->hasRole('admin')) {
                /** @var \Modules\System\Models\User|null $lockedBy */
                $lockedBy = $content->lockedBy;

                return $this->error(
                    'Content is currently being edited by '.($lockedBy ? $lockedBy->name : 'another user'),
                    423,
                    [],
                    'CONTENT_LOCKED',
                    [
                        'locked_by' => $lockedBy,
                        'locked_at' => $content->locked_at,
                    ]
                );
            }
        }

        $this->contentService->lock($content, (int) $user->id);

        $content->refresh()->load('lockedBy');

        return $this->success([
            'is_locked' => true,
            'locked_by' => $content->lockedBy,
            'locked_at' => $content->locked_at,
            'can_unlock' => true,
        ], 'Content locked successfully');
    }

    /**
     * Get current lock status of content.
     */
    public function lockStatus(Request $request, Content $content): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $content->load('lockedBy');

        // Consider lock active for up to 60 minutes, consistent with update() guard.
        $isActiveLock = (bool) $content->locked_by
            && (bool) $content->locked_at
            && $content->locked_at->diffInMinutes(now()) < 60;

        return $this->success([
            'is_locked' => $isActiveLock,
            'locked_by' => $isActiveLock ? $content->lockedBy : null,
            'locked_at' => $isActiveLock ? $content->locked_at : null,
            'can_unlock' => $isActiveLock
                ? ($content->locked_by === $user->id || $user->can('manage content'))
                : true,
        ], 'Content lock status retrieved');
    }

    /**
     * @OA\Post(
     *     path="/api/admin/ja/contents/{content}/unlock",
     *     summary="Unlock content",
     *     tags={"Content"},
     *
     *     @OA\Parameter(name="content", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Content unlocked"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function unlock(Request $request, Content $content): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if ($this->contentService->isLockedByOther($content, (int) $user->id)) {
            if (! $user->can('manage content')) {
                return $this->forbidden('You can only unlock content you locked');
            }
        }

        $this->contentService->unlock($content);

        return $this->success(null, 'Content unlocked successfully');
    }

    /**
     * Restore trashed content.
     *
     * @param  int|string  $id
     */
    public function restore(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('delete content')) {
            return $this->forbidden('You do not have permission to restore content');
        }

        $this->contentService->restore((int) $id);

        return $this->success(null, 'Content restored successfully');
    }

    /**
     * Permanently delete content.
     *
     * @param  int|string  $id
     */
    public function forceDelete(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('delete content') || ! $user->can('manage content')) {
            return $this->forbidden('You do not have permission to permanently delete content');
        }

        $this->contentService->forceDelete((int) $id);

        return $this->success(null, 'Content permanently deleted');
    }

    /**
     * Empty trash.
     */
    public function emptyTrash(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('delete content') || ! $user->can('manage content')) {
            return $this->forbidden('You do not have permission to empty trash');
        }

        $count = $this->contentService->emptyTrash();

        return $this->success(['deleted_count' => $count], 'Trash emptied successfully');
    }
}
