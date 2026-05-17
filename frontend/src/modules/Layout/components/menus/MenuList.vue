<script setup lang="ts">
import { ref, onMounted, watch, h } from 'vue';
import { useI18n } from 'vue-i18n';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import RotateCcw from 'lucide-vue-next/dist/esm/icons/rotate-ccw.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import CheckCircle2 from 'lucide-vue-next/dist/esm/icons/circle-check-big.js';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { parseResponse, ensureArray, type PaginationData } from '@/shared/utils/responseParser';
import { cn } from '@/shared/utils/lib-utils';

// UI Components
import {
    Button,
    Input,
    Badge,
    Card,
    CardContent,
    Checkbox,
    Pagination,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    DataTable
} from '@/shared/components/ui';

import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
    getSortedRowModel,
    type SortingState,
    type RowSelectionState
} from '@tanstack/vue-table';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();

const emit = defineEmits<{
    (e: 'select-menu', id: string): void;
    (e: 'create-menu'): void;
}>();

interface Menu {
    id: string;
    name: string;
    slug: string;
    location: string;
    is_active: boolean;
    all_items_count?: number;
    deleted_at?: string | null;
    created_at: string;
}

const loading = ref(true);
const menus = ref<Menu[]>([]);
const pagination = ref<PaginationData | null>(null);
const search = ref('');
const statusFilter = ref('without'); // without, only
const perPage = ref('10');
const rowSelection = ref<RowSelectionState>({});
const bulkAction = ref('');
const trashedCount = ref(0);

const columnHelper = createColumnHelper<Menu>();

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
        header: t('modules.cms.menus.form.name'),
        cell: ({ row }) => {
            const menu = row.original;
            return h('div', { class: 'flex flex-col gap-0.5' }, [
                h('div', { class: 'flex items-center gap-2 font-medium cursor-pointer hover:text-primary transition-colors', onClick: () => emit('select-menu', menu.id) }, [
                    menu.name,
                    menu.deleted_at ? h(Badge, { variant: 'destructive', class: 'h-4 text-[9px] px-1' }, t('common.labels.trashed')) : null
                ]),
                h('span', { class: 'text-[10px] text-muted-foreground font-mono' }, menu.slug)
            ]);
        }
    }),
    columnHelper.accessor('location', {
        header: t('modules.cms.menus.form.location'),
        cell: ({ row }) => {
            const loc = row.original.location;
            if (!loc || loc === 'none') return h('span', { class: 'text-muted-foreground italic' }, t('modules.cms.menus.form.placeholders.none'));
            return h(Badge, { variant: 'secondary', class: 'capitalize' }, loc.replace(/_/g, ' '));
        }
    }),
    columnHelper.accessor('all_items_count', {
        header: t('modules.cms.menus.headers.items'),
        cell: ({ row }) => h(Badge, { variant: 'outline' }, `${row.original.all_items_count || 0} ${t('modules.cms.menus.headers.items').toLowerCase()}`)
    }),
    columnHelper.accessor('is_active', {
        header: t('common.labels.status'),
        cell: ({ row }) => {
            const active = row.original.is_active;
            return h('div', { class: 'flex items-center gap-1.5' }, [
                h('div', { class: cn('w-2 h-2 rounded-full', active ? 'bg-success' : 'bg-muted-foreground/30') }),
                h('span', { class: 'text-xs' }, active ? t('common.labels.active') : t('common.labels.inactive'))
            ]);
        }
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('common.actions.title')),
        cell: ({ row }) => {
            const menu = row.original;
            return h('div', { class: 'flex justify-end gap-1' }, [
                menu.deleted_at 
                    ? [
                        h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-success',
                            onClick: () => handleRestore(menu), title: t('common.actions.restore')
                        }, [h(RotateCcw, { class: 'w-4 h-4' })]),
                        h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-destructive',
                            onClick: () => handleForceDelete(menu), title: t('common.actions.deletePermanently')
                        }, [h(Trash2, { class: 'w-4 h-4' })])
                    ]
                    : [
                        h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8',
                            onClick: () => emit('select-menu', menu.id), title: t('common.actions.edit')
                        }, [h(Pencil, { class: 'w-4 h-4' })]),
                        h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-destructive',
                            onClick: () => handleDelete(menu), title: t('common.actions.delete')
                        }, [h(Trash2, { class: 'w-4 h-4' })])
                    ]
            ]);
        }
    })
];

