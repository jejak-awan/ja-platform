<template>
  <div class="h-full flex flex-col">
    <!-- Header - Clean, just title -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-foreground">
          {{ t('modules.cms.menus.title') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ t('modules.cms.menus.subtitle') }}
        </p>
      </div>
            
      <div class="flex items-center gap-1 p-1 bg-muted/30 rounded-lg border border-border/40">
        <Button 
          variant="ghost" 
          size="sm" 
          class="h-8 px-3 text-xs"
          :class="{ 'bg-background shadow-sm': viewMode === 'list' }"
          @click="viewMode = 'list'"
        >
          <List class="w-3.5 h-3.5 mr-1.5" />
          {{ t('modules.cms.menus.actions.listView') }}
        </Button>
        <Button 
          variant="ghost" 
          size="sm" 
          class="h-8 px-3 text-xs"
          :class="{ 'bg-background shadow-sm': viewMode === 'builder' }"
          :disabled="!selectedMenuId"
          @click="viewMode = 'builder'"
        >
          <Edit3 class="w-3.5 h-3.5 mr-1.5" />
          {{ t('modules.cms.menus.actions.builderView') }}
        </Button>
      </div>
    </div>

    <!-- Main Content Area -->
    <div
      v-if="isLoading && !selectedMenuId && viewMode === 'builder'"
      class="flex-1 flex items-center justify-center min-h-[400px]"
    >
      <Loader2 class="w-8 h-8 animate-spin text-muted-foreground" />
    </div>

    <div
      v-else
      class="flex-1"
    >
      <template v-if="viewMode === 'list'">
        <MenuList 
          @select-menu="handleSelectMenu" 
          @create-menu="openCreateModal"
        />
      </template>
      <template v-else>
        <MenuBuilder 
          :key="(selectedMenuId as string | number)" 
          :menu-id="(selectedMenuId as string | number)"
          :menus="(menus as any[])"
          :trashed-filter="trashedFilter"
          :trashed-count="trashedCount"
          :is-trashed="!!selectedMenu?.deleted_at"
          @menu-updated="handleMenuUpdated"
          @create-menu="openCreateModal"
          @delete-menu="deleteCurrentMenu"
          @restore-menu="restoreCurrentMenu"
          @select-menu="handleSelectMenu"
          @update:trashed-filter="trashedFilter = $event"
          @back-to-list="viewMode = 'list'"
        />
      </template>
    </div>

    <!-- Create Menu Modal -->
    <MenuModal
      v-if="showCreateModal"
      @close="showCreateModal = false"
      @saved="handleMenuCreated"
    />
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import MenuBuilder from '@/modules/Cms/components/menus/MenuBuilder.vue';
import MenuList from '@/modules/Cms/components/menus/MenuList.vue';
import MenuModal from '@/modules/Cms/components/menus/MenuModal.vue';
import { 
    Button
} from '@/shared/components/ui';
import List from 'lucide-vue-next/dist/esm/icons/list.js';
import Edit3 from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();

interface Menu {
    id: string | number;
    name: string;
    deleted_at?: string | null;
}

const menus = ref<Menu[]>([]);
const showCreateModal = ref(false);
const selectedMenuId = ref<string | number | null>(null);
const isLoading = ref(true);
const trashedFilter = ref('without');
const trashedCount = ref(0);
const viewMode = ref<'list' | 'builder'>('list');

// Computed
const selectedMenu = computed(() => {
    return menus.value.find(m => m.id.toString() === selectedMenuId.value?.toString());
});

// Fetch all menus (legacy or for builder dropdown)
const fetchMenus = async () => {
    isLoading.value = true;
    try {
        const response = await api.get('/manage/cms/menus', {
            params: {
                trashed: trashedFilter.value,
                per_page: 100 // Get many for the dropdown
            }
        });
        
        // Capture trashed count from meta
        trashedCount.value = response.data?.meta?.trashed_count ?? 0;

        const { data } = parseResponse(response);
        menus.value = ensureArray(data);
        
    } catch (error: unknown) {
        logger.error('Failed to fetch menus:', error);
        toast.error.action(t('modules.cms.menus.messages.loadingFailed') || 'Failed to load menus');
    } finally {
        isLoading.value = false;
    }
};

const openCreateModal = () => {
    showCreateModal.value = true;
};

const handleMenuCreated = async (newMenu: { id?: string | number }) => {
    showCreateModal.value = false;
    await fetchMenus();
    if (newMenu && newMenu.id) {
        selectedMenuId.value = newMenu.id.toString();
        viewMode.value = 'builder';
    }
};

const handleSelectMenu = (menuId: string | number) => {
    selectedMenuId.value = menuId;
    viewMode.value = 'builder';
};

const handleMenuUpdated = async () => {
    const currentId = selectedMenuId.value;
    await fetchMenus();
    selectedMenuId.value = currentId;
};

const deleteCurrentMenu = async () => {
    if (!selectedMenu.value) return;
    const isTrashed = !!selectedMenu.value.deleted_at;

    const confirmed = await confirm({
        title: isTrashed ? t('common.actions.forceDelete') : t('modules.cms.menus.actions.delete'),
        message: isTrashed 
            ? t('modules.cms.menus.messages.forceDeleteConfirm', { name: selectedMenu.value.name })
            : t('modules.cms.menus.messages.deleteConfirm', { name: selectedMenu.value.name }),
        variant: 'danger',
        confirmText: isTrashed ? t('common.actions.forceDelete') : t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        if (isTrashed) {
             await api.delete(`/manage/cms/menus/${selectedMenuId.value}/force-delete`);
             toast.success.action(t('common.messages.success.deleted', { item: t('modules.cms.menus.title') }));
        } else {
            await api.delete(`/manage/cms/menus/${selectedMenuId.value}`);
            toast.success.delete(t('modules.cms.menus.title'));
        }
        selectedMenuId.value = null;
        await fetchMenus();
    } catch (error: unknown) {
        logger.error('Error deleting menu:', error);
        toast.error.delete(error, t('modules.cms.menus.title'));
    }
};

const restoreCurrentMenu = async () => {
    if (!selectedMenu.value) return;

     const confirmed = await confirm({
        title: t('common.actions.restore'),
        message: t('modules.cms.menus.messages.restoreConfirm', { name: selectedMenu.value.name }),
        variant: 'info',
        confirmText: t('common.actions.restore'),
    });

    if (!confirmed) return;

    try {
        await api.post(`/manage/cms/menus/${selectedMenuId.value}/restore`);
        toast.success.restore(t('modules.cms.menus.title'));
        await fetchMenus();
    } catch (error: unknown) {
         logger.error('Error restoring menu:', error);
        toast.error.fromResponse(error);
    }
};

onMounted(() => {
    fetchMenus();
});

watch(trashedFilter, () => {
    selectedMenuId.value = null;
    fetchMenus();
});
</script>
