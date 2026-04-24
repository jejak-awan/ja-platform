<?php

namespace Modules\Cms\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Cms\Models\MediaFolder;
use Modules\Core\Http\Controllers\Api\BaseApiController;

class MediaFolderController extends BaseApiController
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = $request->user();
            /** @var \Modules\Core\Models\User|null $user */
            $query = MediaFolder::query();

            // Scope logic
            if ($user && ! $user->can('manage media')) {
                $query->where(function ($q) use ($user) {
                    $q->where('author_id', $user->id)
                        ->orWhere('is_shared', true);
                });
            } elseif (! $user) {
                $query->where('is_shared', true);
            }

            // Handle trashed items
            if ($request->input('trashed') === 'only') {
                $query->onlyTrashed();
            } elseif ($request->input('trashed') === 'with') {
                $query->withTrashed();
            }

            if ($request->has('parent_id')) {
                $parentIdRaw = $request->parent_id;
                if ($parentIdRaw === 'null' || $parentIdRaw === null) {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $parentIdRaw);
                }
            }

            // Get tree structure if requested
            if ($request->has('tree') && $request->boolean('tree')) {
                // Apply same scope to tree root finding
                // We need to apply the query constraint to the root query
                // Note: The original code used MediaFolder::whereNull('parent_id') direct call.
                // We must use the $query builder which has our scopes.
                $foldersQuery = $query->clone()->whereNull('parent_id');

                $folders = $foldersQuery->with(['children' => function ($q) use ($request, $user) {
                    if ($request->input('trashed') === 'only') {
                        $q->onlyTrashed();
                    } elseif ($request->input('trashed') === 'with') {
                        $q->withTrashed();
                    }
                    // We must also scope children?
                    if ($user && ! $user->can('manage media')) {
                        $q->where(function ($sq) use ($user) {
                            $sq->where('author_id', $user->id)
                                ->orWhere('is_shared', true);
                        });
                    }

                    $q->orderBy('sort_order')->with('children'); // Eager load sub-children
                }])
                    ->orderBy('sort_order')
                    ->get();

                // Transform to include is_trashed and children_count for frontend
                $transformFolder = function ($folder) use (&$transformFolder) {
                    $children = $folder->children ? $folder->children->map($transformFolder)->toArray() : [];

                    return [
                        'id' => $folder->id,
                        'name' => $folder->name,
                        'slug' => $folder->slug,
                        'parent_id' => $folder->parent_id,
                        'sort_order' => $folder->sort_order,
                        'is_trashed' => $folder->trashed(),
                        'children_count' => count($children),
                        'children' => $children,
                        'created_at' => $folder->created_at,
                        'updated_at' => $folder->updated_at,
                        'deleted_at' => $folder->deleted_at,
                        'author_id' => $folder->author_id,
                        'is_shared' => $folder->is_shared,
                    ];
                };

                $foldersData = $folders->map($transformFolder);

                return $this->success($foldersData, 'Media folders tree retrieved successfully');
            }

            $folders = $query->with('parent')
                ->orderBy('sort_order')
                ->get();

            $foldersData = $folders->map(function ($folder) {
                return [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'slug' => $folder->slug,
                    'parent_id' => $folder->parent_id,
                    'sort_order' => $folder->sort_order,
                    'is_trashed' => $folder->trashed(),
                    'parent' => $folder->parent ? [
                        'id' => $folder->parent->id,
                        'name' => $folder->parent->name,
                        'slug' => $folder->parent->slug,
                    ] : null,
                    'created_at' => $folder->created_at,
                    'updated_at' => $folder->updated_at,
                    'deleted_at' => $folder->deleted_at,
                    'author_id' => $folder->author_id,
                ];
            });

            return $this->success($foldersData, 'Media folders retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Media folders index error: '.$e->getMessage());

            return $this->success([], 'Media folders retrieved successfully');
        }
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\Core\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        // Just checking 'create media' which implies upload rights?
        // Authors with 'upload media' should be able to create folders for organization
        if (! $user->can('upload media') && ! $user->can('manage media')) {
            return $this->forbidden('You do not have permission to create media folders');
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'parent_id' => 'nullable|exists:media_folders,id',
                'sort_order' => 'integer|min:0',
                'author_id' => 'nullable|exists:users,id',
                'is_shared' => 'sometimes|boolean',
            ]);

            $name = is_string($validated['name']) ? $validated['name'] : '';
            $validated['slug'] = Str::slug($name);

            // Allow Admin to set author and shared status
            if ($user->can('manage media')) {
                // can set author_id and is_shared
            } else {
                $validated['author_id'] = $user->id;
                $validated['is_shared'] = false;
            }

            // Ensure slug is unique within the same parent
            $originalSlug = is_string($validated['slug']) ? $validated['slug'] : '';
            $counter = 1;
            $parentQuery = MediaFolder::where('parent_id', $validated['parent_id'] ?? null);
            while ($parentQuery->clone()->where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug.'-'.$counter++;
            }

            $folder = MediaFolder::create($validated);

            return $this->success($folder, 'Folder created successfully');
        } catch (\Exception $e) {
            Log::error('Media folder create error: '.$e->getMessage());

            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  string|int  $id
     */
    public function show($id): \Illuminate\Http\JsonResponse
    {
        /** @var MediaFolder $mediaFolder */
        $mediaFolder = MediaFolder::withTrashed()->findOrFail($id);
        $user = request()->user();
        /** @var \Modules\Core\Models\User|null $user */

        // Scope check
        if ($user && ! $user->can('manage media')) {
            if ($mediaFolder->author_id && $mediaFolder->author_id !== $user->id) {
                return $this->forbidden('You do not have permission to view this folder');
            }
        }

        return $this->success($mediaFolder->load(['parent', 'children']), 'Folder retrieved successfully');
    }

    public function update(Request $request, MediaFolder $mediaFolder): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\Core\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage media') && ! $user->can('edit media')) {
            // Allow 'edit media' permission
            return $this->forbidden('You do not have permission to update media folders');
        }

        // Ownership check
        if (! $user->can('manage media')) {
            if ($mediaFolder->author_id && $mediaFolder->author_id !== $user->id) {
                return $this->forbidden('You do not have permission to update this folder');
            }
            if (is_null($mediaFolder->author_id)) {
                // Cannot edit Global folders
                return $this->forbidden('You cannot update global folders');
            }
            unset($request['author_id']);
        }

        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'parent_id' => 'nullable|exists:media_folders,id',
                'sort_order' => 'integer|min:0',
                'author_id' => 'nullable|exists:users,id',
            ]);

            if (isset($validated['name'])) {
                $name = is_string($validated['name']) ? $validated['name'] : '';
                $validated['slug'] = Str::slug($name);

                // Ensure slug is unique
                $originalSlug = $validated['slug'];
                $counter = 1;
                $parentIdRaw = $validated['parent_id'] ?? $mediaFolder->parent_id;
                $parentId = $parentIdRaw !== null ? (int) $parentIdRaw : null;
                $parentQuery = MediaFolder::where('parent_id', $parentId);
                while ($parentQuery->clone()->where('slug', $validated['slug'])->where('id', '!=', $mediaFolder->id)->exists()) {
                    $validated['slug'] = $originalSlug.'-'.$counter++;
                }
            }

            $mediaFolder->update($validated);

            return $this->success($mediaFolder, 'Folder updated successfully');
        } catch (\Exception $e) {
            Log::error('Media folder update error: '.$e->getMessage());

            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(Request $request, MediaFolder $mediaFolder): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\Core\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage media') && ! $user->can('delete media')) {
            return $this->forbidden('You do not have permission to delete media folders');
        }

        // Ownership check
        if (! $user->can('manage media')) {
            if ($mediaFolder->author_id && $mediaFolder->author_id !== $user->id) {
                return $this->forbidden('You do not have permission to delete this folder');
            }
            if (is_null($mediaFolder->author_id)) {
                return $this->forbidden('You cannot delete global folders');
            }
        }

        $permanent = $request->boolean('permanent', false);

        if ($permanent) {
            return $this->forceDelete($request, (string) $mediaFolder->id); // Pass ID as string
        }

        // Soft delete - recursive behavior is handled by MediaFolderObserver
        $mediaFolder->delete();

        return $this->success(null, 'Folder moved to trash');
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  string|int  $id
     */
    public function restore(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\Core\Models\User|null $user */
        if (! $user || ! $user->can('manage media')) {
            return $this->forbidden('You do not have permission to restore media folders');
        }

        /** @var MediaFolder $folder */
        $folder = MediaFolder::onlyTrashed()->findOrFail($id);
        // Recursive restore is handled by MediaFolderObserver
        $folder->restore();

        return $this->success($folder->fresh(), 'Folder restored successfully');
    }

    /**
     * Permanently remove the specified resource from storage.
     *
     * @param  string|int  $id
     */
    public function forceDelete(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\Core\Models\User|null $user */
        if (! $user || ! $user->can('manage media')) {
            return $this->forbidden('You do not have permission to permanently delete media folders');
        }

        /** @var MediaFolder $folder */
        $folder = MediaFolder::withTrashed()->findOrFail($id);

        $this->recursiveForceDelete($folder);

        return $this->success(null, 'Folder and its contents permanently deleted');
    }

    /**
     * Recursively delete all children folders and media
     */
    protected function recursiveForceDelete(MediaFolder $folder): void
    {
        // 1. Permanently delete all media files in this folder
        $mediaItems = \Modules\Cms\Models\Media::where('folder_id', $folder->id)->withTrashed()->get();
        foreach ($mediaItems as $media) {
            /** @var \Modules\Cms\Models\Media $media */
            $media->forceDelete();
        }

        // 2. Recurse into children folders
        $children = \Modules\Cms\Models\MediaFolder::where('parent_id', $folder->id)->withTrashed()->get();
        foreach ($children as $child) {
            /** @var MediaFolder $child */
            $this->recursiveForceDelete($child);
        }

        // 3. Permanently delete the folder itself
        $folder->forceDelete();
    }

    public function move(Request $request, MediaFolder $mediaFolder): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\Core\Models\User|null $user */
        if (! $user || ! $user->can('manage media')) {
            return $this->forbidden('You do not have permission to move media folders');
        }

        $validated = $request->validate([
            'parent_id' => 'nullable|exists:media_folders,id',
            'sort_order' => 'integer|min:0',
        ]);

        // Prevent setting self as parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $mediaFolder->id) {
            return $this->validationError(['parent_id' => ['Folder cannot be its own parent']], 'Folder cannot be its own parent');
        }

        // Prevent circular reference
        if (isset($validated['parent_id']) && $validated['parent_id']) {
            $parentId = (int) $validated['parent_id'];
            /** @var MediaFolder|null $parent */
            $parent = MediaFolder::find($parentId);
            if ($parent) {
                $checkParent = $parent;
                while ($checkParent && $checkParent->parent_id) {
                    if ($checkParent->parent_id == $mediaFolder->id) {
                        return $this->validationError(['parent_id' => ['Cannot set parent to a child folder']], 'Cannot set parent to a child folder');
                    }
                    /** @var MediaFolder|null $nextParent */
                    $nextParent = MediaFolder::find($checkParent->parent_id);
                    $checkParent = $nextParent;
                }
            }
        }

        $mediaFolder->update($validated);

        return $this->success($mediaFolder->load(['parent', 'children']), 'Media folder moved successfully');
    }
}
