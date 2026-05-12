<template>
  <div class="space-y-6">
    <!-- Header -->
    <div
      v-if="!isEmbedded"
      class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4"
    >
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-foreground">
          {{ $t('modules.cms.categories.title') }}
        </h1>
        <p class="text-muted-foreground mt-2">
          {{ $t('modules.cms.categories.description') }}
        </p>
      </div>
      <div class="flex space-x-3">
        <Button 
          v-if="authStore.hasPermission('create categories')"
          @click="openCreateModal"
        >
          <Plus class="w-4 h-4 mr-2" />
          {{ $t('modules.cms.categories.createNew') }}
        </Button>
      </div>
    </div>

    <!-- content -->
    <Card class="overflow-hidden">
      <!-- Filters -->
      <div class="px-6 py-4 border-b border-border/40">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <!-- Left: Search / Filter -->
          <div class="flex items-center gap-2 flex-1 flex-wrap">
            <div class="relative w-full sm:w-72">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                v-model="search"
                :placeholder="$t('modules.cms.categories.search')"
                class="pl-9"
              />
            </div>
            <!-- Status Filter -->
            <Select
              v-model="statusFilter"
              @update:model-value="onFilterChange"
            >
              <SelectTrigger class="w-[140px]">
                <Filter class="w-4 h-4 mr-2 text-muted-foreground" />
                <SelectValue :placeholder="$t('common.labels.status')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ $t('common.labels.all') }}
                </SelectItem>
                <SelectItem value="active">
                  {{ $t('modules.cms.categories.status.active') }}
                </SelectItem>
                <SelectItem value="inactive">
                  {{ $t('modules.cms.categories.status.inactive') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Right: Actions -->
          <div class="flex items-center gap-2">
            <!-- Bulk Actions -->
            <div
              v-if="selectedIds.length > 0"
              class="flex items-center gap-3 p-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10 animate-in fade-in slide-in-from-right-2 mr-2"
            >
              <span class="text-xs font-semibold text-primary whitespace-nowrap uppercase tracking-wider">
                {{ selectedIds.length }} {{ $t('common.labels.selected') }}
              </span>
              <div class="h-4 w-px bg-primary/20" />
              <Button
                variant="ghost"
                size="sm"
                class="h-7 px-2 text-destructive hover:bg-destructive/10"
                @click="confirmBulkDelete"
              >
                <Trash2 class="w-4 h-4 mr-1.5" />
                {{ $t('common.actions.delete') }}
              </Button>
            </div>

            <!-- Create Button -->
            <Button 
              v-if="isEmbedded && authStore.hasPermission('create categories')"
              size="sm"
              @click="openCreateModal"
            >
              <Plus class="w-4 h-4 mr-1" />
              {{ $t('modules.cms.categories.createNew') }}
            </Button>
          </div>
        </div>
      </div>
      <CardContent class="p-0">
        <div class="rounded-md border-0">
          <DataTable
            :table="table"
            :loading="loading"
            :empty-message="$t('modules.cms.categories.empty')"
          />
        </div>
      </CardContent>
      <Pagination
        v-if="pagination"
        :current-page="pagination.current_page"
        :total-items="pagination.total"
        :per-page="Number(pagination.per_page)"
        :show-page-numbers="true"
        class="border-none shadow-none mt-4"
        @page-change="changePage"
        @update:per-page="changePerPage"
      />
    </Card>

    <!-- Category Modal -->
    <CategoryFormModal
      v-model:open="showModal"
      :category="editingCategory"
      :categories="categories"
      @success="handleModalSuccess"
    />
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/core/api/client';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Edit2 from 'lucide-vue-next/dist/esm/icons/pen-line.js';
import ChevronRight from 'lucide-vue-next/dist/esm/icons/chevron-right.js';
import Filter from 'lucide-vue-next/dist/esm/icons/list-filter.js';
import { debounce } from '@/shared/utils/debounce';
import CategoryFormModal from './CategoryFormModal.vue';

// UI Components
import {
    Button,
    Input,
    Badge,
    Checkbox,
    Card,
    CardContent,
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

// Stores & Composables
import { useAuthStore } from '@/modules/Core/stores/auth';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useToast } from '@/shared/composables/useToast';
import type { Category } from '@/modules/Cms/types/cms';
import { parseResponse, type PaginationData } from '@/shared/utils/responseParser';

interface FlatCategory extends Category {
    _depth: number;
}

defineProps<{
    isEmbedded?: boolean;
}>();

const { t } = useI18n();
const authStore = useAuthStore();
const toast = useToast();

const loading = ref(true);
const categories = ref<Category[]>([]); // Raw tree data
const search = ref('');
const statusFilter = ref('all');
const selectedIds = ref<number[]>([]);
const expandedIds = ref<number[]>([]); // Track expanded nodes
const pagination = ref<PaginationData>({
    current_page: 1,
    last_page: 1,
    per_page: 5,
    total: 0,
    from: 1,
    to: 1
});

// Modal State
const showModal = ref(false);
const editingCategory = ref<Category | null>(null);

const { confirm } = useConfirm();

// Toggle expand/collapse
const toggleExpand = (id: number) => {
    const index = expandedIds.value.indexOf(id);
    if (index > -1) {
        expandedIds.value.splice(index, 1);
    } else {
        expandedIds.value.push(id);
    }
};

// Helper to check if category has children
const hasChildren = (category: Category): boolean => {
    const children = category.all_children || category.children;
    return !!(children && Array.isArray(children) && children.length > 0);
};

// Helper to collect all parent IDs for auto-expansion
const getAllParentIds = (nodes: Category[]): number[] => {
    let ids: number[] = [];
    nodes.forEach(node => {
        const children = node.all_children || node.children;
        if (children && children.length > 0) {
            ids.push(node.id);
            ids = ids.concat(getAllParentIds(children));
        }
    });
    return ids;
};

// Flatten tree into array for Table display, calculating depth
const flattenTree = (nodes: Category[], depth = 0): FlatCategory[] => {
    if (!nodes) return [];
    let result: FlatCategory[] = [];
    nodes.forEach(node => {
        const flatNode: FlatCategory = { ...node, _depth: depth };
        result.push(flatNode);
        
        const children = node.all_children || node.children;
        const hasKids = children && Array.isArray(children) && children.length > 0;
        
        if (hasKids && (search.value || expandedIds.value.includes(node.id))) {
            result = result.concat(flattenTree(children, depth + 1));
        }
    });
    return result;
};

// Computed flat list for display
const flatCategories = computed(() => {
    return flattenTree(categories.value);
});

const columnHelper = createColumnHelper<FlatCategory>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h('div', { class: 'text-center' }, [
            authStore.hasPermission('delete categories') && h(Checkbox, {
                checked: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
                'onUpdate:checked': (val) => table.toggleAllPageRowsSelected(!!val),
            })
        ]),
        cell: ({ row }) => h('div', { class: 'text-center' }, [
            authStore.hasPermission('delete categories') && h(Checkbox, {
                checked: row.getIsSelected(),
                'onUpdate:checked': (val) => row.toggleSelected(!!val),
            })
        ]),
        size: 40,
    }),
    columnHelper.accessor('name', {
        header: t('modules.cms.categories.table.name'),
        cell: ({ row }) => {
            const category = row.original;
            return h('div', {
                style: { paddingLeft: `${category._depth * 24}px` },
                class: 'flex items-center'
            }, [
                hasChildren(category) ? h(Button, {
                    variant: 'ghost',
                    size: 'icon',
                    class: 'h-6 w-6 mr-2 shrink-0',
                    onClick: (e: Event) => {
                        e.stopPropagation();
                        toggleExpand(category.id);
                    }
                }, [
                    h(ChevronRight, {
                        class: [
                            'w-4 h-4 transition-transform duration-200',
                            expandedIds.value.includes(category.id) ? 'rotate-90' : ''
                        ]
                    })
                ]) : h('span', { class: 'w-6 mr-2 shrink-0' }),
                h('span', { class: 'font-medium' }, category.name)
            ]);
        }
    }),
    columnHelper.accessor('slug', {
        header: t('modules.cms.categories.table.slug'),
        cell: ({ row }) => h('span', { class: 'text-muted-foreground font-mono text-xs' }, row.original.slug)
    }),
    columnHelper.accessor('is_active', {
        header: t('modules.cms.categories.table.status'),
        cell: ({ row }) => h(Badge, {
            variant: row.original.is_active ? 'default' : 'secondary'
        }, row.original.is_active ? t('modules.cms.categories.status.active') : t('modules.cms.categories.status.inactive'))
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-center' }, t('modules.cms.categories.table.actions')),
        cell: ({ row }) => h('div', { class: 'flex justify-center gap-1' }, [
            authStore.hasPermission('edit categories') && h(Button, {
                variant: 'ghost',
                size: 'icon',
                class: 'h-8 w-8 text-muted-foreground hover:text-foreground',
                onClick: () => openEditModal(row.original)
            }, [h(Edit2, { class: 'w-4 h-4' })]),
            authStore.hasPermission('delete categories') && h(Button, {
                variant: 'ghost',
                size: 'icon',
                class: 'h-8 w-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10',
                onClick: () => deleteCategory(row.original)
            }, [h(Trash2, { class: 'w-4 h-4' })])
        ])
    })
];

