<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { useHead } from '@unhead/vue';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import CheckCircle2 from 'lucide-vue-next/dist/esm/icons/circle-check-big.js';
import Clock3 from 'lucide-vue-next/dist/esm/icons/clock-3.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import FileEdit from 'lucide-vue-next/dist/esm/icons/file-pen.js';
import Archive from 'lucide-vue-next/dist/esm/icons/archive.js';
import RotateCcw from 'lucide-vue-next/dist/esm/icons/rotate-ccw.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Calendar from 'lucide-vue-next/dist/esm/icons/calendar.js';
import { useAuthStore } from '@/modules/System/stores/auth';
import { useCmsStore } from '@/modules/Cms/stores/cms';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useToast } from '@/shared/composables/useToast';
import api from '@/engine/api/client';
import { parseResponse, parseSingleResponse, ensureArray, type PaginationData } from '@/shared/utils/responseParser';
import { cn } from '@/shared/utils/lib-utils';
import type { Content } from '@/modules/Cms/types/cms';

// UI Components
import {
    Button,
    Input,
    Badge,
    Card,
    CardContent,
    Checkbox,
    Switch,
    Pagination,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    DataTable
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

interface ContentStats {
    total: number;
    published: number;
    draft: number;
    pending: number;
    archived: number;
    trashed: number;
}

interface ContentFilter {
    page: number;
    per_page: string;
    sort: string;
    order: string;
    search?: string;
    status?: string;
    [key: string]: string | number | undefined;
}

interface ConfirmOptions {
    title: string;
    message: string;
    variant?: 'danger' | 'warning' | 'info' | 'success';
    confirmText?: string;
}

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const cmsStore = useCmsStore();
const { confirm } = useConfirm();
const toast = useToast();

const props = defineProps<{
    isEmbedded?: boolean;
}>();

if (!props.isEmbedded) {
    useHead({
        title: computed(() => `${cmsStore.siteSettings?.site_name || t('app.name')} | ${t('modules.cms.content.list.title')}`)
    });
}

const loading = ref(true);
const contents = ref<Content[]>([]);
const pagination = ref<PaginationData | null>(null);
const search = ref('');
const statusFilter = ref('all');
const perPage = ref('10');
const selectedContents = ref<number[]>([]);
const bulkAction = ref('');

const columnHelper = createColumnHelper<Content>();

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
    columnHelper.accessor('title', {
        header: t('common.labels.title'),
        cell: ({ row }) => {
            const content = row.original;
            return h('div', { class: 'flex flex-col gap-0.5' }, [
                h('div', { class: 'flex items-center gap-2' }, [
                    h('span', { class: 'text-sm font-semibold text-foreground group-hover:text-primary transition-colors' }, content.title),
                    content.deleted_at ? h(Badge, { variant: 'destructive', class: 'h-4.5 text-[9px] px-1.5 font-bold tracking-wider' }, t('modules.cms.content.status.trashed')) : null
                ]),
                h('span', { class: 'text-xs text-muted-foreground/70 font-mono' }, content.slug)
            ]);
        }
    }),
    columnHelper.accessor('author', {
        header: t('common.labels.author'),
        cell: ({ row }) => {
            const author = row.original.author;
            return h('div', { class: 'flex items-center gap-2' }, [
                h('div', { class: 'w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold text-primary' }, getUserInitials(author?.name)),
                h('span', { class: 'text-sm text-foreground/80' }, author?.name)
            ]);
        }
    }),
    columnHelper.accessor('status', {
        header: t('common.labels.status'),
        cell: ({ row }) => {
            const status = row.original.status || '';
            return h(Badge, {
                variant: 'outline',
                class: cn('capitalize border-none px-2 py-0.5', getStatusBadgeClass(status))
            }, t(`modules.cms.content.status.${status}`));
        }
    }),
    columnHelper.accessor('is_featured', {
        header: t('modules.cms.content.form.featured'),
        cell: ({ row }) => h(Switch, {
            checked: !!row.original.is_featured,
            'onUpdate:checked': () => toggleFeatured(row.original)
        })
    }),
    columnHelper.accessor('created_at', {
        header: t('common.labels.date'),
        cell: ({ row }) => h('div', { class: 'flex items-center gap-1.5 text-xs text-muted-foreground' }, [
            h(Calendar, { class: 'w-3.5 h-3.5' }),
            formatDate(row.original.created_at)
        ])
    }),

    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('common.actions.title')),
        cell: ({ row }) => {
            const content = row.original;
            return h('div', { class: 'flex justify-end items-center gap-1' }, [
                content.deleted_at 
                    ? [
                        authStore.hasPermission('delete content') && h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-success',
                            onClick: () => handleRestore(content), title: t('common.actions.restore')
                        }, [h(RotateCcw, { class: 'w-4 h-4' })]),
                        authStore.hasPermission('delete content') && h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-destructive',
                            onClick: () => handleForceDelete(content), title: t('common.actions.deletePermanently')
                        }, [h(Trash2, { class: 'w-4 h-4' })])
                    ]
                    : [
                        authStore.hasPermission('edit content') && h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8',
                            onClick: () => handleEdit(content), title: t('common.actions.edit')
                        }, [h(Pencil, { class: 'w-4 h-4' })]),
                        authStore.hasPermission('delete content') && h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-destructive',
                            onClick: () => handleDelete(content), title: t('common.actions.delete')
                        }, [h(Trash2, { class: 'w-4 h-4' })])
                    ]
            ]);
        }
    })
];

