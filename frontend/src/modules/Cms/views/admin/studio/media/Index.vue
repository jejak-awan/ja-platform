<template>
  <div class="h-full flex flex-col">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-foreground">
          {{ $t('features.media.title') }}
        </h1>
        <p class="text-muted-foreground">
          {{ $t('features.media.description') }}
        </p>
      </div>
      <div class="flex items-center space-x-3">
        <Button 
          variant="outline"
          @click="showFolderModal = true"
        >
          <FolderPlus class="w-4 h-4 mr-2" />
          {{ $t('features.media.newFolder') }}
        </Button>
        <Button
          type="button"
          @click="showUploadModal = true"
        >
          <Plus class="w-4 h-4 mr-2" />
          {{ $t('features.media.upload') }}
        </Button>
      </div>
    </div>

    <!-- Statistics -->
    <MediaStats
      :stats="statistics"
      class="mb-6"
    />

    <div class="flex gap-6 relative">
      <!-- Sidebar -->
      <MediaSidebar 
        :class="[
          'shrink-0 z-[40]',
          sidebarCollapsed 
            ? 'hidden lg:block w-0' 
            : 'fixed lg:relative inset-y-0 left-0 w-72 lg:w-72 bg-card shadow-xl lg:shadow-none border-r border-border lg:border-r',
          'transition-colors duration-300'
        ]"
      />

      <!-- Sidebar Backdrop (Mobile) -->
      <div 
        v-if="!sidebarCollapsed" 
        class="fixed inset-0 z-[35] bg-black/50 backdrop-blur-sm lg:hidden h-full w-full"
        @click="sidebarCollapsed = true"
      />

      <!-- Main Content -->
      <div class="flex-1 min-w-0 flex flex-col gap-4 relative">
        <!-- Floating Toggle Button (Desktop) -->
        <div class="absolute -left-[14px] top-7 -translate-y-1/2 z-[45] hidden lg:block">
          <Button
            variant="outline"
            size="icon"
            type="button"
            class="h-7 w-7 rounded-full bg-background border border-border/60 shadow-sm hover:shadow-md hover:bg-muted transition-colors flex items-center justify-center p-0"
            @click="sidebarCollapsed = !sidebarCollapsed"
          >
            <ChevronLeft
              v-if="!sidebarCollapsed"
              class="w-3.5 h-3.5 text-muted-foreground"
            />
            <ChevronRight
              v-else
              class="w-3.5 h-3.5 text-muted-foreground"
            />
          </Button>
        </div>

        <!-- Toolbar & Content Card -->
        <div class="bg-card border border-border/40 rounded-xl overflow-hidden shadow-none flex flex-col min-h-[500px]">
          <!-- Toolbar -->
          <MediaToolbar class="bg-transparent border-b border-border/40 py-2" />

          <!-- Content Area -->
          <div class="flex-1 overflow-hidden relative group">
            <div class="h-full overflow-y-auto custom-scrollbar p-0">
              <div
                v-if="loading"
                class="p-12 text-center h-full flex flex-col items-center justify-center"
              >
                <Loader2 class="w-8 h-8 animate-spin text-primary mb-4" />
                <p class="text-muted-foreground">
                  {{ $t('features.media.loading') }}
                </p>
              </div>
        
              <ContextMenu
                v-else-if="mediaList.length === 0 && currentFolders.length === 0"
                class="h-full"
              >
                <ContextMenuTrigger class="h-full">
                  <div class="flex flex-col items-center justify-center h-full p-12 text-center">
                    <div class="w-20 h-20 rounded-full bg-muted/20 flex items-center justify-center mb-6">
                      <FolderPlus
                        class="w-8 h-8 text-muted-foreground/30"
                        stroke-width="1.5"
                      />
                    </div>
                    <h3 class="text-lg font-bold text-foreground/90">
                      {{ $t('features.media.empty') }}
                    </h3>
                    <p class="text-xs text-muted-foreground max-w-[240px] mt-2 italic">
                      {{ $t('features.file_manager.help.sections.navigation.content') }}
                    </p>
                  </div>
                </ContextMenuTrigger>
                <ContextMenuContent>
                  <template v-if="!isTrashMode">
                    <ContextMenuItem @click="showFolderModal = true">
                      <FolderPlus class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                      {{ $t('features.media.newFolder') }}
                    </ContextMenuItem>
                    <ContextMenuItem @click="showUploadModal = true">
                      <Plus class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                      {{ $t('features.media.upload') }}
                    </ContextMenuItem>
                    <ContextMenuSeparator />
                    <ContextMenuItem @click="fetchMedia(); fetchFolders();">
                      <RefreshCw class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                      {{ $t('features.media.actions.refresh') }}
                    </ContextMenuItem>
                  </template>
                  <template v-else>
                    <ContextMenuItem @click="emptyTrash">
                      <Trash2 class="w-4 h-4 text-destructive/70 transition-colors" />
                      {{ $t('features.media.emptyTrash') }}
                    </ContextMenuItem>
                  </template>
                </ContextMenuContent>
              </ContextMenu>

              <div v-else>
                <MediaGridView v-if="viewMode === 'grid'" />
                <MediaListView v-else />
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <Pagination
          v-if="pagination && pagination.total > 0"
          v-model:per-page="pagination.per_page"
          :current-page="pagination.current_page"
          :total-items="pagination.total"
          :show-per-page="true"
          class="mt-2"
          @page-change="changePage"
        />
      </div>

      <!-- Properties Panel (Right) -->
      <MediaPropertiesPanel />
    </div>

    <!-- Modals -->
    <MediaUploadModal
      v-if="showUploadModal"
      :folder-id="selectedFolder"
      @close="showUploadModal = false"
      @uploaded="handleMediaUploaded"
    />



    <MediaViewModal
      v-if="showViewModal && viewingMedia"
      :media="viewingMedia"
      @close="showViewModal = false"
      @edit="editMedia"
      @delete="deleteMedia"
    />

    <FolderModal
      v-if="showFolderModal"
      @close="showFolderModal = false"
      @created="handleFolderCreated"
    />

    <MoveToFolderModal
      v-if="showMoveFolderModal"
      :folders="folders"
      @close="showMoveFolderModal = false"
      @moved="handleMoveToFolder"
    />

    <BulkUpdateAltModal
      v-if="showUpdateAltModal"
      :selected-count="selectedMedia.length"
      :processing="bulkProcessing"
      @close="showUpdateAltModal = false"
      @submit="handleUpdateAltText"
    />

    <div
      v-if="bulkProcessing"
      class="fixed bottom-4 right-4 bg-card border border-border/40 rounded-xl p-4 w-80 z-50 shadow-none"
    >
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-foreground">{{ $t('features.media.modals.bulk.processing') }}</span>
        <span class="text-sm text-muted-foreground">{{ bulkProgress }}%</span>
      </div>
      <div class="w-full bg-muted rounded-full h-2">
        <div
          class="bg-indigo-600 h-2 rounded-full"
          :style="{ width: bulkProgress + '%' }"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, provide } from 'vue';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import FolderPlus from 'lucide-vue-next/dist/esm/icons/folder-plus.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import ChevronLeft from 'lucide-vue-next/dist/esm/icons/chevron-left.js';