const sorting = ref<SortingState>([]);
const rowSelection = ref<RowSelectionState>({});

const table = useVueTable({
    get data() { return flatCategories.value },
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

// Clear selection when categories change
watch(categories, () => {
    rowSelection.value = {};
});



const fetchCategories = async (page = 1) => {
    loading.value = true;
    try {
        const params: Record<string, string | number | boolean> = {
            page: page,
            per_page: Number(pagination.value.per_page),
            tree: true 
        };
        
        if (search.value) {
            params.tree = false;
            params.search = search.value;
        }

        if (statusFilter.value && statusFilter.value !== 'all') {
            params.is_active = statusFilter.value === 'active' ? 1 : 0;
        }

        const response = await api.get('/admin/cms/categories', { params });
        const { data, pagination: paginationData } = parseResponse<Category>(response);
        
        categories.value = data || [];

        // Auto-expand all by default on first load or page change
        if (!search.value) {
            expandedIds.value = getAllParentIds(categories.value);
        } else {
            expandedIds.value = [];
        }

        if (paginationData) {
            pagination.value = paginationData;
        } else {
            pagination.value.total = categories.value.length;
            pagination.value.current_page = 1;
        }
        
        selectedIds.value = [];
    } catch (error: unknown) {
        logger.error('Failed to fetch categories:', error);
    } finally {
        loading.value = false;
    }
};

const debouncedSearch = debounce(() => {
    fetchCategories(1);
}, 300);

// Watch search input
watch(search, () => {
    debouncedSearch();
});

// Handle filter change
const onFilterChange = () => {
    fetchCategories(1);
};

const changePage = (page: number) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchCategories(page);
    }
};

