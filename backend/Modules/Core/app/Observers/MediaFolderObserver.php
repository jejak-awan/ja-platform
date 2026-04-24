<?php

namespace Modules\Core\Observers;

use Modules\Cms\Models\MediaFolder;

class MediaFolderObserver
{
    /**
     * Handle the MediaFolder "deleting" event.
     */
    public function deleting(MediaFolder $mediaFolder): void
    {
        if ($mediaFolder->isForceDeleting()) {
            // Permanent delete - handled in controller/service for safety with checks
            return;
        }

        // Soft delete all children recursively
        foreach ($mediaFolder->children as $child) {
            $child->delete();
        }

        // Soft delete all media in this folder
        // This preserves the folder_id relationship while trashing the files
        foreach ($mediaFolder->media as $media) {
            $media->delete();
        }
    }

    /**
     * Handle the MediaFolder "restoring" event.
     */
    public function restoring(MediaFolder $mediaFolder): void
    {
        // Restore all children recursively
        foreach (\Modules\Cms\Models\MediaFolder::onlyTrashed()->where('parent_id', $mediaFolder->id)->get() as $child) {
            $child->restore();
        }

        // Restore all media files that belong to this folder
        // We only restore media that were trashed (deleted_at is NOT null)
        foreach (\Modules\Cms\Models\Media::onlyTrashed()->where('folder_id', $mediaFolder->id)->get() as $media) {
            $media->restore();
        }
    }
}
