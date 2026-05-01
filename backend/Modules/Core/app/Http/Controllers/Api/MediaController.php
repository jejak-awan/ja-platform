<?php

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Core\Models\Media;
use Modules\Core\Http\Controllers\Api\BaseApiController;

class MediaController extends BaseApiController
{
    /**
     * List ALL media files across all modules
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Media::with(['folder', 'tags']);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%")
                  ->orWhere('alt', 'like', "%{$search}%");
            });
        }

        // Filter by Module
        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        // Filter by Mime Type
        if ($request->filled('mime_type')) {
            $query->where('mime_type', 'like', $request->input('mime_type') . '%');
        }

        // Handle Trashed
        if ($request->input('trashed') === 'only') {
            $query->onlyTrashed();
        } elseif ($request->input('trashed') === 'with') {
            $query->withTrashed();
        }

        $perPage = (int) $request->input('per_page', 30);
        $media = $query->latest()->paginate($perPage);

        return $this->success($media, 'Global media retrieved successfully');
    }

    /**
     * Get media stats by module
     */
    public function stats(): \Illuminate\Http\JsonResponse
    {
        $stats = Media::selectRaw('module, count(*) as count, sum(size) as total_size')
            ->groupBy('module')
            ->get();

        return $this->success($stats, 'Media statistics retrieved successfully');
    }

    /**
     * Delete media permanently (Global)
     */
    public function destroy(int|string $id): \Illuminate\Http\JsonResponse
    {
        /** @var Media $media */
        $media = Media::withTrashed()->findOrFail($id);
        
        // Physical deletion logic should ideally be in a Service
        // For now, we use the model's forceDelete if requested
        $media->forceDelete();

        return $this->success(null, 'Media permanently deleted from system');
    }
}
