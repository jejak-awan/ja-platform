<template>
  <div>
    <!-- Header -->
    <div
      v-if="!isEmbedded"
      class="mb-6 flex justify-between items-center"
    >
      <h1 class="text-2xl font-bold text-foreground">
        {{ $t('modules.cms.tags.title') }}
      </h1>
      <div class="flex space-x-3">
        <Button 
          v-if="authStore.hasPermission('create tags')"
          @click="openCreateModal"
        >
          <Plus class="w-4 h-4 mr-2" />
          {{ $t('modules.cms.tags.createNew') }}
        </Button>
      </div>
    </div>

    <!-- Statistics -->
    <div
      v-if="statistics && !isEmbedded"
      class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6"
    >
      <Card class="bg-card">
        <CardContent class="p-6">
          <div class="flex items-center">
            <div class="bg-indigo-100 dark:bg-indigo-900/30 p-2 rounded-lg">
              <Tag class="h-5 w-5 text-primary" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-muted-foreground">
                {{ $t('modules.cms.tags.stats.total') }}
              </p>
              <p class="text-2xl font-semibold text-foreground">
                {{ statistics.total_tags || 0 }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
      <Card class="bg-card">
        <CardContent class="p-6">
          <div class="flex items-center">
            <div class="bg-green-100 dark:bg-green-900/30 p-2 rounded-lg">
              <BarChart3 class="h-5 w-5 text-success" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-muted-foreground">
                {{ $t('modules.cms.tags.stats.used') }}
              </p>
              <p class="text-2xl font-semibold text-foreground">
                {{ statistics.used_tags || 0 }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
      <Card class="bg-card">
        <CardContent class="p-6">
          <div class="flex items-center">
            <div class="bg-blue-100 dark:bg-blue-900/30 p-2 rounded-lg">
              <MousePointer2 class="h-5 w-5 text-info" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-muted-foreground">
                {{ $t('modules.cms.tags.stats.usage') }}
              </p>
              <p class="text-2xl font-semibold text-foreground">
                {{ statistics.total_usage || 0 }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- content -->
    <Card class="overflow-hidden">
      <!-- Filters & Bulk Actions -->
      <div class="px-6 py-4 border-b border-border/40">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div class="flex flex-1 items-center gap-2 max-w-sm flex-wrap md:flex-nowrap">
            <div class="relative w-full">
              <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                v-model="search"
                type="text"
                :placeholder="$t('modules.cms.tags.search')"
                class="pl-9"
                @input="onSearchInput"
              />
            </div>
            <Select
              v-model="filterUsage"
              @update:model-value="fetchTags("1")"
            >
              <SelectTrigger class="w-[180px]">
                <SelectValue :placeholder="$t('modules.cms.tags.filters.usage')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ $t('modules.cms.tags.filters.all') }}
                </SelectItem>
                <SelectItem value="used">
                  {{ $t('modules.cms.tags.filters.used') }}
                </SelectItem>
                <SelectItem value="unused">
                  {{ $t('modules.cms.tags.filters.unused') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="flex items-center gap-2">
            <div
              v-if="selectedIds.length > 0"
              class="flex items-center gap-3 p-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10 animate-in fade-in slide-in-from-right-2 mr-2"
            >
              <span class="text-xs font-semibold text-primary whitespace-nowrap uppercase tracking-wider">{{ selectedIds.length }} {{ $t('common.labels.selected') }}</span>
              <div class="h-4 w-px bg-primary/20" />
              <Button
                variant="ghost"
                size="sm"
                class="h-7 px-2 text-destructive hover:bg-destructive/10"
                @click="bulkDelete"
              >
                <Trash2 class="w-4 h-4 mr-1.5" />
                {{ $t('common.actions.delete') }}
              </Button>
            </div>

            <!-- Add Create Button here when embedded -->
            <Button 
              v-if="isEmbedded && authStore.hasPermission('create tags')"
              size="sm"
              @click="openCreateModal"
            >
              <Plus class="w-4 h-4 mr-1" />
              {{ $t('modules.cms.tags.createNew') }}
            </Button>
          </div>
        </div>
      </div>

      <CardContent class="p-0">
        <DataTable
          :table="table"
          :loading="loading"
          :empty-message="$t('modules.cms.tags.empty')"
        />
      </CardContent>
            
      <!-- Pagination -->
      <Pagination
        v-if="pagination && pagination.total > 0"
        :current-page="pagination.current_page"
        :total-items="pagination.total"
        :per-page="Number(pagination.per_page)"
        class="border-none shadow-none"
        @page-change="changePage"
        @update:per-page="changePerPage"
      />
    </Card>

    <!-- Tag Modal -->
    <TagFormModal
      v-model:open="showModal"
      :tag="editingTag"
      @success="handleModalSuccess"
    />
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import BarChart3 from 'lucide-vue-next/dist/esm/icons/chart-bar-stacked.js';
import MousePointer2 from 'lucide-vue-next/dist/esm/icons/mouse-pointer-2.js';
import Edit from 'lucide-vue-next/dist/esm/icons/pen.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import SearchIcon from 'lucide-vue-next/dist/esm/icons/search.js';
import api from '@/engine/api/client';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useToast } from '@/shared/composables/useToast';
import { debounce } from '@/shared/utils/debounce';
import { parseResponse, type PaginationData } from '@/shared/utils/responseParser';
import TagFormModal from './TagFormModal.vue';

// UI Components
import {
    Button,
    Input,
    Badge,
    Checkbox,
    Card,
    Pagination,
    DataTable,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/shared/components/ui';
import { h } from 'vue';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
    getSortedRowModel,
    type SortingState,
    type RowSelectionState
} from '@tanstack/vue-table';

import { useAuthStore } from '@/modules/System/stores/auth';
import type { Tag } from '@/modules/Cms/types/cms';

defineProps<{
    isEmbedded?: boolean;
}>();

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();
const authStore = useAuthStore();
const loading = ref(true);
const tags = ref<Tag[]>([]);
const statistics = ref<Record<string, number> | null>(null);
const search = ref('');
const filterUsage = ref('all');
const selectedIds = ref<number[]>([]);
const pagination = ref<PaginationData>({
    current_page: 1,
    per_page: 20,
    total: 0,
    last_page: 1,
    from: 0,
    to: 0
});

// Modal State
const showModal = ref(false);
const editingTag = ref<Tag | null>(null);

const columnHelper = createColumnHelper<Tag>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            checked: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
            'onUpdate:checked': (val) => table.toggleAllPageRowsSelected(!!val),
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: row.getIsSelected(),
            'onUpdate:checked': (val) => row.toggleSelected(!!val),
        }),
        size: 50,
    }),
    columnHelper.accessor('name', {
        header: t('modules.cms.tags.table.name'),
        cell: ({ row }) => h('div', { class: 'font-medium' }, [
            row.original.name,
            h('div', { class: 'md:hidden text-xs text-muted-foreground mt-1' }, row.original.slug)
        ])
    }),
    columnHelper.accessor('slug', {
        header: t('modules.cms.tags.table.slug'),
        cell: ({ row }) => h('span', { class: 'text-muted-foreground' }, row.original.slug),
        meta: { className: 'hidden md:table-cell' }
    }),
    columnHelper.accessor('description', {
        header: t('modules.cms.tags.table.description'),
        cell: ({ row }) => h('div', { class: 'max-w-[300px] truncate', title: row.original.description }, row.original.description || '-'),
        meta: { className: 'hidden lg:table-cell' }
    }),
    columnHelper.accessor('contents_count', {
        header: () => h('div', { class: 'text-right' }, t('modules.cms.tags.table.usage')),
        cell: ({ row }) => h('div', { class: 'text-right' }, [
            h(Badge, { variant: 'secondary', class: 'font-mono' }, String(row.original.contents_count || 0))
        ])
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('modules.cms.tags.table.actions')),
        cell: ({ row }) => h('div', { class: 'flex justify-end gap-1' }, [
            authStore.hasPermission('edit tags') && h(Button, {
                variant: 'ghost', size: 'icon', class: 'h-8 w-8',
                onClick: () => openEditModal(row.original)
            }, [h(Edit, { class: 'w-4 h-4' })]),
            authStore.hasPermission('delete tags') && h(Button, {
                variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10',
                onClick: () => deleteTag(row.original)
            }, [h(Trash2, { class: 'w-4 h-4' })])
        ])
    })
];

