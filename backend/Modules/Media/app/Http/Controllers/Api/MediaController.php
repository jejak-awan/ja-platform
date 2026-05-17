<?php

namespace Modules\Media\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Media\Contracts\MediaServiceInterface;
use Modules\Media\Models\File;
use Modules\Media\Models\Folder;

class MediaController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected MediaServiceInterface $mediaService)
    {
    }

    /**
     * Display a listing of the media files.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', File::class);
        $query = File::with(['folder']);

        if ($request->input('trashed') === 'only') {
            $query->onlyTrashed();
        }

        if ($request->has('folder_id')) {
            $folderId = $request->input('folder_id');
            if ($folderId === 'null' || $folderId === null) {
                $query->whereNull('folder_id');
            } else {
                $query->where('folder_id', $folderId);
            }
        }

        if ($request->has('mime_type')) {
            $mimeType = $request->input('mime_type');
            $query->where('mime_type', 'like', "{$mimeType}/%");
        }

        if ($request->has('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? $searchRaw : '';
            $query->where(function($q) use ($search): void {
                $searchStr = strtolower($search);
                $q->where(\Illuminate\Support\Facades\DB::raw('lower(name)'), 'like', "%{$searchStr}%")
                  ->orWhere(\Illuminate\Support\Facades\DB::raw('lower(file_name)'), 'like', "%{$searchStr}%")
                  ->orWhere(\Illuminate\Support\Facades\DB::raw('lower(alt)'), 'like', "%{$searchStr}%");
            });
        }

        $perPage = $request->input('per_page', 24);
        $files = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $files,
            'message' => 'Media retrieved successfully'
        ]);
    }

    /**
     * Upload a new media file.
     */
    public function upload(Request $request)
    {
        $this->authorize('create', File::class);
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB to match test expectations
            'folder_id' => 'nullable|exists:srv_media_folders,id',
            'is_shared' => 'sometimes|boolean',
            'caption' => 'nullable|string',
            'alt' => 'nullable|string',
            'module' => 'nullable|string',
        ]);

        $file = $request->file('file');
        
        $media = $this->mediaService->upload(
            $file,
            $request->input('folder_id'),
            true, // optimize
            is_scalar(Auth::id()) ? (string) Auth::id() : null,
            $request->boolean('is_shared', false),
            [
                'caption' => $request->input('caption'),
                'alt' => $request->input('alt'),
            ],
            null, // subpath
            $request->input('module', 'system')
        );

        return response()->json([
            'success' => true,
            'data' => [
                'media' => $media->load('folder'),
                'url' => $media->url,
            ],
            'message' => 'Media uploaded successfully'
        ], 201);
    }

    /**
     * Display the specified media file.
     */
    public function show(File $file)
    {
        $this->authorize('view', $file);
        return response()->json([
            'success' => true,
            'data' => $file->load('folder'),
            'message' => 'Media retrieved successfully'
        ]);
    }

    /**
     * Update the specified media file.
     */
    public function update(Request $request, File $file)
    {
        $this->authorize('update', $file);
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'alt' => 'nullable|string',
            'description' => 'nullable|string',
            'caption' => 'nullable|string',
            'is_shared' => 'sometimes|boolean',
            'tags' => 'sometimes|array',
        ]);

        $file->update($validated);

        if ($request->has('tags')) {
            $this->mediaService->syncTags($file, $request->input('tags'));
        }

        return response()->json([
            'success' => true,
            'data' => $file,
            'message' => 'Media updated successfully'
        ]);
    }

    /**
     * Remove the specified media file.
     */
    public function destroy(Request $request, File $file)
    {
        $this->authorize('delete', $file);
        $permanent = $request->boolean('permanent', false);
        $this->mediaService->delete($file, $permanent);

        return response()->json([
            'success' => true,
            'message' => $permanent ? 'Media permanently deleted' : 'Media moved to trash'
        ]);
    }

    /**
     * Bulk actions on media files.
     */
    public function bulk(Request $request)
    {
        // Bulk actions usually require manage media or specific ones
        $this->authorize('viewAny', File::class);
        $request->validate([
            'action' => 'required|string|in:delete,delete_permanent,restore,move',
            'media_ids' => 'required_without:ids|array',
            'ids' => 'required_without:media_ids|array',
            'folder_id' => 'nullable|required_if:action,move|exists:srv_media_folders,id',
        ]);

        $mediaIds = $request->input('media_ids', $request->input('ids'));

        $result = $this->mediaService->bulkAction(
            $request->input('action'),
            $mediaIds,
            $request->input('folder_id')
        );

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Bulk action completed'
        ]);
    }

    /**
     * Restore a soft-deleted media file.
     */
    public function restore(string $id)
    {
        $file = $this->mediaService->restore($id);
        if (!$file instanceof \Modules\Media\Models\File) {
            return response()->json(['success' => false, 'message' => 'Media not found or not in trash'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $file,
            'message' => 'Media restored successfully'
        ]);
    }

    /**
     * Generate thumbnail for media.
     */
    public function thumbnail(File $file)
    {
        $this->authorize('update', $file);
        $path = $this->mediaService->generateThumbnail($file);
        
        if (!$path) {
            return response()->json(['success' => false, 'message' => 'Failed to generate thumbnail'], 400);
        }

        return response()->json([
            'success' => true,
            'data' => ['path' => $path, 'url' => \Illuminate\Support\Facades\Storage::disk($file->disk)->url($path)],
            'message' => 'Thumbnail generated successfully'
        ]);
    }

    /**
     * Resize image media.
     */
    public function resize(Request $request, File $file)
    {
        $this->authorize('update', $file);
        $request->validate([
            'width' => 'required_without:height|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'quality' => 'nullable|integer|min:1|max:100',
        ]);

        $success = $this->mediaService->resize(
            $file,
            (int) ($request->input('width') ?? 0),
            $request->has('height') ? (int) $request->input('height') : null,
            (int) ($request->input('quality') ?? 85)
        );

        if (!$success) {
            return response()->json(['success' => false, 'message' => 'Failed to resize image'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Image resized successfully'
        ]);
    }

    /**
     * Get media usage information.
     */
    public function usage(File $file): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $file);
        $usage = $this->mediaService->getUsageInfo($file);

        return response()->json([
            'success' => true,
            'data' => $usage,
            'message' => 'Media usage retrieved successfully'
        ]);
    }

    /**
     * Empty trash.
     */
    public function emptyTrash(): \Illuminate\Http\JsonResponse
    {
        // This is a bulk action usually
        $files = File::onlyTrashed()->get();
        foreach ($files as $file) {
            $this->mediaService->delete($file, true);
        }

        return response()->json([
            'success' => true,
            'message' => 'Trash emptied successfully'
        ]);
    }

    /**
     * Get media statistics.
     */
    public function statistics(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', File::class);
        $stats = $this->mediaService->getStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Statistics retrieved successfully'
        ]);
    }

    /**
     * Get media filters.
     */
    public function filters(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', File::class);

        $authorIds = File::whereNotNull('author_id')
            ->distinct()
            ->pluck('author_id');

        $authors = \Modules\System\Models\User::whereIn('id', $authorIds)
            ->select('id', 'name')
            ->get();

        return response()->json([
            'success' => true,
            'authors' => $authors,
            'message' => 'Filters retrieved successfully'
        ]);
    }
}