const sorting = ref<SortingState>([]);
const rowSelection = ref<RowSelectionState>({});

const table = useVueTable({
    get data() { return contents.value },
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

// Sync selectedContents with rowSelection for bulk actions
watch(rowSelection, (newSelection) => {
    selectedContents.value = Object.keys(newSelection)
        .filter(key => newSelection[key])
        .map(id => Number(id));
}, { deep: true });

// Clear selection when contents change
watch(contents, () => {
    rowSelection.value = {};
});

const stats = ref<ContentStats>({
    total: 0,
    published: 0,
    draft: 0,
    pending: 0,
    archived: 0,
    trashed: 0
});

const fetchContents = async (page: number = 1) => {
    loading.value = true;
    try {
        const params: ContentFilter = {
            page,
            per_page: perPage.value,
            sort: 'created_at',
            order: 'desc',
        };

        if (search.value) params.search = search.value;
        if (statusFilter.value && statusFilter.value !== 'all') {
            params.status = statusFilter.value;
        }

        const response = await api.get('/manage/cms/contents', { params });
        const { data, pagination: meta } = parseResponse<Content[]>(response);
        
        contents.value = ensureArray(data);
        pagination.value = meta;
        
    } catch (error: unknown) {
        logger.error('Failed to fetch contents:', error);
        toast.error.action(error);
        contents.value = [];
        pagination.value = null;
    } finally {
        loading.value = false;
    }
};

const fetchStats = async () => {
    try {
        const response = await api.get('/manage/cms/contents/stats');
        const data = parseSingleResponse<ContentStats>(response);
        stats.value = data || {
            total: 0,
            published: 0,
            draft: 0,
            pending: 0,
            archived: 0,
            trashed: 0
        };
    } catch (error: unknown) {
        logger.error('Failed to fetch stats:', error);
    }
};


const toggleFeatured = async (content: Content) => {
    const previousState = !!content.is_featured;
    content.is_featured = !previousState;

    try {
        await api.patch(`/manage/cms/contents/${content.id}/toggle-featured`);
        toast.success.action(t('common.messages.success.updated'));
    } catch (error: unknown) {
        content.is_featured = previousState;
        toast.error.action(error);
    }
};

const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'published': return 'bg-success/10 text-success border-success/20';
        case 'draft': return 'bg-muted text-muted-foreground border-border/40';
        case 'pending': return 'bg-warning/10 text-warning border-warning/20';
        case 'archived': return 'bg-primary/10 text-primary border-primary/20';
        case 'trashed': return 'bg-destructive/10 text-destructive border-destructive/20';
        default: return 'bg-muted text-muted-foreground border-border/40';
    }
};

const getUserInitials = (name?: string) => {
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};