const sorting = ref<SortingState>([]);
const rowSelection = ref<RowSelectionState>({});

const table = useVueTable({
    get data() { return tags.value },
    columns,
    state: {
        get sorting() { return sorting.value },
        get rowSelection() { return rowSelection.value },
    },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    onRowSelectionChange: updaterOrValue => {
        rowSelection.value = typeof updaterOrValue === 'function' ? updaterOrValue(rowSelection.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
    enableRowSelection: true,
});

// Sync selectedIds with rowSelection for bulk actions
watch(rowSelection, (newSelection) => {
    selectedIds.value = Object.keys(newSelection)
        .filter(key => newSelection[key])
        .map(id => Number(id));
}, { deep: true });

// Clear selection when tags change
watch(tags, () => {
    rowSelection.value = {};
});

const onSearchInput = debounce(() => {
    fetchTags("1");
}, 500);

const fetchTags = async (page = 1) => {
    loading.value = true;
    try {
        const params: Record<string, unknown> = {
            page: page,
            per_page: pagination.value.per_page,
            search: search.value,
        };

        if (filterUsage.value !== 'all') {
            params.usage = filterUsage.value;
        }

        const response = await api.get('/manage/cms/tags', { params });
        const { data, pagination: paginationData } = parseResponse(response);
        
        tags.value = (data as unknown as Tag[]) || [];
        if (paginationData) {
            pagination.value = paginationData;
        } else {
             pagination.value = { ...pagination.value, total: tags.value.length, current_page: 1 };
        }

        try {
            const statsResponse = await api.get('/manage/cms/tags/statistics');
            statistics.value = statsResponse.data.data || statsResponse.data;
        } catch (error: unknown) {
            logger.error('Failed to fetch statistics:', error);
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch tags:', error);
    } finally {
        loading.value = false;
    }
};

const changePage = (page: number) => {
    if (page >= 1 && page <= (pagination.value.last_page || 1)) {
        fetchTags(page);
    }
};

const changePerPage = (value: string | number) => {
    pagination.value.per_page = typeof value === 'string' ? parseInt(value) : value;
    fetchTags("1");
};


const bulkDelete = async () => {
    if (selectedIds.value.length === 0) return;
    
    const confirmed = await confirm({
        title: t('modules.cms.tags.actions.bulkDelete'),
        message: t('common.messages.confirm.bulkDelete', { count: selectedIds.value.length }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.post('/manage/cms/tags/bulk-delete', { ids: selectedIds.value });
        selectedIds.value = [];
        await fetchTags(pagination.value.current_page);
        toast.success.delete(t('modules.cms.tags.title', { count: 2 }));
    } catch (error: unknown) {
        logger.error('Bulk delete failed:', error);
        toast.error.fromResponse(error);
    }
};

// Modal Actions
const openCreateModal = () => {
    editingTag.value = null;
    showModal.value = true;
};

const openEditModal = (tag: Tag) => {
    editingTag.value = { ...tag };
    showModal.value = true;
};

const handleModalSuccess = () => {
    fetchTags(pagination.value.current_page);
};

const deleteTag = async (tag: Tag) => {
    const confirmed = await confirm({
        title: t('modules.cms.tags.actions.delete'),
        message: t('modules.cms.tags.messages.deleteConfirm', { name: tag.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/manage/cms/tags/${tag.id}`);
        await fetchTags();
        toast.success.delete(t('modules.cms.tags.title_singular'));
    } catch (error: unknown) {
        logger.error('Failed to delete tag:', error);
        toast.error.delete(error, t('modules.cms.tags.title_singular'));
    }
};

onMounted(() => {
    fetchTags();
});
</script>
