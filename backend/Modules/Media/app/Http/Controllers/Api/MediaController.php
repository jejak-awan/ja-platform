<?php

namespace Modules\Media\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Media\Contracts\MediaServiceInterface;
use Modules\Media\Models\File;
use Modules\Media\Models\Folder;

class MediaController extends Controller
{
    protected MediaServiceInterface $mediaService;

    public function __construct(MediaServiceInterface $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Display a listing of the media files.
     */
    public function index(Request $request)
    {
        $query = File::with(['folder']);

        if ($request->has('folder_id')) {
            $folderId = $request->input('folder_id');
            if ($folderId === 'null' || $folderId === null) {
                $query->whereNull('folder_id');
            } else {
                $query->where('folder_id', $folderId);
            }
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%")
                  ->orWhere('alt', 'like', "%{$search}%");
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
        $request->validate([
            'file' => 'required|file|max:20480', // 20MB
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
            Auth::id(),
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
            'data' => $media->load('folder'),
            'message' => 'Media uploaded successfully'
        ], 201);
    }

    /**
     * Display the specified media file.
     */
    public function show(File $file)
    {
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
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'alt' => 'nullable|string',
            'description' => 'nullable|string',
            'caption' => 'nullable|string',
            'is_shared' => 'sometimes|boolean',
        ]);

        $file->update($validated);

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
        $request->validate([
            'action' => 'required|string|in:delete,delete_permanent,restore,move',
            'media_ids' => 'required|array',
            'folder_id' => 'nullable|required_if:action,move|exists:srv_media_folders,id',
        ]);

        $result = $this->mediaService->bulkAction(
            $request->input('action'),
            $request->input('media_ids'),
            $request->input('folder_id')
        );

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Bulk action completed'
        ]);
    }
}