import ChevronRight from 'lucide-vue-next/dist/esm/icons/chevron-right.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import { 
    Button, 
    Pagination,
    ContextMenu,
    ContextMenuTrigger,
    ContextMenuContent,
    ContextMenuItem,
    ContextMenuSeparator
} from '@/components/ui';
import MediaUploadModal from '@/components/shared/media/MediaUploadModal.vue';
import MediaViewModal from '@/components/shared/media/MediaViewModal.vue';
import FolderModal from '@/components/shared/media/FolderModal.vue';
import MoveToFolderModal from '@/components/shared/media/MoveToFolderModal.vue';
import BulkUpdateAltModal from '@/components/shared/media/BulkUpdateAltModal.vue';

// Composables & Sub-components
import { useMediaManager } from '@/composables/useMediaManager';
import MediaStats from '@/components/shared/media/MediaStats.vue';
import MediaSidebar from '@/components/shared/media/MediaSidebar.vue';
import MediaToolbar from '@/components/shared/media/MediaToolbar.vue';
import MediaPropertiesPanel from '@/components/shared/media/MediaPropertiesPanel.vue';
import MediaGridView from '@/components/shared/media/MediaGridView.vue';
import MediaListView from '@/components/shared/media/MediaListView.vue';

import { MediaManagerKey } from '@/keys';

const mediaManager = useMediaManager();
const {
    viewMode,
    loading,
    mediaList,
    folders,
    currentFolders,
    isTrashMode,
    selectedFolder,
    selectedMedia,
    pagination,
    statistics,
    bulkProcessing,
    bulkProgress,
    fetchMedia,
    fetchStatistics,
    fetchFolders,
    fetchTags,
    fetchFilters,
    handleBulkAction,
    editMedia,
    deleteMedia,
    emptyTrash,
    // Modal State
    sidebarCollapsed,
    showUploadModal,
    showViewModal,
    showFolderModal,
    showMoveFolderModal,
    showUpdateAltModal,
    viewingMedia,
} = mediaManager;

provide(MediaManagerKey, mediaManager);

const isReady = ref(false);

const handleMoveToFolder = (folderId: string | null) => {
    handleBulkAction('move', { folderId: folderId ? Number(folderId) : null });
    showMoveFolderModal.value = false;
};

const handleUpdateAltText = (altText: string) => {
    handleBulkAction('update_alt', { altText });
    showUpdateAltModal.value = false;
};

const handleMediaUploaded = () => {
    fetchMedia();
    showUploadModal.value = false;
};


const handleFolderCreated = () => {
    fetchFolders();
    showFolderModal.value = false;
};


const changePage = (page: number) => {
    if (pagination.value) {
        pagination.value.current_page = page;
        fetchMedia();
    }
};

onMounted(() => {
    fetchMedia();
    fetchFolders();
    fetchTags();
    fetchFilters();
    fetchStatistics();
    setTimeout(() => { isReady.value = true; }, 100);
});
</script>
