<template>
  <ContextMenuContent class="w-56">
    <!-- Folders/Generic Open -->
    <ContextMenuItem
      v-if="isFolder(item)"
      @click="navigateToPath(item.path)"
    >
      <FolderOpen class="w-4 h-4 mr-2" />
      {{ t('modules.core.file_manager.actions.open') }}
    </ContextMenuItem>
    <!-- Files Open -->
    <ContextMenuItem
      v-else
      @click="$emit('preview', item)"
    >
      <Eye class="w-4 h-4 mr-2" />
      {{ t('modules.core.file_manager.actions.open') }}
    </ContextMenuItem>

    <ContextMenuItem
      v-if="!isFolder(item)"
      @click="downloadFile(item)"
    >
      <Download class="w-4 h-4 mr-2" />
      {{ t('modules.core.file_manager.actions.download') }}
    </ContextMenuItem>

    <ContextMenuSeparator />

    <ContextMenuItem @click="copyPath(item)">
      <Link class="w-4 h-4 mr-2" />
      {{ t('modules.core.file_manager.actions.copyPath') }}
    </ContextMenuItem>
        
    <ContextMenuItem
      v-if="!isFolder(item)"
      @click="copyUrl(item)"
    >
      <Copy class="w-4 h-4 mr-2" />
      {{ t('modules.core.file_manager.actions.copyUrl') }}
    </ContextMenuItem>

    <ContextMenuSeparator />

    <ContextMenuItem
      v-if="!isFolder(item) && isArchive(item)"
      @click="extractFile(item)"
    >
      <PackageOpen class="w-4 h-4 mr-2" />
      {{ t('modules.core.file_manager.actions.extract') }}
    </ContextMenuItem>

    <ContextMenuItem @click="compressItems([item.path])">
      <Archive class="w-4 h-4 mr-2" />
      {{ t('modules.core.file_manager.actions.compress') }}
    </ContextMenuItem>

    <ContextMenuSeparator />

    <ContextMenuItem @click="copyToClipboard([item], 'copy')">
      <CopyIcon class="w-4 h-4 mr-2" />
      {{ t('modules.core.file_manager.actions.copy') }}
    </ContextMenuItem>

    <ContextMenuItem
      v-if="isFolder(item) && clipboardCount > 0"
      @click="pasteFromClipboard(item.path)"
    >
      <ClipboardPaste class="w-4 h-4 mr-2" />
      {{ t('modules.core.file_manager.actions.paste') }}
    </ContextMenuItem>

    <ContextMenuSeparator />

    <ContextMenuItem
      class="text-destructive focus:text-destructive"
      @click="deleteItem(item)"
    >
      <Trash2 class="w-4 h-4 mr-2" />
      {{ t('modules.core.file_manager.actions.delete') }}
    </ContextMenuItem>

    <ContextMenuSeparator />

    <!-- Properties -->
    <ContextMenuItem @click="togglePropertiesSidebar">
      <Info class="w-4 h-4 mr-2" />
      {{ t('modules.core.file_manager.properties.title', 'Properties') }}
    </ContextMenuItem>
  </ContextMenuContent>
</template>

<script setup lang="ts">
import { inject, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import FolderOpen from 'lucide-vue-next/dist/esm/icons/folder-open.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import Link from 'lucide-vue-next/dist/esm/icons/link.js';
import Copy from 'lucide-vue-next/dist/esm/icons/copy.js';
import PackageOpen from 'lucide-vue-next/dist/esm/icons/package-open.js';
import Archive from 'lucide-vue-next/dist/esm/icons/archive.js';
import CopyIcon from 'lucide-vue-next/dist/esm/icons/copy.js';
import ClipboardPaste from 'lucide-vue-next/dist/esm/icons/clipboard-paste.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Info from 'lucide-vue-next/dist/esm/icons/info.js';
import { 
    ContextMenuContent, 
    ContextMenuItem, 
    ContextMenuSeparator 
} from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import api from '@/engine/api/client';
import { FileManagerKey } from '@/engine/keys';
import type { FileItem, FolderItem } from '@/modules/Cms/types/file-manager';

defineProps<{
    item: FileItem | FolderItem;
}>();

defineEmits(['preview']);

const { t } = useI18n();
const toast = useToast();

const {
    navigateToPath,
    copyToClipboard,
    pasteFromClipboard,
    deleteItem,
    fetchCurrentPath,
    isArchive,
    clipboard,
    togglePropertiesSidebar
} = inject(FileManagerKey)!;

const isFolder = (item: FileItem | FolderItem): item is FolderItem => 'children' in item || !('extension' in item);
const clipboardCount = computed(() => clipboard.value.items.length);

const copyPath = async (item: FileItem | FolderItem) => {
    try {
        await navigator.clipboard.writeText(item.path);
        toast.success.action(t('modules.core.file_manager.messages.path_copied', 'Path copied'));
    } catch {
        toast.error.default(t('modules.core.file_manager.messages.copy_failed', 'Failed to copy'));
    }
};

const copyUrl = async (file: FileItem) => {
    if (file.url) {
        try {
            await navigator.clipboard.writeText(file.url);
            toast.success.action(t('modules.core.file_manager.messages.url_copied', 'URL copied'));
        } catch {
            toast.error.default(t('modules.core.file_manager.messages.copy_failed', 'Failed to copy'));
        }
    }
};

const downloadFile = (file: FileItem) => {
    if (file.url) {
        const link = document.createElement('a');
        link.href = file.url;
        link.download = file.name;
        link.click();
    }
};

const extractFile = async (file: FileItem) => {
    try {
        await api.post('/manage/file-manager/extract', { path: file.path.replace(/^\//, '') });
        toast.success.action(t('modules.core.file_manager.messages.extracted', 'Extracted'));
        await fetchCurrentPath();
    } catch {
        toast.error.default(t('modules.core.file_manager.messages.extract_failed', 'Extraction failed'));
    }
};

const compressItems = async (paths: string[]) => {
    try {
        await api.post('/manage/file-manager/compress', { paths: paths.map(p => p.replace(/^\//, '')) });
        toast.success.action(t('modules.core.file_manager.messages.compressed', 'Compressed'));
        await fetchCurrentPath();
    } catch {
        toast.error.default(t('modules.core.file_manager.messages.compress_failed', 'Compression failed'));
    }
};
</script>