const sorting = ref<SortingState>([]);

const table = useVueTable({
    get data() { return menus.value },
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

const fetchMenus = async (page: number = 1) => {
    loading.value = true;
    try {
        const response = await api.get('/manage/layout/menus', {
            params: {
                page,
                per_page: perPage.value,
                search: search.value,
                trashed: statusFilter.value
            }
        });
        
        const { data, pagination: meta } = parseResponse<Menu[]>(response);
        menus.value = ensureArray(data);
        pagination.value = meta;
        trashedCount.value = response.data?.meta?.trashed_count ?? 0;
        rowSelection.value = {}; // Reset selection
    } catch (error) {
        toast.error.action(error);
    } finally {
        loading.value = false;
    }
};

const handleDelete = async (menu: Menu) => {
    const confirmed = await confirm({
        title: t('common.actions.delete'),
        message: t('modules.cms.menus.messages.deleteConfirm', { name: menu.name }),
        variant: 'danger'
    });
    if (confirmed) {
        try {
            await api.delete(`/manage/layout/menus/${menu.id}`);
            toast.success.delete(t('modules.cms.menus.title'));
            fetchMenus();
        } catch (error) { toast.error.action(error); }
    }
};

const handleRestore = async (menu: Menu) => {
    const confirmed = await confirm({
        title: t('common.actions.restore'),
        message: t('modules.cms.menus.messages.restoreConfirm', { name: menu.name }),
        variant: 'info'
    });
    if (confirmed) {
        try {
            await api.post(`/manage/layout/menus/${menu.id}/restore`);
            toast.success.restore(t('modules.cms.menus.title'));
            fetchMenus();
        } catch (error) { toast.error.action(error); }
    }
};

const handleForceDelete = async (menu: Menu) => {
    const confirmed = await confirm({
        title: t('common.actions.deletePermanently'),
        message: t('modules.cms.menus.messages.forceDeleteConfirm', { name: menu.name }),
        variant: 'danger'
    });
    if (confirmed) {
        try {
            await api.delete(`/manage/layout/menus/${menu.id}/force-delete`);
            toast.success.delete(t('modules.cms.menus.title'));
            fetchMenus();
        } catch (error) { toast.error.action(error); }
    }
};

const handleBulkAction = async () => {
    if (!bulkAction.value) return;
    const selectedIds = Object.keys(rowSelection.value).map(Number);
    if (selectedIds.length === 0) return;

    const confirmed = await confirm({
        title: t('common.actions.bulkAction'),
        message: t('common.messages.confirm.bulkAction', { action: bulkAction.value, count: selectedIds.length }),
        variant: bulkAction.value.includes('delete') ? 'danger' : 'info'
    });

    if (confirmed) {
        try {
            await api.post('/manage/layout/menus/bulk-action', {
                action: bulkAction.value,
                menu_ids: selectedIds
            });
            toast.success.action(t('common.messages.success.action'));
            bulkAction.value = '';
            fetchMenus();
        } catch (error) { toast.error.action(error); }
    }
};

watch([search, statusFilter], () => fetchMenus(1));

onMounted(() => fetchMenus());
</script>

<template>
  <div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <Card class="bg-primary/5 border-primary/10">
        <CardContent class="p-4 flex items-center justify-between">
          <div>
            <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-wider mb-1">
              {{ t('modules.cms.menus.headers.totalMenus') }}
            </p>
            <h3 class="text-2xl font-black text-primary">
              {{ pagination?.total || 0 }}
            </h3>
          </div>
          <div class="p-3 bg-primary/10 rounded-xl text-primary">
            <FileText class="w-5 h-5" />
          </div>
        </CardContent>
      </Card>
      <Card class="border-success/10 bg-success/5">
        <CardContent class="p-4 flex items-center justify-between">
          <div>
            <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-wider mb-1">
              {{ t('modules.cms.menus.headers.activeLocations') }}
            </p>
            <h3 class="text-2xl font-black text-success">
              {{ menus.filter(m => m.location && m.location !== 'none').length }}
            </h3>
          </div>
          <div class="p-3 bg-success/10 rounded-xl text-success">
            <CheckCircle2 class="w-5 h-5" />
          </div>
        </CardContent>
      </Card>
      <Card
        class="border-destructive/10 bg-destructive/5 cursor-pointer hover:bg-destructive/10 transition-colors"
        @click="statusFilter = 'only'"
      >
        <CardContent class="p-4 flex items-center justify-between">
          <div>
            <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-wider mb-1">
              {{ t('common.labels.trashed') }}
            </p>
            <h3 class="text-2xl font-black text-destructive">
              {{ trashedCount }}
            </h3>
          </div>
          <div class="p-3 bg-destructive/10 rounded-xl text-destructive">
            <Trash2 class="w-5 h-5" />
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Table Card -->
    <Card>
      <div class="px-6 py-4 border-b border-border flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-1 items-center gap-3">
          <div class="relative w-full md:w-80">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
            <Input
              v-model="search"
              :placeholder="t('common.actions.search')"
              class="pl-9 h-10"
            />
          </div>
          <Select v-model="statusFilter">
            <SelectTrigger class="w-[180px] h-10">
              <SelectValue :placeholder="t('common.labels.status')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="without">
                {{ t('common.labels.activeOnly') }}
              </SelectItem>
              <SelectItem value="only">
                {{ t('common.labels.trashedOnly') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="flex items-center gap-2">
          <!-- Bulk Actions -->
          <div
            v-if="Object.keys(rowSelection).length > 0"
            class="flex items-center gap-2 animate-in fade-in slide-in-from-right-2 mr-2"
          >
            <div class="flex items-center gap-3 p-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10">
              <span class="text-sm font-medium text-primary">{{ t('common.messages.selectedItems', { count: Object.keys(rowSelection).length }) }}</span>
              <div class="h-4 w-px bg-primary/20" />
              <Select
                v-model="bulkAction"
                @update:model-value="handleBulkAction"
              >
                <SelectTrigger class="w-[140px] h-8 border-primary/20">
                  <SelectValue :placeholder="t('common.actions.bulkAction')" />
                </SelectTrigger>
                <SelectContent>
                  <template v-if="statusFilter !== 'only'">
                    <SelectItem
                      value="delete"
                      class="text-destructive"
                    >
                      {{ t('common.actions.delete') }}
                    </SelectItem>
                  </template>
                  <template v-else>
                    <SelectItem
                      value="restore"
                      class="text-success"
                    >
                      {{ t('common.actions.restore') }}
                    </SelectItem>
                    <SelectItem
                      value="force_delete"
                      class="text-destructive"
                    >
                      {{ t('common.actions.deletePermanently') }}
                    </SelectItem>
                  </template>
                </SelectContent>
              </Select>
            </div>
          </div>

          <Button
            size="sm"
            class="h-10"
            @click="emit('create-menu')"
          >
            <Plus class="w-4 h-4 mr-2" />
            {{ t('modules.cms.menus.actions.create') }}
          </Button>
        </div>
      </div>

      <DataTable
        :table="table"
        :loading="loading"
      />

      <div class="px-6 py-4 border-t border-border">
        <Pagination
          v-if="pagination"
          :total-items="pagination.total"
          :per-page="parseInt(perPage)"
          :current-page="pagination.current_page"
          @page-change="fetchMenus"
          @update:per-page="(val) => { perPage = String(val); fetchMenus(1); }"
        />
      </div>
    </Card>
  </div>
</template>
