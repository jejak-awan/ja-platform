<template>
  <div class="file-manager-container">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div class="flex items-center gap-2">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-foreground">
            {{ $t('modules.core.file_manager.title') }}
          </h1>
          <p class="text-muted-foreground">
            {{ $t('modules.core.file_manager.description') }}
          </p>
        </div>
        <!-- Help Button Next to Title -->
        <Button
          variant="ghost"
          size="icon"
          type="button"
          :class="[
            'h-8 w-8 rounded-lg transition-colors mt-1',
            showHelp ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:text-foreground'
          ]"
          @click="showHelp = true"
        >
          <CircleHelp class="w-4 h-4" />
        </Button>
      </div>
      <div class="flex items-center space-x-3">
        <Button 
          variant="outline"
          @click="showCreateFolderModal = true"
        >
          <FolderPlus class="w-4 h-4 mr-2" />
          {{ $t('modules.core.file_manager.actions.newFolder') }}
        </Button>
        <Button
          type="button"
          @click="showUploadModal = true"
        >
          <Upload class="w-4 h-4 mr-2" />
          {{ $t('modules.core.file_manager.actions.upload') }}
        </Button>
      </div>
    </div>

    <div class="flex gap-6 relative">
      <!-- Sidebar Navigation (Left) -->
      <FileSidebar 
        :class="[
          'shrink-0 z-[40] border-r border-border/40 lg:border-r-0',
          fm.sidebarCollapsed.value 
            ? 'hidden lg:block w-0' 
            : 'fixed lg:relative inset-y-0 left-0 w-72 lg:w-72 bg-white lg:bg-transparent shadow-xl lg:shadow-none lg:border-r lg:border-border/40',
          fm.isMounted.value ? 'transition-colors duration-300' : ''
        ]" 
      />

      <!-- Sidebar Backdrop (Mobile Overlay) -->
      <div 
        v-if="!fm.sidebarCollapsed.value" 
        class="fixed inset-0 z-[35] bg-black/50 backdrop-blur-sm lg:hidden h-full w-full"
        @click="fm.toggleSidebar"
      />

      <!-- Main Content Area -->
      <div class="flex-1 min-w-0 flex flex-col gap-4 relative">
        <!-- Floating Toggle Button (Between Sidebar and Card) -->
        <div class="absolute -left-[14px] top-7 -translate-y-1/2 z-[45] hidden lg:block">
          <Button
            variant="outline"
            size="icon"
            type="button"
            class="h-7 w-7 rounded-full bg-background border border-border/60 shadow-sm hover:shadow-md hover:bg-muted transition-colors flex items-center justify-center p-0"
            @click="fm.toggleSidebar"
          >
            <ChevronLeft
              v-if="!fm.sidebarCollapsed.value"
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
          <!-- Toolbar (Below Breadcrumbs) -->
          <FileToolbar 
            class="py-2.5 px-4 bg-transparent border-b border-border/40" 
            @new-folder="showCreateFolderModal = true"
            @upload="showUploadModal = true"
          />
                    
          <!-- Content Area -->
          <div class="flex-1 overflow-hidden relative group">
            <ContextMenu>
              <ContextMenuTrigger as-child>
                <div class="h-full overflow-y-auto custom-scrollbar">
                  <div
                    v-if="fm.showTrashView.value"
                    class="h-full"
                  >
                    <FileTrashView />
                  </div>

                  <div
                    v-else-if="fm.loading.value"
                    class="absolute inset-0 z-50 flex flex-col items-center justify-center bg-background/60 backdrop-blur-[2px]"
                  >
                    <Spinner class="w-10 h-10 text-primary" />
                    <p class="mt-4 text-xs font-bold text-primary animate-pulse uppercase tracking-widest leading-none">
                      {{ $t('modules.core.file_manager.messages.loading') }}
                    </p>
                  </div>

                  <div
                    v-else-if="!fm.loading.value && (fm.paginatedFolders.value.length === 0 && fm.paginatedFiles.value.length === 0)"
                    class="flex flex-col items-center justify-center h-full p-12 text-center"
                  >
                    <div class="w-20 h-20 rounded-full bg-muted/20 flex items-center justify-center mb-6">
                      <FolderPlus
                        class="w-8 h-8 text-muted-foreground/30"
                        stroke-width="1.5"
                      />
                    </div>
                    <h3 class="text-lg font-bold text-foreground/90">
                      {{ $t('modules.core.file_manager.messages.noFiles') }}
                    </h3>
                    <p class="text-xs text-muted-foreground max-w-[240px] mt-2 italic">
                      {{ $t('modules.core.file_manager.help.sections.navigation.content') }}
                    </p>
                  </div>

                  <div
                    v-else
                    class="p-0"
                  >
                    <FileGridView
                      v-if="fm.viewMode.value === 'grid'"
                      @preview="openPreview"
                    />
                    <FileListView
                      v-else
                      @preview="openPreview"
                    />
                  </div>
                </div>
              </ContextMenuTrigger>
              <ContextMenuContent class="w-56">
                <ContextMenuItem @click="showCreateFolderModal = true">
                  <FolderPlus class="w-4 h-4 mr-2" />
                  {{ $t('modules.core.file_manager.actions.newFolder') }}
                </ContextMenuItem>
                <ContextMenuItem @click="showUploadModal = true">
                  <Upload class="w-4 h-4 mr-2" />
                  {{ $t('modules.core.file_manager.actions.upload') }}
                </ContextMenuItem>
                <ContextMenuSeparator v-if="fm.clipboard.value.items.length > 0" />
                <ContextMenuItem
                  v-if="fm.clipboard.value.items.length > 0"
                  @click="fm.pasteFromClipboard(fm.currentPath.value)"
                >
                  <ClipboardPaste class="w-4 h-4 mr-2" />
                  {{ $t('modules.core.file_manager.actions.paste') }}
                </ContextMenuItem>
                <ContextMenuSeparator />
                <ContextMenuItem @click="fm.fetchCurrentPath()">
                  <RefreshCw class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                  {{ $t('common.actions.refresh') }}
                </ContextMenuItem>
              </ContextMenuContent>
            </ContextMenu>
          </div>
        </div>

        <!-- Pagination (Outside Card) -->
        <div class="mt-2">
          <Pagination
            v-if="fm.totalItems.value > 0"
            v-model:current-page="fm.currentPage.value"
            v-model:per-page="fm.itemsPerPage.value"
            :total-items="fm.totalItems.value"
            :show-per-page="true"
            :show-page-numbers="true"
            size="sm"
            @page-change="() => fm.fetchCurrentPath()"
          />
        </div>
      </div>

      <!-- Properties Sidebar (Right) -->
      <FilePropertiesSidebar class="h-full" />
    </div>

    <!-- Modals and Dialogs -->
    <FilePreviewModal
      v-if="previewFile"
      :file="previewFile"
      @close="previewFile = null"
    />

    <FileUploadModal
      v-if="showUploadModal"
      :path="fm.currentPath.value"
      @close="showUploadModal = false"
      @uploaded="handleFileUploaded"
    />

    <CreateFolderModal
      v-if="showCreateFolderModal"
      :path="fm.currentPath.value"
      @close="showCreateFolderModal = false"
      @created="handleFolderCreated"
    />

    <!-- Help Dialog -->
    <Dialog v-model:open="showHelp">
      <DialogContent class="sm:max-w-[700px] bg-background">
        <DialogHeader>
          <DialogTitle>{{ $t('modules.core.file_manager.help.title') }}</DialogTitle>
          <DialogDescription>
            {{ $t('modules.core.file_manager.help.sections.navigation.content') }}
          </DialogDescription>
        </DialogHeader>
        <div class="grid gap-6 py-4">
          <div class="grid gap-2">
            <div class="flex items-center gap-2 font-medium">
              <Upload class="w-4 h-4" />
              {{ $t('modules.core.file_manager.help.sections.upload.title') }}
            </div>
            <p class="text-sm text-muted-foreground">
              {{ $t('modules.core.file_manager.help.sections.upload.content') }}
            </p>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, provide } from 'vue';
