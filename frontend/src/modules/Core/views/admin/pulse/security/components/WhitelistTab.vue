<template>
  <Card>
    <CardHeader class="flex flex-row items-center justify-between pb-4">
      <div>
        <CardTitle class="text-lg">
          {{ $t('features.security.whitelist.title') }}
        </CardTitle>
        <CardDescription>{{ $t('features.security.whitelist.description') }}</CardDescription>
      </div>
      <div class="flex items-center space-x-2">
        <Badge
          v-if="selectedIds.length > 0"
          variant="secondary"
        >
          {{ $t('features.security.bulkActions.selected', { count: selectedIds.length }) }}
        </Badge>
        <Button
          v-if="selectedIds.length > 0"
          variant="destructive"
          size="sm"
          @click="$emit('bulk-remove', selectedIds)"
        >
          <Trash2 class="w-4 h-4 mr-2" />
          {{ $t('features.security.bulkActions.removeSelected') }}
        </Button>
      </div>
    </CardHeader>

    <!-- Add IP Section -->
    <div class="px-6 py-4 bg-muted/20">
      <Label class="text-sm font-medium mb-2 block">
        {{ $t('features.security.whitelist.addIp') }}
      </Label>
      <div class="flex space-x-2">
        <Input
          v-model="ipToAdd"
          type="text"
          placeholder="192.168.1.1"
          class="max-w-md"
        />
        <Button
          :disabled="!ipToAdd.trim()"
          @click="handleAddIp"
        >
          <Plus class="w-4 h-4 mr-2" />
          {{ $t('common.actions.add') }}
        </Button>
      </div>
    </div>

    <CardContent class="p-0">
      <DataTable
        :table="table"
        :loading="loading"
        :empty-message="t('features.security.whitelist.empty')"
      />
    </CardContent>
    <Pagination
      v-if="whitelist.length > 0"
      v-model:per-page="perPage"
      :current-page="currentPage"
      :total-items="whitelist.length"
      class="border-none shadow-none px-6 py-4"
      @page-change="(val: number) => currentPage = val"
    />
  </Card>
</template>

<script setup lang="ts">
import { ref, computed, h } from 'vue';
import { useI18n } from 'vue-i18n';
import { useVueTable, getCoreRowModel, getSortedRowModel, createColumnHelper, type SortingState } from '@tanstack/vue-table';
import {
    Card, CardHeader, CardTitle, CardDescription, CardContent,
    Button, Badge, Input, Label, Checkbox, DataTable, Pagination
} from '@/components/ui';

import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';

interface User {
    id: number;
    name: string;
}

interface IpManagementItem {
    id: number | string;
    ip_address: string;
    reason?: string | null;
    creator?: User | null;
    created_at: string;
}

const props = defineProps<{
    whitelist: IpManagementItem[];
    loading: boolean;
}>();

const emit = defineEmits<{
    'add': [ip: string];
    'remove': [ip: string];
    'bulk-remove': [ips: string[]];
}>();

const { t } = useI18n();

// Local state
const ipToAdd = ref('');
const selectedIds = ref<string[]>([]);
const currentPage = ref(1);
const perPage = ref(10);
const sorting = ref<SortingState>([]);

// Paginated data
const paginatedWhitelist = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = Math.min(start + perPage.value, props.whitelist.length);
    return props.whitelist.slice(start, end);
});

const handleAddIp = () => {
    if (ipToAdd.value.trim()) {
        emit('add', ipToAdd.value);
        ipToAdd.value = '';
    }
};

const formatDate = (date: string | null | undefined): string => {
    if (!date) return '-';
    return new Date(date).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// TanStack Table
const columnHelper = createColumnHelper<IpManagementItem>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: () => h(Checkbox, {
            checked: selectedIds.value.length === paginatedWhitelist.value.length && paginatedWhitelist.value.length > 0,
            'onUpdate:checked': (val: boolean) => {
                selectedIds.value = val ? paginatedWhitelist.value.map(item => item.ip_address) : [];
            }
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: selectedIds.value.includes(row.original.ip_address),
            'onUpdate:checked': (val: boolean) => {
                if (val) {
                    selectedIds.value.push(row.original.ip_address);
                } else {
                    selectedIds.value = selectedIds.value.filter(id => id !== row.original.ip_address);
                }
            }
        })
    }),
    columnHelper.accessor('ip_address', {
        header: t('features.security.whitelist.table.ip'),
        cell: ({ row }) => h('span', { class: 'font-mono text-sm' }, row.original.ip_address)
    }),
    columnHelper.accessor('reason', {
        header: t('features.security.whitelist.table.reason'),
        cell: ({ row }) => h('span', { class: 'text-muted-foreground text-sm' }, row.original.reason || '-')
    }),
    columnHelper.accessor(row => row.creator?.name, {
        id: 'creator',
        header: t('features.security.whitelist.table.createdBy'),
        cell: ({ row }) => h('span', { class: 'text-sm' }, row.original.creator?.name || '-')
    }),
    columnHelper.accessor('created_at', {
        header: t('features.security.whitelist.table.date'),
        cell: ({ row }) => h('span', { class: 'text-muted-foreground whitespace-nowrap text-sm' }, formatDate(row.original.created_at))
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('features.security.whitelist.table.actions')),
        cell: ({ row }) => h('div', { class: 'text-right' }, [
            h(Button, {
                variant: 'ghost',
                size: 'icon',
                onClick: () => emit('remove', row.original.ip_address),
                class: 'text-destructive hover:text-destructive hover:bg-destructive/10 h-8 w-8'
            }, () => h(Trash2, { class: 'w-4 h-4' }))
        ])
    })
];

const table = useVueTable({
    get data() { return paginatedWhitelist.value },
    columns,
    state: {
        get sorting() { return sorting.value }
    },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
});

// Expose methods
defineExpose({
    clearSelection: () => { selectedIds.value = []; }
});
</script>