const changePerPage = (perPage: string | number) => {
    pagination.value.per_page = typeof perPage === 'string' ? parseInt(perPage) : perPage;
    fetchCategories(1);
};

// Modal Actions
const openCreateModal = () => {
    editingCategory.value = null;
    showModal.value = true;
};

const openEditModal = (category: Category) => {
    // Clone to specific object to avoid binding issues
    editingCategory.value = { ...category };
    showModal.value = true;
};

const handleModalSuccess = () => {
    fetchCategories(pagination.value.current_page);
};

// const editCategory REMOVED - replaced by openEditModal

const deleteCategory = async (category: Category) => {
    const confirmed = await confirm({
        title: t('modules.cms.categories.actions.delete'),
        message: t('modules.cms.categories.messages.deleteConfirm', { name: category.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (confirmed) {
        try {
            await api.delete(`/admin/cms/categories/${category.id}`);
            fetchCategories(pagination.value.current_page);
            toast.success.delete(t('modules.cms.categories.title_singular'));
        } catch (error: unknown) {
            logger.error('Failed to delete category:', error);
            toast.error.delete(error, t('modules.cms.categories.title_singular'));
        }
    }
};

// Bulk Actions

const confirmBulkDelete = async () => {
   const confirmed = await confirm({
       title: t('common.actions.bulkDelete'),
       message: t('common.messages.confirm.bulkDelete', { count: selectedIds.value.length }),
       variant: 'danger',
       confirmText: t('common.actions.delete'),
   });

   if (confirmed) {
        try {
            await api.post('/admin/cms/categories/bulk-destroy', { ids: selectedIds.value });
            const count = selectedIds.value.length;
            selectedIds.value = [];
            fetchCategories(pagination.value.current_page);
            toast.success.delete(t('modules.cms.categories.title', { count: count }));
        } catch (error: unknown) {
           logger.error('Bulk delete failed:', error);
           toast.error.action(error as Record<string, unknown>);
        }
    }
}

onMounted(() => {
    fetchCategories();
});
</script>