import CircleHelp from 'lucide-vue-next/dist/esm/icons/circle-question-mark.js';
import FolderPlus from 'lucide-vue-next/dist/esm/icons/folder-plus.js';
import Upload from 'lucide-vue-next/dist/esm/icons/upload.js';
import ClipboardPaste from 'lucide-vue-next/dist/esm/icons/clipboard-paste.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import ChevronLeft from 'lucide-vue-next/dist/esm/icons/chevron-left.js';
import ChevronRight from 'lucide-vue-next/dist/esm/icons/chevron-right.js';
import { 
    Button, 
    Dialog, 
    DialogContent, 
    DialogHeader, 
    DialogTitle, 
    DialogDescription, 
    Pagination,
    ContextMenu,
    ContextMenuTrigger,
    ContextMenuContent,
    ContextMenuItem,
    ContextMenuSeparator,
    Spinner
} from '@/shared/components/ui';

// Composable and Components
import { useFileManager } from '@/engine/composables/useFileManager';
import FileSidebar from '@/modules/Core/components/file-manager/FileSidebar.vue';
import FileToolbar from '@/modules/Core/components/file-manager/FileToolbar.vue';
import FilePropertiesSidebar from '@/modules/Core/components/file-manager/FilePropertiesSidebar.vue';
import FileGridView from '@/modules/Core/components/file-manager/FileGridView.vue';
import FileListView from '@/modules/Core/components/file-manager/FileListView.vue';
import FileTrashView from '@/modules/Core/components/file-manager/FileTrashView.vue';
import FilePreviewModal from '@/modules/Core/components/file-manager/FilePreviewModal.vue';
import FileUploadModal from '@/modules/Core/components/file-manager/FileUploadModal.vue';
import CreateFolderModal from '@/modules/Core/components/file-manager/CreateFolderModal.vue';
import type { FileItem } from '@/modules/Cms/types/file-manager';
import { FileManagerKey } from '@/engine/keys';

// Initialize File Manager Composable with CMS scope
const fm = useFileManager({ rootPath: '/cms/media' });

// Provide state to children
provide(FileManagerKey, fm);

// UI Local State
const showHelp = ref(false);
const showUploadModal = ref(false);
const showCreateFolderModal = ref(false);
const previewFile = ref<FileItem | null>(null);

// Methods
const openPreview = (file: FileItem) => {
    previewFile.value = file;
};

const handleFileUploaded = () => {
    fm.fetchCurrentPath();
    showUploadModal.value = false;
};

const handleFolderCreated = () => {
    fm.fetchCurrentPath();
    showCreateFolderModal.value = false;
};

// Lifecycle
onMounted(() => {
    fm.isMounted.value = true;
    fm.fetchAllFolders();
    fm.fetchCurrentPath();
    fm.fetchFilters();
    fm.fetchTrash();
});
</script>