const handleBulkAction = async (action: string) => {
    if (!action) return;
    
    const confirmConfig: ConfirmOptions = {
        title: t('modules.cms.content.list.bulkActions'),
        message: t('common.messages.confirm.bulkAction', { action: action, count: selectedContents.value.length }),
    };
    
    if (action === 'delete') {
        confirmConfig.variant = 'danger';
        confirmConfig.confirmText = t('common.actions.delete');
    } else if (action === 'approve') {
        confirmConfig.variant = 'success';
        confirmConfig.confirmText = t('modules.cms.content.actions.approve');
    } else if (action === 'reject') {
        confirmConfig.variant = 'danger';
        confirmConfig.confirmText = t('modules.cms.content.actions.reject');
    } else if (action === 'restore') {
        confirmConfig.variant = 'info';
        confirmConfig.confirmText = t('common.actions.restore');
    } else if (action === 'force_delete') {
        confirmConfig.variant = 'danger';
        confirmConfig.confirmText = t('common.actions.deletePermanently');
    }

    const confirmed = await confirm(confirmConfig);

    if (!confirmed) {
        bulkAction.value = '';
        return;
    }

    try {
        await api.post('/manage/cms/contents/bulk-action', {
            action: action,
            content_ids: selectedContents.value,
        });
        selectedContents.value = []; // Clear selection
        await fetchContents();
        await fetchStats();
        toast.success.update();
        bulkAction.value = '';
    } catch (error: unknown) {
        logger.error('Failed to perform bulk action:', error);
        toast.error.action(error);
    } finally {
        bulkAction.value = '';
    }
};

const handleEmptyTrash = async () => {
    const confirmed = await confirm({
        title: t('modules.cms.content.actions.emptyTrash'),
        message: t('common.messages.confirm.emptyTrash'),
        confirmText: t('common.actions.deletePermanently'),
        variant: 'danger'
    });

    if (!confirmed) return;

    try {
        await api.delete('/manage/cms/contents/trash/empty');
        await fetchContents();
        await fetchStats();
        toast.success.action(t('common.messages.success.deleted'));
    } catch (error: unknown) {
        logger.error('Failed to empty trash:', error);
        toast.error.action(error);
    }
};

const handleDelete = async (content: Content) => {
    const confirmed = await confirm({
        title: t('common.actions.delete'),
        message: t('common.messages.confirm.delete', { item: content.title }),
        confirmText: t('common.actions.delete'),
        variant: 'danger'
    });
    if (!confirmed) return;
    try {
        await api.delete(`/manage/cms/contents/${content.id}`);
        await fetchContents();
        await fetchStats();
    } catch (error: unknown) {
        logger.error('Failed to delete content:', error);
        toast.error.delete(error, content.title);
    }
};

const handleRestore = async (content: Content) => {
    const confirmed = await confirm({
        title: t('common.actions.restore'),
        message: t('common.messages.confirm.restore', { item: content.title }),
        confirmText: t('common.actions.restore'),
        variant: 'info'
    });

    if (!confirmed) return;

    try {
        await api.put(`/manage/cms/contents/${content.id}/restore`);
        await fetchContents();
        await fetchStats();
        toast.success.action(t('common.messages.success.restored'));
    } catch (error: unknown) {
        logger.error('Failed to restore content:', error);
        toast.error.action(error);
    }
};

const handleForceDelete = async (content: Content) => {
    const confirmed = await confirm({
        title: t('common.actions.deletePermanently'),
        message: t('common.messages.confirm.deletePermanently', { item: content.title }),
        confirmText: t('common.actions.delete'),
        variant: 'danger'
    });
    if (!confirmed) return;
    try {
        await api.delete(`/manage/cms/contents/${content.id}/force-delete`);
        await fetchContents();
        await fetchStats();
    } catch (error: unknown) {
        logger.error('Failed to force delete content:', error);
        toast.error.delete(error, content.title);
    }
};

const handleEdit = (content: Content) => {
    router.push({ name: 'contents.edit', params: { id: content.id } });
};


const formatDate = (date: string | undefined) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString();
};

watch([search, statusFilter], () => {
    fetchContents();
});

onMounted(() => {
    if (route.query.q) {
        search.value = route.query.q as string;
    }
    fetchContents();
    fetchStats();
});
</script>

