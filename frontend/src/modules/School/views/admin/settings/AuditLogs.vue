<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-foreground">
          {{ $t('features.school.audit.title') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ $t('features.school.audit.subtitle') }}
        </p>
      </div>
    </div>

    <Card>
      <CardContent class="p-0">
        <div class="p-6 border-b flex justify-between items-center">
          <div class="flex gap-4">
            <div class="relative w-64">
              <LucideIcon
                name="Search"
                class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground"
              />
              <Input
                v-model="filters.search"
                :placeholder="$t('features.school.audit.placeholders.search')"
                class="pl-9"
              />
            </div>
          </div>
          <Button
            variant="outline"
            @click="fetchLogs"
          >
            <LucideIcon
              name="RefreshCcw"
              class="w-4 h-4 mr-2"
            />
            {{ $t('common.actions.refresh') }}
          </Button>
        </div>

        <DataTable
          :table="table"
          :loading="loading"
        />
        
        <div class="p-4 border-t">
          <Pagination 
            v-if="pagination && (pagination.total || 0) > (pagination.per_page || 0)" 
            :total-items="pagination.total || 0" 
            :per-page="pagination.per_page || 20"
            :current-page="pagination.current_page || 1"
            @page-change="page => { if (pagination) { pagination.current_page = page; fetchLogs(); } }"
          />
        </div>
      </CardContent>
    </Card>

    <!-- Detail Dialog -->
    <Dialog v-model:open="dialogs.detail">
      <DialogContent class="sm:max-w-[600px]">
        <DialogHeader>
          <DialogTitle>{{ $t('features.school.audit.labels.details') }}</DialogTitle>
        </DialogHeader>
        <div
          v-if="selectedLog"
          class="space-y-4 py-4"
        >
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-muted-foreground">
                {{ $t('features.school.audit.labels.causer') }}
              </p>
              <p class="font-medium">
                {{ selectedLog.causer?.name || 'System' }}
              </p>
            </div>
            <div>
              <p class="text-muted-foreground">
                {{ $t('features.school.audit.labels.time') }}
              </p>
              <p class="font-medium">
                {{ new Date(selectedLog.created_at).toLocaleString($t('common.language') === 'id' ? 'id-ID' : 'en-US') }}
              </p>
            </div>
            <div class="col-span-2">
              <p class="text-muted-foreground">
                {{ $t('features.school.audit.labels.description') }}
              </p>
              <p class="font-medium">
                {{ selectedLog.description }}
              </p>
            </div>
          </div>
           
          <div
            v-if="selectedLog.properties"
            class="space-y-2"
          >
            <p class="text-sm text-muted-foreground">
              {{ $t('features.school.audit.labels.properties') }}:
            </p>
            <pre class="bg-muted p-4 rounded-lg text-xs overflow-auto max-h-60">{{ JSON.stringify(selectedLog.properties, null, 2) }}</pre>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  Card, CardContent, Button, LucideIcon, DataTable, Input,
  Dialog, DialogContent, DialogHeader, DialogTitle, Pagination
} from '@/components/ui';
import api from '@/services/api';
import { parseResponse } from '@/utils/responseParser';
import { createColumnHelper, useVueTable, getCoreRowModel } from '@tanstack/vue-table';

const { t } = useI18n();
const loading = ref(false);
const logs = ref<any[]>([]);
const pagination = ref({ total: 0, per_page: 20, current_page: 1 });
const selectedLog = ref<any>(null);
const dialogs = ref({ detail: false });

const filters = ref({
  search: ''
});

const columnHelper = createColumnHelper<any>();

const columns = [
  columnHelper.accessor('created_at', { 
    header: t('features.school.audit.labels.time'), 
    cell: info => new Date(info.getValue()).toLocaleString(t('common.language') === 'id' ? 'id-ID' : 'en-US') 
  }),
  columnHelper.accessor('causer.name', { 
    header: t('features.school.audit.labels.admin'), 
    cell: info => info.getValue() || 'System' 
  }),
  columnHelper.accessor('description', { header: t('features.school.audit.labels.activity') }),
  columnHelper.accessor('subject_type', { 
    header: t('features.school.audit.labels.module'), 
    cell: info => info.getValue()?.split('\\').pop() || '-'
  }),
  columnHelper.display({
    id: 'actions',
    header: t('common.labels.status'),
    cell: ({ row }) => h(Button, {
      variant: 'ghost',
      size: 'sm',
      onClick: () => {
        selectedLog.value = row.original;
        dialogs.value.detail = true;
      }
    }, t('common.actions.view'))
  })
];

const table = useVueTable({
  get data() { return logs.value },
  get columns() { return columns },
  getCoreRowModel: getCoreRowModel(),
});

const fetchLogs = async () => {
  loading.value = true;
  try {
    const params = {
      ...filters.value,
      page: pagination.value?.current_page || 1
    };
    const response = await api.get('/admin/extensions/logs', { params });
    const res = parseResponse(response);
    logs.value = res.data;
    if (res.pagination && pagination.value) pagination.value.total = res.pagination.total || 0;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

watch([() => pagination.value.current_page, filters], fetchLogs, { deep: true });

onMounted(fetchLogs);
</script>
