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
          :key="selectedMenuId!" 
          :menu-id="selectedMenuId!"
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
import { useLayoutStore } from '@/modules/Layout/stores/layout';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import MenuBuilder from '@/modules/Layout/components/menus/MenuBuilder.vue';
import MenuList from '@/modules/Layout/components/menus/MenuList.vue';
import MenuModal from '@/modules/Layout/components/menus/MenuModal.vue';
import { 
    Button
} from '@/shared/components/ui';
import List from 'lucide-vue-next/dist/esm/icons/list.js';
import Edit3 from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const layoutStore = useLayoutStore();

interface Menu {
    id: string;
    name: string;
    deleted_at?: string | null;
}

const showCreateModal = ref(false);
const selectedMenuId = ref<string | null>(null);
const trashedFilter = ref('without');
const viewMode = ref<'list' | 'builder'>('list');

// Computed
const menus = computed(() => layoutStore.menuList as Menu[]);
const isLoading = computed(() => layoutStore.loading);
const trashedCount = computed(() => layoutStore.trashedCount);
const selectedMenu = computed(() => {
    return menus.value.find(m => m.id === selectedMenuId.value);
});

// Fetch all menus
const fetchMenus = async () => {
    try {
        await layoutStore.fetchAllMenus({
            trashed: trashedFilter.value,
            per_page: 100
        });
    } catch (error: unknown) {
        toast.error.action(t('modules.cms.menus.messages.loadingFailed') || 'Failed to load menus');
    }
};

const openCreateModal = () => {
    showCreateModal.value = true;
};

const handleMenuCreated = async (newMenu: { id?: string }) => {
    showCreateModal.value = false;
    await fetchMenus();
    if (newMenu && newMenu.id) {
        selectedMenuId.value = String(newMenu.id);
        viewMode.value = 'builder';
    }
};

const handleSelectMenu = (menuId: string) => {
    selectedMenuId.value = String(menuId);
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

    if (!selectedMenuId.value) return;

    try {
        await layoutStore.deleteMenu(selectedMenuId.value, isTrashed);
        toast.success.delete(t('modules.cms.menus.title'));
        selectedMenuId.value = null;
        await fetchMenus();
    } catch (error: unknown) {
        logger.error('Error deleting menu:', error);
        toast.error.delete(error, t('modules.cms.menus.title'));
    }
};

const restoreCurrentMenu = async () => {
    if (!selectedMenu.value || !selectedMenuId.value) return;

     const confirmed = await confirm({
        title: t('common.actions.restore'),
        message: t('modules.cms.menus.messages.restoreConfirm', { name: selectedMenu.value.name }),
        variant: 'info',
        confirmText: t('common.actions.restore'),
    });

    if (!confirmed) return;

    try {
        await layoutStore.restoreMenu(selectedMenuId.value);
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