<template>
  <div :class="isEmbedded ? 'space-y-6' : 'container mx-auto p-6 space-y-8'">
    <div
      v-if="!isEmbedded"
      class="mb-6"
    >
      <!-- Header removed or simplified since it's redundant in Studio -->
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
      <!-- Total Contents -->
      <Card 
        :class="cn(
          'cursor-pointer transition-colors hover:shadow-sm border',
          statusFilter === 'all' ? 'border-primary bg-primary/5' : 'border-border bg-transparent'
        )"
        @click="statusFilter = 'all'"
      >
        <CardContent class="p-3">
          <div class="flex items-center justify-between">
            <div class="space-y-0.5">
              <p class="text-[10px] font-bold text-muted-foreground">
                {{ $t('features.dashboard.stats.totalContents') }}
              </p>
              <p class="text-xl font-black text-foreground">
                {{ stats.total || 0 }}
              </p>
            </div>
            <div class="p-2 bg-primary/10 rounded-lg text-primary">
              <FileText class="w-4 h-4" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Published -->
      <Card 
        :class="cn('cursor-pointer hover:shadow-sm border transition-colors',
                   statusFilter === 'published' ? 'border-success bg-success/5' : 'border-border bg-transparent')"
        @click="statusFilter = 'published'"
      >
        <CardContent class="p-3">
          <div class="flex items-center justify-between">
            <div class="space-y-0.5">
              <p class="text-[10px] font-bold text-muted-foreground">
                {{ $t('modules.cms.content.status.published') }}
              </p>
              <p class="text-xl font-black text-success">
                {{ stats.published || 0 }}
              </p>
            </div>
            <div class="p-2 bg-success/10 rounded-lg text-success">
              <CheckCircle2 class="w-4 h-4" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Draft -->
      <Card 
        :class="cn('cursor-pointer hover:shadow-sm border transition-colors',
                   statusFilter === 'draft' ? 'border-primary bg-primary/5' : 'border-border bg-transparent')"
        @click="statusFilter = 'draft'"
      >
        <CardContent class="p-3">
          <div class="flex items-center justify-between">
            <div class="space-y-0.5">
              <p class="text-[10px] font-bold text-muted-foreground">
                {{ $t('modules.cms.content.status.draft') }}
              </p>
              <p class="text-xl font-black text-primary">
                {{ stats.draft || 0 }}
              </p>
            </div>
            <div class="p-2 bg-primary/10 rounded-lg text-primary">
              <FileEdit class="w-4 h-4" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Pending -->
      <Card 
        :class="cn('cursor-pointer hover:shadow-sm border transition-colors',
                   statusFilter === 'pending' ? 'border-warning bg-warning/5' : 'border-border bg-transparent')"
        @click="statusFilter = 'pending'"
      >
        <CardContent class="p-3">
          <div class="flex items-center justify-between">
            <div class="space-y-0.5">
              <p class="text-[10px] font-bold text-muted-foreground">
                {{ $t('modules.cms.content.status.pending') }}
              </p>
              <p class="text-xl font-black text-warning">
                {{ stats.pending || 0 }}
              </p>
            </div>
            <div class="p-2 bg-warning/10 rounded-lg text-warning">
              <Clock3 class="w-4 h-4" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Archived -->
      <Card 
        :class="cn('cursor-pointer hover:shadow-sm border transition-colors',
                   statusFilter === 'archived' ? 'border-primary bg-primary/5' : 'border-border bg-transparent')"
        @click="statusFilter = 'archived'"
      >
        <CardContent class="p-3">
          <div class="flex items-center justify-between">
            <div class="space-y-0.5">
              <p class="text-[10px] font-bold text-muted-foreground">
                {{ $t('modules.cms.content.status.archived') }}
              </p>
              <p class="text-xl font-black text-primary">
                {{ stats.archived || 0 }}
              </p>
            </div>
            <div class="p-2 bg-primary/10 rounded-lg text-primary">
              <Archive class="w-4 h-4" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Trashed -->
      <Card 
        :class="cn('cursor-pointer hover:shadow-sm border transition-colors',
                   statusFilter === 'trashed' ? 'border-destructive bg-destructive/5' : 'border-border bg-transparent')"
        @click="statusFilter = 'trashed'"
      >
        <CardContent class="p-3">
          <div class="flex items-center justify-between">
            <div class="space-y-0.5">
              <p class="text-[10px] font-bold text-muted-foreground">
                {{ $t('modules.cms.content.status.trashed') }}
              </p>
              <p class="text-xl font-black text-destructive">
                {{ stats.trashed || 0 }}
              </p>
            </div>
            <div class="p-2 bg-destructive/10 rounded-lg text-destructive">
              <Trash2 class="w-4 h-4" />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card class="overflow-hidden">
      <!-- Filters -->
      <!-- Filters -->
      <div class="px-6 py-4 border-b border-border">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <!-- Left: Search & Filter -->
          <div class="flex items-center gap-2 flex-1 flex-wrap">
            <div class="relative w-full md:w-72">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
              <Input
                v-model="search"
                type="text"
                :placeholder="$t('common.actions.search') + '...'"
                class="pl-9"
              />
            </div>
            <Select v-model="statusFilter">
              <SelectTrigger class="w-[180px]">
                <SelectValue :placeholder="t('modules.cms.content.list.filterByStatus')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ $t('common.labels.all') }} {{ $t('common.labels.status') }}
                </SelectItem>
                <SelectItem value="published">
                  {{ $t('modules.cms.content.status.published') }}
                </SelectItem>
                <SelectItem value="draft">
                  {{ $t('modules.cms.content.status.draft') }}
                </SelectItem>
                <SelectItem value="pending">
                  {{ $t('modules.cms.content.status.pending') }}
                </SelectItem>
                <SelectItem value="archived">
                  {{ $t('modules.cms.content.status.archived') }}
                </SelectItem>
                <SelectItem value="trashed">
                  {{ $t('modules.cms.content.status.trashed') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Right: Actions -->
          <div class="flex items-center gap-2">
            <!-- Empty Trash -->
            <Button 
              v-if="statusFilter === 'trashed' && stats.trashed > 0 && selectedContents.length === 0 && authStore.hasPermission('delete content')" 
              variant="destructive" 
              size="sm" 
              @click="handleEmptyTrash" 
            >
              <Trash2 class="w-4 h-4 mr-2" />
              {{ t('modules.cms.content.actions.emptyTrash') || 'Empty Trash' }}
            </Button>

            <!-- Bulk Actions -->
            <div
              v-if="selectedContents.length > 0"
              class="flex items-center gap-2 animate-in fade-in slide-in-from-right-2 mr-2"
            >
              <div class="flex items-center gap-3 p-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10">
                <span class="text-sm font-medium text-primary whitespace-nowrap">
                  {{ t('modules.cms.content.list.selected', { count: selectedContents.length }) }}
                </span>
                <div class="h-4 w-px bg-primary/20" />
                <Select
                  v-model="bulkAction"
                  @update:model-value="(val: string) => handleBulkAction(val)"
                >
                  <SelectTrigger class="w-[140px] h-8 border-primary/20">
                    <SelectValue :placeholder="t('modules.cms.content.list.bulkActions')" />
                  </SelectTrigger>
                  <SelectContent>
                    <template v-if="statusFilter !== 'trashed'">
                      <SelectItem
                        v-if="authStore.hasPermission('approve content')"
                        value="approve"
                        class="text-success focus:text-success"
                      >
                        {{ $t('modules.cms.content.actions.approve') }}
                      </SelectItem>
                      <SelectItem
                        v-if="authStore.hasPermission('approve content')"
                        value="reject"
                        class="text-destructive focus:text-destructive"
                      >
                        {{ $t('modules.cms.content.actions.reject') }}
                      </SelectItem>
                      <SelectItem
                        v-if="authStore.hasPermission('delete content')"
                        value="delete"
                        class="text-destructive focus:text-destructive"
                      >
                        {{ $t('common.actions.delete') }}
                      </SelectItem>
                    </template>
                    <template v-else>
                      <SelectItem
                        v-if="authStore.hasPermission('delete content')"
                        value="restore"
                        class="text-success focus:text-success"
                      >
                        {{ $t('common.actions.restore') }}
                      </SelectItem>
                      <SelectItem
                        v-if="authStore.hasPermission('delete content')"
                        value="force_delete"
                        class="text-destructive focus:text-destructive"
                      >
                        {{ $t('common.actions.deletePermanently') }}
                      </SelectItem>
                    </template>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <!-- Create Button -->
            <Button
              v-if="isEmbedded && authStore.hasPermission('create content')"
              size="sm"
              @click="router.push({ name: 'contents.create' })"
            >
              <Plus class="w-4 h-4 mr-1" />
              {{ $t('modules.cms.content.list.createNew') }}
            </Button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <DataTable
          :table="table"
          :loading="loading"
          :empty-message="t('common.messages.empty.default')"
        />
      </div>
            
      <div class="px-6 py-4 border-t border-border">
        <Pagination
          v-if="pagination"
          :total-items="pagination.total || 0"
          :per-page="parseInt(perPage) || 10"
          :current-page="pagination.current_page || 1"
          @page-change="fetchContents"
          @update:per-page="(val: number) => { perPage = String(val); fetchContents("1"); }"
        />
      </div>
    </Card>
  </div>
</template>
