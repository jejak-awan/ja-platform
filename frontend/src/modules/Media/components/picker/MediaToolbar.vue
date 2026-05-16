<template>
  <TooltipProvider>
    <div class="bg-transparent px-2.5 py-1.5 shadow-none">
      <div class="flex items-center gap-2 sm:gap-4 overflow-x-auto flex-nowrap custom-scrollbar overflow-y-hidden pb-1 items-center">
        <!-- Mobile Sidebar Toggle -->
        <Button
          variant="ghost"
          size="icon"
          class="lg:hidden h-9 w-9 text-muted-foreground hover:text-foreground rounded-md shrink-0 border border-border/40 sm:border-transparent"
          @click="sidebarCollapsed = !sidebarCollapsed"
        >
          <MenuIcon class="w-4 h-4" />
        </Button>

        <!-- Breadcrumbs (Left) -->
        <div class="flex items-center gap-0.5 sm:gap-1 text-sm shrink-0 min-w-0">
          <Tooltip>
            <TooltipTrigger as-child>
              <Button 
                variant="ghost" 
                size="icon"
                class="h-8 w-8 text-muted-foreground hover:text-foreground rounded-md shrink-0"
                @click="selectFolder(null)"
              >
                <Home class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
              </Button>
            </TooltipTrigger>
            <TooltipContent side="bottom">
              {{ $t('modules.system.media.allMedia') }}
            </TooltipContent>
          </Tooltip>
          <template
            v-for="(crumb, index) in breadcrumbs"
            :key="crumb.id"
          >
            <span class="flex items-center gap-0.5 sm:gap-1 min-w-0">
              <span class="text-muted-foreground/40 text-[10px] select-none">/</span>
              <Button 
                variant="ghost" 
                size="sm" 
                class="h-7 px-1.5 sm:px-2 font-medium rounded-md hover:text-foreground truncate max-w-[60px] sm:max-w-[120px]"
                :class="index === breadcrumbs.length - 1 ? 'text-primary font-bold' : 'text-muted-foreground'"
                @click="selectFolder(crumb.id)"
              >
                {{ crumb.name }}
              </Button>
            </span>
          </template>
        </div>

        <!-- Search (Center) -->
        <div class="flex-1 flex justify-center min-w-0">
          <div class="relative group w-full max-w-md">
            <SearchIcon class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 h-3 w-3 sm:h-3.5 sm:h-3.5 text-muted-foreground group-focus-within:text-primary transition-colors" />
            <Input
              v-model="search"
              type="text"
              :placeholder="$t('modules.system.media.search')"
              class="pl-8 sm:pl-9 bg-background border-border/40 rounded-lg h-8 sm:h-9 text-[11px] sm:text-xs focus-visible:ring-1 focus-visible:ring-primary/20 transition-colors duration-200 w-full"
            />
          </div>
        </div>
            
        <!-- Actions (Right) -->
        <div class="flex items-center gap-1 sm:gap-2 shrink-0">
          <!-- Unified Filter Toggle -->
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger as-child>
                <Button
                  variant="ghost"
                  size="icon"
                  :class="`h-9 w-9 p-0 rounded-lg border border-border/40 transition-colors duration-200 ${showFilters ? 'bg-accent/10 text-primary border-primary/20' : 'bg-background hover:bg-accent/10'}`"
                  @click="showFilters = !showFilters"
                >
                  <FilterIcon class="w-3.5 h-3.5" />
                </Button>
              </TooltipTrigger>
              <TooltipContent
                side="bottom"
                align="center"
              >
                {{ $t('modules.system.media.filter.advanced') }}
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>

          <!-- Separator -->
          <div class="mx-0.5 h-5 w-[1px] bg-border/40" />

          <!-- View Mode Toggle -->
          <Tooltip>
            <TooltipTrigger as-child>
              <Button
                variant="ghost"
                size="icon"
                type="button"
                class="h-9 w-9 rounded-lg border border-border/40 transition-colors duration-200 text-muted-foreground hover:text-foreground hover:bg-accent/10"
                @click="viewMode = viewMode === 'grid' ? 'list' : 'grid'"
              >
                <LayoutGrid
                  v-if="viewMode === 'list'"
                  class="w-3.5 h-3.5"
                />
                <List
                  v-else
                  class="w-3.5 h-3.5"
                />
              </Button>
            </TooltipTrigger>
            <TooltipContent side="bottom">
              {{ viewMode === 'grid' ? $t('modules.system.media.views.list') : $t('modules.system.media.views.grid') }}
            </TooltipContent>
          </Tooltip>
        </div>
      </div>

      <!-- Bulk Actions Panel (Relocated) -->
      <div
        v-if="selectedMedia.length > 0 || selectedFolders.length > 0"
        class="mt-2 pt-2 border-t border-border/40"
      >
        <div class="flex items-center justify-between gap-4 overflow-x-auto flex-nowrap custom-scrollbar pb-1">
          <div class="flex items-center gap-2">
            <div class="h-8 flex-shrink-0 flex items-center px-3 rounded-lg bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-widest select-none border border-primary/20">
              {{ selectedMedia.length + selectedFolders.length }} <span class="hidden sm:inline ml-1">{{ $t('modules.system.media.selected', { count: '' }).trim() }}</span>
            </div>
                    
            <div class="flex items-center gap-1 p-0.5 bg-accent/5 rounded-lg border border-border/40">
              <!-- Normal Mode Actions -->
              <template v-if="!isTrashMode">
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="sm"
                      class="h-7 px-3 rounded-md hover:bg-background text-xs font-medium transition-colors duration-200"
                      @click="startBulkAction('download')"
                    >
                      <Download class="w-3.5 h-3.5 mr-1.5" />
                      <span>{{ $t('modules.system.media.actions.downloadZip') }}</span>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent side="bottom">
                    {{ $t('modules.system.media.actions.downloadZip') }}
                  </TooltipContent>
                </Tooltip>
                            
                <!-- Move Button -->
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="sm"
                      class="h-7 px-3 rounded-md hover:bg-background text-xs font-medium transition-colors duration-200"
                      @click="showMoveFolderModal = true"
                    >
                      <MoveIcon class="w-3.5 h-3.5 mr-1.5" />
                      <span>{{ $t('modules.system.media.actions.move') }}</span>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent side="bottom">
                    {{ $t('modules.system.media.actions.move') }}
                  </TooltipContent>
                </Tooltip>

                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="sm"
                      class="h-7 px-3 rounded-md hover:bg-background text-xs font-medium transition-colors duration-200"
                      @click="showUpdateAltModal = true"
                    >
                      <Type class="w-3.5 h-3.5 mr-1.5" />
                      <span>{{ $t('modules.system.media.actions.updateAlt') }}</span>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent side="bottom">
                    {{ $t('modules.system.media.actions.updateAlt') }}
                  </TooltipContent>
                </Tooltip>

                <div class="w-px h-4 bg-border/60 mx-0.5" />

                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="sm"
                      class="h-7 px-3 rounded-md text-destructive hover:bg-destructive/10 hover:text-destructive text-xs font-medium transition-colors duration-200"
                      @click="startBulkAction('delete')"
                    >
                      <Trash2 class="w-3.5 h-3.5 mr-1.5" />
                      <span>{{ $t('modules.system.media.actions.delete') }}</span>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent side="bottom">
                    {{ $t('modules.system.media.actions.delete') }}
                  </TooltipContent>
                </Tooltip>
              </template>

              <!-- Trash Mode Actions -->
              <template v-else>
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="sm"
                      class="h-7 px-3 rounded-md hover:bg-background text-xs font-medium transition-colors duration-200"
                      @click="startBulkAction('restore')"
                    >
                      <RefreshCw class="w-3.5 h-3.5 mr-1.5" />
                      <span>{{ $t('modules.system.media.actions.restore') }}</span>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent side="bottom">
                    {{ $t('modules.system.media.actions.restore') }}
                  </TooltipContent>
                </Tooltip>

                <div class="w-px h-4 bg-border/60 mx-0.5" />

                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="sm"
                      class="h-7 px-3 rounded-md text-destructive hover:bg-destructive/10 hover:text-destructive text-xs font-medium transition-colors duration-200"
                      @click="startBulkAction('delete_permanent')"
                    >
                      <Trash2 class="w-3.5 h-3.5 mr-1.5" />
                      <span>{{ $t('modules.system.media.actions.deletePermanent') }}</span>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent side="bottom">
                    {{ $t('modules.system.media.actions.deletePermanent') }}
                  </TooltipContent>
                </Tooltip>
              </template>
            </div>
          </div>

          <Tooltip>
            <TooltipTrigger as-child>
              <Button
                variant="ghost"
                size="sm"
                class="h-8 px-3 rounded-lg text-muted-foreground hover:text-foreground hover:bg-destructive/10 hover:text-destructive transition-colors text-xs"
                @click="clearSelection"
              >
                {{ $t('modules.system.media.actions.clearSelection') }}
                <X class="w-3.5 h-3.5 ml-1.5" />
              </Button>
            </TooltipTrigger>
            <TooltipContent side="bottom">
              {{ $t('modules.system.media.actions.clearSelection') }}
            </TooltipContent>
          </Tooltip>
        </div>
      </div>

      <!-- Unified Filter Panel -->
      <div
        v-if="showFilters"
        class="mt-4 pt-4 border-t border-border/40 space-y-4"
      >
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
          <!-- Type Filter -->
          <div class="space-y-1.5 min-w-0">
            <label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 ml-1">
              {{ $t('modules.system.media.filter.type') }}
            </label>
            <Select v-model="mimeFilter">
              <SelectTrigger class="bg-background h-10 border-border/40 rounded-lg w-full">
                <SelectValue :placeholder="$t('modules.system.media.filter.allTypes')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ $t('modules.system.media.filter.allTypes') }}
                </SelectItem>
                <SelectItem value="image">
                  {{ $t('modules.system.media.filter.images') }}
                </SelectItem>
                <SelectItem value="video">
                  {{ $t('modules.system.media.filter.videos') }}
                </SelectItem>
                <SelectItem value="application">
                  {{ $t('modules.system.media.filter.documents') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Status Filter -->
          <div class="space-y-1.5 min-w-0">
            <label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 ml-1">
              {{ $t('modules.system.media.filter.status') }}
            </label>
            <Select v-model="usageFilter">
              <SelectTrigger class="bg-background h-10 border-border/40 rounded-lg w-full">
                <SelectValue :placeholder="$t('modules.system.media.filter.allStatus')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ $t('modules.system.media.filter.allStatus') }}
                </SelectItem>
                <SelectItem value="used">
                  {{ $t('modules.system.media.filter.used') }}
                </SelectItem>
                <SelectItem value="unused">
                  {{ $t('modules.system.media.filter.unused') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Author Filter -->
          <div class="space-y-1.5 min-w-0">
            <label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 ml-1">
              {{ $t('modules.system.media.filter.author') }}
            </label>
            <Select v-model="authorFilterString">
              <SelectTrigger class="bg-background h-10 border-border/40 rounded-lg w-full">
                <SelectValue :placeholder="$t('modules.system.media.filter.allAuthors')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ $t('modules.system.media.filter.allAuthors') }}
                </SelectItem>
                <SelectItem
                  v-for="author in availableFilters.authors"
                  :key="author.id"
                  :value="author.id.toString()"
                >
                  {{ author.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Size Filter -->
          <div class="space-y-1.5 min-w-0">
            <label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 ml-1">
              {{ $t('modules.system.media.filter.size') }}
            </label>
            <div class="flex items-center gap-1.5">
              <Input 
                v-model="minSizeFilter" 
                type="number" 
                placeholder="Min" 
                class="bg-background h-10 border-border/40 rounded-lg w-full min-w-0" 
              />
              <span class="text-muted-foreground">-</span>
              <Input 
                v-model="maxSizeFilter" 
                type="number" 
                placeholder="Max" 
                class="bg-background h-10 border-border/40 rounded-lg w-full min-w-0" 
              />
            </div>
          </div>

          <!-- Date Filter -->
          <div class="space-y-1.5 min-w-0 xl:col-span-2">
            <label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 ml-1">
              {{ $t('modules.system.media.filter.dateRange') }}
            </label>
            <div class="flex items-center gap-1.5">
              <Input 
                v-model="dateFromFilter" 
                type="date" 
                class="bg-background h-10 border-border/40 rounded-lg text-[10px] px-2 w-full min-w-0" 
              />
              <span class="text-muted-foreground text-[10px] flex-shrink-0 px-1">{{ $t('common.labels.to') }}</span>
              <Input 
                v-model="dateToFilter" 
                type="date" 
                class="bg-background h-10 border-border/40 rounded-lg text-[10px] px-2 w-full min-w-0" 
              />
            </div>
          </div>
        </div>

        <div class="flex justify-end pt-2">
          <Button
            variant="ghost"
            size="sm"
            class="text-xs h-8 hover:bg-destructive/10 hover:text-destructive rounded-lg"
            @click="clearFilters"
          >
            <X class="w-3.5 h-3.5 mr-1.5" />
            {{ $t('common.labels.clear') }}
          </Button>
        </div>
      </div>
    </div>
  </TooltipProvider>
</template>

<script setup lang="ts">
import { ref, inject, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import SearchIcon from 'lucide-vue-next/dist/esm/icons/search.js';
import LayoutGrid from 'lucide-vue-next/dist/esm/icons/layout-grid.js';
import List from 'lucide-vue-next/dist/esm/icons/list.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import MoveIcon from 'lucide-vue-next/dist/esm/icons/move.js';
import Type from 'lucide-vue-next/dist/esm/icons/type.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import FilterIcon from 'lucide-vue-next/dist/esm/icons/list-filter.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import Home from 'lucide-vue-next/dist/esm/icons/house.js';
import MenuIcon from 'lucide-vue-next/dist/esm/icons/menu.js';
import { 
    Button, 
    Input, 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue,
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger
} from '@/shared/components/ui';
import { MediaManagerKey } from '@/engine/keys';

useI18n();
const showFilters = ref(false);

const {
    search,
    mimeFilter,
    usageFilter,
    authorFilter,
    minSizeFilter,
    maxSizeFilter,
    dateFromFilter,
    dateToFilter,
    viewMode,
    isTrashMode,
    selectedMedia,
    selectedFolders,
    availableFilters,
    startBulkAction,
    breadcrumbs,
    selectFolder,
    sidebarCollapsed,
    clearSelection,
    showMoveFolderModal,
    showUpdateAltModal,
} = inject(MediaManagerKey)!;

const clearFilters = () => {
    mimeFilter.value = 'all';
    usageFilter.value = 'all';
    authorFilter.value = 'all';
    minSizeFilter.value = '';
    maxSizeFilter.value = '';
    dateFromFilter.value = '';
    dateToFilter.value = '';
};

// Handle string/number mismatch for Select
const authorFilterString = computed({
    get: () => String(authorFilter.value),
    set: (v: string) => {
        authorFilter.value = v;
    }
});
</script>
