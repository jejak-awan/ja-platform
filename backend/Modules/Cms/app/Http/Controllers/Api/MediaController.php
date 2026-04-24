<?php

namespace Modules\Cms\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Cms\Models\Media;
use Modules\Cms\Models\Tag;
use Modules\Cms\Services\MediaService;
use Modules\Core\Http\Controllers\Api\BaseApiController;
use Modules\Core\Models\ActivityLog;
use Modules\Core\Models\User;
use Modules\Core\Services\CacheService;

/**
 * @OA\Tag(name="Media")
 */
class MediaController extends BaseApiController
{
    protected MediaService $mediaService;

    public function __construct()
    {
        $this->mediaService = new MediaService;
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ja/media",
     *     summary="List media files",
     *     tags={"Media"},
     *
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="folder_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="mime_type", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="trashed", in="query", @OA\Schema(type="string", enum={"only", "with"})),
     *
     *     @OA\Response(response=200, description="Media retrieved successfully"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Media::with(['folder', 'usages', 'tags']);

        $user = $request->user();
        if (! $user) {
            return $this->unauthorized();
        }

        // Scope logic - apply BEFORE trash handling
        if (! $user->can('manage media')) {
            $query->where(function ($q) use ($user) {
                $q->where('author_id', $user->id)
                    ->orWhere('is_shared', true);
            });
        }

        // Handle trashed items (applied after scoping)
        $trashed = $request->input('trashed');
        if ($trashed === 'only') {
            $query->onlyTrashed();
        } elseif ($trashed === 'with') {
            $query->withTrashed();
        }

        if ($request->has('mime_type')) {
            $mimeType = $request->input('mime_type');
            if (is_string($mimeType)) {
                $query->where('mime_type', 'like', $mimeType.'%');
            }
        }

        if ($request->has('tag')) {
            $tag = $request->input('tag');
            if (is_string($tag)) {
                $query->whereHas('tags', function ($q) use ($tag) {
                    $q->where('name', $tag)->orWhere('slug', $tag);
                });
            }
        }

        if ($request->has('folder_id')) {
            $folderId = $request->input('folder_id');
            if ($folderId === 'null' || $folderId === null) {
                $query->whereNull('folder_id');
            } elseif (is_scalar($folderId)) {
                $query->where('folder_id', strval($folderId));
            }
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            if (is_string($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('file_name', 'like', '%'.$search.'%')
                        ->orWhere('alt', 'like', '%'.$search.'%');
                });
            }
        }

        if ($request->has('usage')) {
            $usage = $request->input('usage');
            if ($usage === 'used') {
                $query->has('usages');
            } elseif ($usage === 'unused') {
                $query->doesntHave('usages');
            }
        }

        if ($request->has('author_id')) {
            $authorId = $request->input('author_id');
            if (is_scalar($authorId)) {
                $query->where('author_id', strval($authorId));
            }
        }

        if ($request->has('min_size')) {
            $minSize = $request->input('min_size');
            if (is_scalar($minSize)) {
                $query->where('size', '>=', strval($minSize));
            }
        }

        if ($request->has('max_size')) {
            $maxSize = $request->input('max_size');
            if (is_scalar($maxSize)) {
                $query->where('size', '<=', strval($maxSize));
            }
        }

        if ($request->has('date_from')) {
            $dateFrom = $request->input('date_from');
            if (is_string($dateFrom)) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
        }

        if ($request->has('date_to')) {
            $dateTo = $request->input('date_to');
            if (is_string($dateTo)) {
                $query->whereDate('created_at', '<=', $dateTo);
            }
        }

        $perPageRaw = $request->input('per_page', 24);
        $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : 24;
        $media = $query->latest()->paginate($perPage);

        return $this->paginated($media, 'Media retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/media/statistics",
     *     summary="Get media statistics",
     *     tags={"Media"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Statistics retrieved successfully"
     *     ),
     *     security={{"sanctum":{}}}
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        $query = Media::query();

        $user = $request->user();
        if (! $user) {
            return $this->unauthorized();
        }

        // Scope stats if not a manager
        if (! $user->can('manage media')) {
            $query->where('author_id', $user->id);
        }

        $totalMedia = (clone $query)->count();
        $totalSize = (clone $query)->sum('size');
        $trashCount = (clone $query)->onlyTrashed()->count();

        $typeBreakdown = (clone $query)->selectRaw("
            CASE 
                WHEN mime_type LIKE 'image/%' THEN 'image'
                WHEN mime_type LIKE 'video/%' THEN 'video'
                WHEN mime_type LIKE 'application/%' THEN 'document'
                ELSE 'other'
            END as type,
            COUNT(*) as count
        ")
            ->groupBy(DB::raw("
                CASE 
                    WHEN mime_type LIKE 'image/%' THEN 'image'
                    WHEN mime_type LIKE 'video/%' THEN 'video'
                    WHEN mime_type LIKE 'application/%' THEN 'document'
                    ELSE 'other'
                END
            "))
            ->get();

        return $this->success([
            'total_count' => $totalMedia,
            'total_size' => $totalSize,
            'trash_count' => $trashCount,
            'types' => $typeBreakdown,
        ], 'Media statistics retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/media/upload",
     *     summary="Upload a single media file",
     *     tags={"Media"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"file"},
     *
     *                 @OA\Property(property="file", type="string", format="binary"),
     *                 @OA\Property(property="folder_id", type="integer"),
     *                 @OA\Property(property="alt", type="string"),
     *                 @OA\Property(property="caption", type="string")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Media uploaded successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     security={{"sanctum":{}}}
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('upload media') && ! $user->can('manage media')) {
            return $this->forbidden('You do not have permission to upload media');
        }

        try {
            $maxSizeRaw = \Modules\Core\Models\Setting::get('max_upload_size', 10240);
            $maxSize = is_numeric($maxSizeRaw) ? (int) $maxSizeRaw : 10240;

            $request->validate([
                'file' => ['required', 'file', 'max:'.$maxSize],
                'folder_id' => 'nullable|exists:media_folders,id',
                'optimize' => 'boolean',
                'min_width' => 'nullable|integer|min:1',
                'min_height' => 'nullable|integer|min:1',
                'max_width' => 'nullable|integer|min:1',
                'max_height' => 'nullable|integer|min:1',
                'author_id' => 'nullable|exists:users,id',
                'is_shared' => 'sometimes|boolean',
                'caption' => 'nullable|string',
                'alt' => 'nullable|string',
                'tags' => 'nullable|array',
            ]);

            $file = $request->file('file');
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                // Additional image dimension validation if requested
                $mimeType = $file->getMimeType();
                if ($mimeType && str_starts_with($mimeType, 'image/')) {
                    $dimensions = [];
                    if ($request->has('min_width')) {
                        $minWidth = $request->input('min_width');
                        if (is_scalar($minWidth)) {
                            $dimensions[] = 'min_width='.(string) $minWidth;
                        }
                    }
                    if ($request->has('min_height')) {
                        $minHeight = $request->input('min_height');
                        if (is_scalar($minHeight)) {
                            $dimensions[] = 'min_height='.(string) $minHeight;
                        }
                    }
                    if ($request->has('max_width')) {
                        $maxWidth = $request->input('max_width');
                        if (is_scalar($maxWidth)) {
                            $dimensions[] = 'max_width='.(string) $maxWidth;
                        }
                    }
                    if ($request->has('max_height')) {
                        $maxHeight = $request->input('max_height');
                        if (is_scalar($maxHeight)) {
                            $dimensions[] = 'max_height='.(string) $maxHeight;
                        }
                    }

                    if (! empty($dimensions)) {
                        $request->validate([
                            'file' => 'dimensions:'.implode(',', $dimensions),
                        ]);
                    }
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $request->file('file');

        // Determine author
        $authorId = (int) $user->id;
        if ($user->can('manage media') && $request->has('author_id')) {
            $reqAuthorId = $request->input('author_id');
            if (is_numeric($reqAuthorId)) {
                $authorId = (int) $reqAuthorId;
            }
        }

        $folderIdRaw = $request->input('folder_id');
        $folderId = is_numeric($folderIdRaw) ? (int) $folderIdRaw : null;

        $optimizeRaw = $request->input('optimize', config('media.optimize', true));
        $optimize = (bool) $optimizeRaw;

        $media = $this->mediaService->upload(
            $file,
            $folderId,
            $optimize,
            $authorId,
            $user->can('manage media') ? $request->boolean('is_shared') : false,
            [
                'caption' => is_string($cap = $request->input('caption')) ? $cap : '',
                'alt' => is_string($alt = $request->input('alt')) ? $alt : '',
                'tags' => is_array($request->input('tags')) ? $request->input('tags') : [],
            ]
        );

        ActivityLog::log('uploaded_media', $media, [], $user, "Uploaded media: {$media->name}");

        $fresh = $media->fresh();

        return $this->success([
            'media' => $fresh ? $fresh->load(['folder', 'usages']) : $media,
            'url' => $media->url,
        ], 'Media uploaded successfully', 201);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/ja/media/upload-multiple",
     *     summary="Upload multiple media files",
     *     tags={"Media"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"files[]"},
     *
     *                 @OA\Property(property="files[]", type="array", @OA\Items(type="string", format="binary")),
     *                 @OA\Property(property="folder_id", type="integer")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Media uploaded successfully"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function uploadMultiple(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('upload media') && ! $user->can('manage media')) {
            return $this->forbidden('You do not have permission to upload media');
        }

        try {
            $maxSizeRaw = \Modules\Core\Models\Setting::get('max_upload_size', 10240);
            $maxSize = is_numeric($maxSizeRaw) ? (int) $maxSizeRaw : 10240;

            $request->validate([
                'files' => 'required|array',
                'files.*' => ['required', 'file', 'max:'.$maxSize],
                'folder_id' => 'nullable|exists:media_folders,id',
                'optimize' => 'boolean',
                'author_id' => 'nullable|exists:users,id',
                'is_shared' => 'sometimes|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $uploadedMedia = [];
        $files = $request->file('files');
        if (! is_array($files)) {
            return $this->error('Invalid file upload format');
        }

        $folderIdRaw = $request->input('folder_id');
        $folderId = is_numeric($folderIdRaw) ? (int) $folderIdRaw : null;

        $optimizeRaw = $request->input('optimize', config('media.optimize', true));
        $optimize = (bool) $optimizeRaw;

        // Determine author
        $authorId = (int) $user->id;
        if ($user->can('manage media') && $request->has('author_id')) {
            $reqAuthorId = $request->input('author_id');
            if (is_numeric($reqAuthorId)) {
                $authorId = (int) $reqAuthorId;
            }
        }

        foreach ($files as $file) {
            try {
                $media = $this->mediaService->upload(
                    $file,
                    $folderId,
                    $optimize,
                    $authorId,
                    $user->can('manage media') ? $request->boolean('is_shared') : false,
                    [] // Bulk upload usually doesn't have custom metadata per file yet
                );
                $uploadedMedia[] = $media->load(['folder', 'usages']);
                ActivityLog::log('uploaded_media', $media, [], $user, "Uploaded media: {$media->name} (batch)");
            } catch (\Exception $e) {
                \Log::error('Bulk upload failed for a file: '.$e->getMessage());
            }
        }

        return $this->success([
            'media' => $uploadedMedia,
            'count' => count($uploadedMedia),
        ], 'Media uploaded successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/media/{media}",
     *     summary="Get media details",
     *     tags={"Media"},
     *
     *     @OA\Parameter(
     *         name="media",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Media details retrieved"
     *     ),
     *     security={{"sanctum":{}}}
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Media $media)
    {
        $user = $request->user();
        if (! $user) {
            return $this->unauthorized();
        }

        // Scope check
        if (! $user->can('manage media')) {
            if ($media->author_id !== (int) $user->id && ! $media->is_shared) {
                return $this->forbidden('You do not have permission to view this media');
            }
        }

        return $this->success($media->load(['folder', 'usages.model']), 'Media retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ja/media/{media}/usage",
     *     summary="Get media usage information",
     *     tags={"Media"},
     *
     *     @OA\Parameter(name="media", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Usage info retrieved"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function usage(Request $request, Media $media): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return $this->unauthorized();
        }

        // Scope check
        if (! $user->can('manage media')) {
            if ($media->author_id !== (int) $user->id && ! $media->is_shared) {
                return $this->forbidden('You do not have permission to view this media');
            }
        }

        $usageInfo = $this->mediaService->getUsageInfo($media);

        return $this->success($usageInfo, 'Media usage retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/media/{media}",
     *     summary="Update media metadata",
     *     tags={"Media"},
     *
     *     @OA\Parameter(
     *         name="media",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="alt", type="string"),
     *             @OA\Property(property="caption", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Media updated successfully"
     *     ),
     *     security={{"sanctum":{}}}
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Media $media)
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $isOwner = $media->author_id && (int) $media->author_id === (int) $user->id;
        $isManager = $user->can('manage media');

        // Owners can always update their own media
        if (! $isOwner && ! $isManager) {
            if (! $user->can('edit media')) {
                return $this->forbidden('You do not have permission to update media');
            }
            // Has edit media permission but still can't edit others' private media
            if ($media->author_id && ! $media->is_shared) {
                return $this->forbidden('You do not have permission to update this media');
            }
        }

        // Global media (no author) can only be updated by managers
        if (is_null($media->author_id) && ! $isManager) {
            return $this->forbidden('You cannot update global media');
        }

        try {
            /** @var array<string, mixed> $validated */
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'alt' => 'nullable|string',
                'description' => 'nullable|string',
                'caption' => 'nullable|string',
                'author_id' => 'nullable|exists:users,id',
                'is_shared' => 'sometimes|boolean',
                'tags' => 'nullable|array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        // Protection for sensitive fields
        if (! $isManager) {
            unset($validated['is_shared']);
            unset($validated['author_id']);
        }

        $media->update($validated);

        if ($request->has('tags')) {
            $tagsRaw = $request->input('tags');
            $tags = is_array($tagsRaw) ? array_map(fn ($v) => is_scalar($v) ? strval($v) : '', $tagsRaw) : [];
            $this->mediaService->syncTags($media, $tags);
        }

        ActivityLog::log('updated_media', $media, $validated, $user);

        $cacheService = new CacheService;
        $cacheService->clearMediaCaches();

        return $this->success($media->load(['folder', 'usages', 'tags']), 'Media updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/media/{media}",
     *     summary="Delete media",
     *     tags={"Media"},
     *
     *     @OA\Parameter(
     *         name="media",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="permanent",
     *         in="query",
     *         description="Skip trash if true",
     *         required=false,
     *
     *         @OA\Schema(type="boolean")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Media deleted successfully"
     *     ),
     *     security={{"sanctum":{}}}
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Media $media)
    {
        $user = $request->user();
        if (! $user) {
            return $this->unauthorized();
        }

        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        \Illuminate\Support\Facades\Log::channel('media')->info('MediaController::destroy called', [
            'id' => $media->id,
            'name' => $media->name,
            'user' => $user->id ?? 'unknown',
            'permanent' => $request->boolean('permanent'),
        ]);

        $isOwner = $media->author_id && (int) $media->author_id === (int) $user->id;
        $isManager = $user->can('manage media');

        // Owners can always delete their own media
        if (! $isOwner && ! $isManager) {
            // Not owner and not manager - check if they have delete permission for shared items
            if (! $user->can('delete media')) {
                return $this->forbidden('You do not have permission to delete media');
            }
            // Has delete media permission but still can't delete others' private media
            if ($media->author_id && ! $media->is_shared) {
                return $this->forbidden('You do not have permission to delete this media');
            }
        }

        // Global media (no author) can only be deleted by managers
        if (is_null($media->author_id) && ! $isManager) {
            return $this->forbidden('You cannot delete global media');
        }

        $permanent = $request->boolean('permanent', false);

        if ($permanent) {
            return $this->forceDelete($request, (string) $media->id); // Pass ID to forceDelete
        }

        // Check usage before soft delete - return warning but still proceed
        $usageCount = $media->usages()->count();

        $this->mediaService->delete($media, false);
        ActivityLog::log('soft_deleted_media', $media, [], $user);

        $response = [
            'message' => 'Media moved to trash',
            'usage_count' => $usageCount,
        ];

        if ($usageCount > 0) {
            $response['warning'] = "This media is used in {$usageCount} content(s). The references may break.";
        }

        return $this->success($response, 'Media moved to trash');
    }

    /**
     * @OA\Post(
     *     path="/api/admin/ja/media/scan",
     *     summary="Scan storage for new media files",
     *     tags={"Media"},
     *
     *     @OA\Response(response=200, description="Scan completed"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function scan(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage media')) {
            return $this->forbidden('You do not have permission to scan media');
        }

        /** @var array{added: int, files: array<int, string>} $stats */
        $stats = $this->mediaService->scan();

        return $this->success($stats, 'Media scan completed successfully. Added '.$stats['added'].' new files.');
    }

    /**
     * @OA\Post(
     *     path="/api/admin/ja/media/{id}/restore",
     *     summary="Restore trashed media",
     *     tags={"Media"},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Media restored"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function restore(Request $request, int|string $id): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        \Illuminate\Support\Facades\Log::info('MediaController::restore called', [
            'id' => $id,
            'user' => $user->id,
        ]);

        /** @var Media $media */
        $media = Media::onlyTrashed()->findOrFail($id);

        $isOwner = $media->author_id && (int) $media->author_id === (int) $user->id;
        $isManager = $user->can('manage media');

        // Owners can always restore their own media
        if (! $isOwner && ! $isManager) {
            if (! $user->can('delete media')) {
                return $this->forbidden('You do not have permission to restore media');
            }
            // Has delete media permission but still can't restore others' private media
            if ($media->author_id && ! $media->is_shared) {
                return $this->forbidden('You do not have permission to restore this media');
            }
        }

        // Global media (no author) can only be restored by managers
        if (is_null($media->author_id) && ! $isManager) {
            return $this->forbidden('You cannot restore global media');
        }

        $this->mediaService->restore((int) $media->id);
        ActivityLog::log('restored_media', $media, [], $user);

        $fresh = $media->fresh();

        return $this->success($fresh ? $fresh->load(['folder', 'usages']) : $media, 'Media restored successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/ja/media/{id}/force",
     *     summary="Permanently delete media",
     *     tags={"Media"},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="force", in="query", @OA\Schema(type="boolean")),
     *
     *     @OA\Response(response=200, description="Media permanently deleted"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function forceDelete(Request $request, int|string $id): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        \Illuminate\Support\Facades\Log::info('MediaController::forceDelete called', [
            'id' => $id,
            'user' => $user->id,
            'force' => $request->boolean('force'),
        ]);

        /** @var Media $media */
        $media = Media::withTrashed()->findOrFail($id);

        $isOwner = $media->author_id && (int) $media->author_id === (int) $user->id;
        $isManager = $user->can('manage media');

        // Owners can always permanently delete their own media
        if (! $isOwner && ! $isManager) {
            if (! $user->can('delete media')) {
                return $this->forbidden('You do not have permission to permanently delete media');
            }
            // Has delete media permission but still can't delete others' private media
            if ($media->author_id && ! $media->is_shared) {
                return $this->forbidden('You do not have permission to manage this media');
            }
        }

        // Global media (no author) can only be deleted by managers
        if (is_null($media->author_id) && ! $isManager) {
            return $this->forbidden('You cannot delete global media');
        }

        // Safety check: if not forcing and in use, block
        $usageCount = $media->usages()->count();
        if ($usageCount > 0 && ! $request->boolean('force', false)) {
            return $this->validationError([
                'media' => ['Media is currently in use. Use force=true to delete anyway.'],
                'usage_count' => [(string) $usageCount],
            ], 'Media is currently in use.');
        }

        $this->mediaService->delete($media, true);

        return $this->success(null, 'Media permanently deleted');
    }

    /**
     * @OA\Post(
     *     path="/api/admin/ja/media/empty-trash",
     *     summary="Permanently delete all trashed media",
     *     tags={"Media"},
     *
     *     @OA\Response(response=200, description="Trash emptied"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function emptyTrash(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage media') && ! $user->can('delete media')) {
            return $this->forbidden('You do not have permission to empty trash');
        }

        // Get all trashed media (scoped)
        $query = Media::onlyTrashed();
        // Scope
        if (! $user->can('manage media')) {
            $query->where('author_id', $user->id);
        }
        $trashedMedia = $query->get();

        $deletedCount = 0;

        foreach ($trashedMedia as $media) {
            $this->mediaService->delete($media, true);
            ActivityLog::log('permanently_deleted_media', $media, [], $user, "Permanently deleted media via empty trash: {$media->name}");
            $deletedCount++;
        }

        // Also delete trashed folders
        $folderQuery = \Modules\Cms\Models\MediaFolder::onlyTrashed();
        if (! $user->can('manage media')) {
            $folderQuery->where('author_id', $user->id);
        }
        $trashedFolders = $folderQuery->get();

        $deletedFoldersCount = 0;

        foreach ($trashedFolders as $folder) {
            $folder->forceDelete();
            ActivityLog::log('permanently_deleted_media_folder', $folder, [], $user, "Permanently deleted media folder via empty trash: {$folder->name}");
            $deletedFoldersCount++;
        }

        return $this->success([
            'deleted_media' => $deletedCount,
            'deleted_folders' => $deletedFoldersCount,
        ], 'Trash emptied successfully');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkAction(Request $request)
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage media') && ! $user->can('edit media') && ! $user->can('delete media')) {
            return $this->forbidden('You do not have permission to perform bulk actions on media');
        }

        $idsRaw = $request->input('ids', $request->input('media_ids', []));
        $ids = is_array($idsRaw) ? $idsRaw : [];

        $actionRaw = $request->input('action');
        $action = is_string($actionRaw) ? $actionRaw : '';

        if ($action === 'move') {
            $action = 'move_folder';
        }

        $folderIdsRaw = $request->input('folder_ids', []);
        $folderIds = is_array($folderIdsRaw) ? $folderIdsRaw : [];

        if (empty($ids) && empty($folderIds)) {
            return $this->validationError(['ids' => ['Media or Folder IDs are required.']], 'The selected action is invalid.');
        }

        /** @var array<int, int> $existingMediaIds */
        $existingMediaIds = [];
        if (! empty($ids)) {
            $query = Media::withTrashed()->whereIn('id', $ids);
            if (! $user->can('manage media')) {
                $query->where('author_id', $user->id);
            }
            $existingMediaIds = array_map(fn ($id) => is_numeric($id) ? (int) $id : 0, $query->pluck('id')->toArray());
        }

        // Filter Folder IDs
        /** @var array<int, int> $existingFolderIds */
        $existingFolderIds = [];
        if (! empty($folderIds)) {
            $folderQuery = \Modules\Cms\Models\MediaFolder::withTrashed()->whereIn('id', $folderIds);
            if (! $user->can('manage media')) {
                $folderQuery->where('author_id', $user->id);
            }
            $existingFolderIds = array_map(fn ($id) => is_numeric($id) ? (int) $id : 0, $folderQuery->pluck('id')->toArray());
        }

        \Illuminate\Support\Facades\Log::channel('media')->info('MediaController::bulkAction processing', [
            'action' => $action,
            'media_ids' => $existingMediaIds,
            'folder_ids' => $existingFolderIds,
            'user' => $user->id ?? 'unknown',
        ]);

        $folderIdRaw = $request->input('folder_id');
        $folderId = null;
        if ($folderIdRaw === 'null') {
            $folderId = null;
        } elseif (is_numeric($folderIdRaw)) {
            $folderId = (int) $folderIdRaw;
        }

        $altTextRaw = $request->input('alt_text');
        $altText = is_string($altTextRaw) ? $altTextRaw : null;

        $affected = $this->mediaService->bulkAction(
            $action,
            $existingMediaIds,
            $folderId,
            $altText,
            $existingFolderIds
        );

        $totalAffected = $affected['media_count'] + $affected['folder_count'];
        ActivityLog::log('bulk_action_media', null, ['action' => $action, 'media_ids' => $existingMediaIds, 'folder_ids' => $existingFolderIds], $user, "Performed bulk action '{$action}' on {$totalAffected} items");

        return $this->success($affected, 'Bulk action completed successfully');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateThumbnail(Request $request, Media $media)
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage media') && ! $user->can('edit media')) {
            return $this->forbidden('You do not have permission to generate thumbnails');
        }

        // Ownership
        if (! $user->can('manage media')) {
            if ($media->author_id && (int) $media->author_id !== (int) $user->id) {
                return $this->forbidden('You do not have permission to modify this media');
            }
        }

        $mimeType = $media->mime_type;
        if (! str_starts_with($mimeType, 'image/')) {
            return $this->error('Thumbnail can only be generated for images', 400, [], 'INVALID_MEDIA_TYPE');
        }

        $widthRaw = $request->input('width', 300);
        $width = is_numeric($widthRaw) ? (int) $widthRaw : 300;
        $heightRaw = $request->input('height', 300);
        $height = is_numeric($heightRaw) ? (int) $heightRaw : 300;

        $thumbnailPath = $this->mediaService->generateThumbnail($media, $width, $height);

        if ($thumbnailPath) {
            $thumbnailUrl = $media->disk === 'public'
                ? '/storage/'.ltrim($thumbnailPath, '/')
                : Storage::disk($media->disk)->url($thumbnailPath);

            $fresh = $media->fresh();

            return $this->success([
                'thumbnail_url' => $thumbnailUrl,
                'thumbnail_path' => $thumbnailPath,
                'media' => $fresh ? $fresh->load(['folder', 'usages']) : $media,
            ], 'Thumbnail generated successfully');
        }

        return $this->error('Image processing library not available', 500, [], 'IMAGE_PROCESSING_UNAVAILABLE');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function resize(Request $request, Media $media)
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage media') && ! $user->can('edit media')) {
            return $this->forbidden('You do not have permission to resize media');
        }

        // Ownership
        if (! $user->can('manage media')) {
            if ($media->author_id && (int) $media->author_id !== (int) $user->id) {
                return $this->forbidden('You do not have permission to modify this media');
            }
        }

        // Only for images
        $mimeType = $media->mime_type;
        if (! str_starts_with($mimeType, 'image/')) {
            return $this->validationError(['media' => ['Resize can only be performed on images']], 'Resize can only be performed on images');
        }

        try {
            /** @var array{width: int, height: int|null, quality: int|null} $validated */
            $validated = $request->validate([
                'width' => 'required|integer|min:1|max:5000',
                'height' => 'nullable|integer|min:1|max:5000',
                'quality' => 'nullable|integer|min:1|max:100',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        try {
            $fullPath = Storage::disk($media->disk)->path($media->path);
            $width = $validated['width'];
            $height = $validated['height'] ?? null;
            $quality = $validated['quality'] ?? 85;

            // Use Intervention Image v3 API
            if (class_exists(\Intervention\Image\ImageManager::class)) {
                $driver = null;
                if (extension_loaded('gd')) {
                    $driver = new \Intervention\Image\Drivers\Gd\Driver;
                } elseif (extension_loaded('imagick')) {
                    $driver = new \Intervention\Image\Drivers\Imagick\Driver;
                }

                if ($driver) {
                    $manager = new \Intervention\Image\ImageManager($driver);
                    $image = $manager->read($fullPath);

                    // Resize image
                    if ($height) {
                        $image->resize($width, $height);
                    } else {
                        $image->scale(width: $width);
                    }

                    // Save resized image
                    $image->save($fullPath, quality: $quality);

                    // Update file size
                    $media->update([
                        'size' => filesize($fullPath),
                    ]);

                    ActivityLog::log('resized_media', $media, ['width' => $width, 'height' => $height], $user);

                    $fresh = $media->fresh();

                    return $this->success([
                        'media' => $fresh ? $fresh->load(['folder', 'usages']) : $media,
                        'url' => $media->disk === 'public'
                            ? '/storage/'.ltrim($media->path, '/')
                            : Storage::disk($media->disk)->url($media->path),
                    ], 'Image resized successfully');
                }
            }

            return $this->error('Image processing library not available', 500, [], 'IMAGE_PROCESSING_UNAVAILABLE');
        } catch (\Exception $e) {
            \Log::error('Image resize failed: '.$e->getMessage());

            return $this->error('Failed to resize image: '.$e->getMessage(), 500, [], 'IMAGE_RESIZE_ERROR');
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadZip(Request $request)
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage media') && ! $user->can('view media')) {
            // Anyone who can view can download?
            return $this->forbidden('You do not have permission to download media');
        }

        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:media,id',
        ]);

        $idsRaw = $request->input('ids');
        $ids = is_array($idsRaw) ? $idsRaw : [];
        $query = Media::whereIn('id', $ids);

        // Scope
        if (! $user->can('manage media')) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('author_id')->orWhere('author_id', $user->id);
            });
        }

        $media = $query->get();

        if ($media->isEmpty()) {
            return $this->error('No media files found or permission denied', 404, [], 'NO_MEDIA_FOUND');
        }

        // Create temporary ZIP file
        $zipFileName = 'media-'.now()->format('Y-m-d-His').'.zip';
        $zipPath = (string) storage_path('app/temp/'.$zipFileName);

        // Ensure temp directory exists
        $tempDir = (string) storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return $this->error('Failed to create ZIP file', 500, [], 'ZIP_CREATION_FAILED');
        }

        foreach ($media as $item) {
            $filePath = Storage::disk($item->disk)->path($item->path);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $item->file_name ?: $item->name);
            }
        }

        $zip->close();

        // Return file download
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request, Media $media)
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage media') && ! $user->can('edit media')) {
            return $this->forbidden('You do not have permission to edit media');
        }

        // Ownership
        if (! $user->can('manage media')) {
            if ($media->author_id && (int) $media->author_id !== (int) $user->id) {
                return $this->forbidden('You do not have permission to edit this media');
            }
        }

        // Only for images
        $mimeType = $media->mime_type;
        if (! str_starts_with($mimeType, 'image/')) {
            return $this->validationError(['media' => ['Image editing can only be performed on images']], 'Image editing can only be performed on images');
        }

        try {
            $request->validate([
                'image' => 'required|image|max:10240', // 10MB max
                'save_as_new' => 'nullable|boolean',
                'custom_filename' => 'nullable|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        try {
            $saveAsNew = $request->boolean('save_as_new', false);
            $customFilenameRaw = $request->input('custom_filename');
            $customFilename = is_string($customFilenameRaw) ? $customFilenameRaw : null;
            $imageFile = $request->file('image');

            if (! $imageFile instanceof \Illuminate\Http\UploadedFile) {
                return $this->error('No image file uploaded');
            }

            // Use Intervention Image v3 API
            if (class_exists(\Intervention\Image\ImageManager::class)) {
                $driver = null;
                if (extension_loaded('gd')) {
                    $driver = new \Intervention\Image\Drivers\Gd\Driver;
                } elseif (extension_loaded('imagick')) {
                    $driver = new \Intervention\Image\Drivers\Imagick\Driver;
                }

                if ($driver) {
                    $manager = new \Intervention\Image\ImageManager($driver);
                    $image = $manager->read($imageFile->getRealPath());

                    if ($saveAsNew) {
                        // Save as new version
                        $originalPath = (string) $media->path;
                        $pathInfo = pathinfo($originalPath);
                        $extension = $pathInfo['extension'] ?? 'jpg';
                        $filename = $pathInfo['filename'];
                        $dirname = $pathInfo['dirname'] ?? '';

                        if ($customFilename) {
                            // Sanitize filename to be safe
                            $customFilenameBase = (string) pathinfo($customFilename, PATHINFO_FILENAME);
                            $customFilenameSlug = \Illuminate\Support\Str::slug($customFilenameBase);
                            $newFileName = $customFilenameSlug.'.'.$extension;
                        } else {
                            $newFileName = $filename.'_edited_'.time().'.'.$extension;
                        }

                        $newPath = ($dirname !== '' ? $dirname.'/' : '').$newFileName;

                        // Check if file exists, if so maybe error? Or append unique?
                        if ($customFilename && Storage::disk($media->disk)->exists($newPath)) {
                            $newFileName = $customFilenameSlug.'_'.time().'.'.$extension;
                            $newPath = ($dirname !== '' ? $dirname.'/' : '').$newFileName;
                        }

                        // Save to same disk
                        $fullPath = Storage::disk($media->disk)->path($newPath);
                        $directory = dirname($fullPath);
                        if (! is_dir($directory)) {
                            mkdir($directory, 0755, true);
                        }

                        $image->save($fullPath);

                        // Create new media record
                        $newMedia = Media::create([
                            'name' => pathinfo($newFileName, PATHINFO_FILENAME),
                            'file_name' => $newFileName,
                            'path' => $newPath,
                            'mime_type' => $media->mime_type,
                            'size' => filesize($fullPath),
                            'disk' => $media->disk,
                            'folder_id' => $media->folder_id,
                            'alt' => $media->alt,
                            'description' => $media->description,
                            'author_id' => $user->id,
                        ]);

                        ActivityLog::log('edited_media', $newMedia, [], $user, "Created new version of media: {$newMedia->name}");

                        // Generate thumbnail if needed
                        if (config('media.generate_thumbnails', true)) {
                            try {
                                $thumbnailPath = $this->mediaService->generateThumbnail($newMedia);
                                $newMedia->update(['thumbnail_path' => $thumbnailPath]);
                            } catch (\Exception $e) {
                                \Log::warning('Thumbnail generation failed for edited image: '.$e->getMessage());
                            }
                        }

                        return $this->success($newMedia->load(['folder', 'usages']), 'Image saved as new version successfully');
                    } else {
                        // Overwrite existing
                        $fullPath = Storage::disk($media->disk)->path((string) $media->path);
                        $image->save($fullPath);

                        // Update file size
                        $media->update([
                            'size' => filesize($fullPath),
                        ]);

                        // Regenerate thumbnail
                        if (config('media.generate_thumbnails', true) && $media->thumbnail_path) {
                            try {
                                $thumbnailPath = $this->mediaService->generateThumbnail($media);
                                ActivityLog::log('regenerated_thumbnail', $media, [], $user);
                                $media->update(['thumbnail_path' => $thumbnailPath]);
                            } catch (\Exception $e) {
                                \Log::warning('Thumbnail regeneration failed: '.$e->getMessage());
                            }
                        }

                        // Invalidate cache
                        app(CacheService::class)->invalidateByPattern("media:{$media->id}:*");

                        $fresh = $media->fresh();

                        return $this->success($fresh ? $fresh->load(['folder', 'usages']) : $media, 'Image updated successfully');
                    }
                }
            }

            return $this->error('Image processing library not available', 500, [], 'IMAGE_PROCESSING_UNAVAILABLE');
        } catch (\Exception $e) {
            \Log::error('Image editing failed: '.$e->getMessage());

            return $this->error('Failed to edit image: '.$e->getMessage(), 500, [], 'IMAGE_EDIT_ERROR');
        }
    }

    /**
     * Get available filters for media
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function filters()
    {
        $tags = Tag::whereHas('media')->get(['id', 'name', 'slug']);

        $authors = User::whereHas('media')
            ->get(['id', 'name', 'avatar']);

        return $this->success([
            'tags' => $tags,
            'authors' => $authors,
        ]);
    }
}
