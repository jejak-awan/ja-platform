<template>
  <div class="space-y-6 p-8 animate-in fade-in duration-700">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-2">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center border border-primary/20 shadow-sm">
            <LucideIcon
              name="Activity"
              class="w-6 h-6 text-primary"
            />
          </div>
          <h1 class="text-3xl font-bold tracking-tight text-foreground">
            {{ $t('modules.school.audit.title') }}
          </h1>
        </div>
        <p class="text-muted-foreground text-sm">
          {{ $t('modules.school.audit.subtitle') }}
        </p>
      </div>
    </div>

    <Card class="border border-border/40 bg-card shadow-sm rounded-2xl overflow-hidden">
      <CardContent class="p-0">
        <div class="p-6 border-b border-border/40 flex justify-between items-center bg-muted/20">
          <div class="flex gap-4">
            <div class="relative w-72">
              <LucideIcon
                name="Search"
                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"
              />
              <Input
                v-model="filters.search"
                :placeholder="$t('modules.school.audit.placeholders.search')"
                class="pl-10 h-11 rounded-xl bg-background border-border/40 focus:ring-primary/20 transition-all"
              />
            </div>
          </div>
          <Button
            variant="outline"
            class="rounded-xl border-border/40 h-11 px-6 font-bold hover:bg-primary/5 transition-colors"
            @click="fetchLogs"
          >
            <LucideIcon
              name="RefreshCcw"
              class="w-4 h-4 mr-2"
              :class="{ 'animate-spin': loading }"
            />
            {{ $t('common.actions.refresh') }}
          </Button>
        </div>

        <DataTable
          :table="table"
          :loading="loading"
        />
        
        <div class="p-6 border-t border-border/40 flex justify-between items-center bg-muted/10">
          <div class="flex flex-col">
            <p class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest leading-none">
               TOTAL ENTRIES
            </p>
            <p class="text-lg font-black text-foreground leading-none mt-1">
              {{ pagination?.total || 0 }}
            </p>
          </div>
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
          <DialogTitle>{{ $t('modules.school.audit.labels.details') }}</DialogTitle>
        </DialogHeader>
        <div
          v-if="selectedLog"
          class="space-y-4 py-4"
        >
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-muted-foreground">
                {{ $t('modules.school.audit.labels.causer') }}
              </p>
              <p class="font-medium">
                {{ selectedLog.causer?.name || 'System' }}
              </p>
            </div>
            <div>
              <p class="text-muted-foreground">
                {{ $t('modules.school.audit.labels.time') }}
              </p>
              <p class="font-medium">
                {{ new Date(selectedLog.created_at).toLocaleString($t('common.language') === 'id' ? 'id-ID' : 'en-US') }}
              </p>
            </div>
            <div class="col-span-2">
              <p class="text-muted-foreground">
                {{ $t('modules.school.audit.labels.description') }}
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
              {{ $t('modules.school.audit.labels.properties') }}:
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
} from '@/shared/components/ui';
import api from '@/engine/api/client';
import { parseResponse } from '@/shared/utils/responseParser';
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
    header: t('modules.school.audit.labels.time'), 
    cell: info => new Date(info.getValue()).toLocaleString(t('common.language') === 'id' ? 'id-ID' : 'en-US') 
  }),
  columnHelper.accessor('causer.name', { 
    header: t('modules.school.audit.labels.admin'), 
    cell: info => info.getValue() || 'System' 
  }),
  columnHelper.accessor('description', { header: t('modules.school.audit.labels.activity') }),
  columnHelper.accessor('subject_type', { 
    header: t('modules.school.audit.labels.module'), 
    cell: info => info.getValue()?.split('\\').pop() || '-'
  }),
  columnHelper.display({
    id: 'actions',
    header: t('common.labels.status'),
    cell: ({ row }) => h(Button, {
      variant: 'ghost',
      size: 'sm',
      class: 'rounded-xl h-8 text-muted-foreground hover:bg-primary/10 hover:text-primary font-bold',
      onClick: () => {
        selectedLog.value = row.original;
        dialogs.value.detail = true;
      }
    }, () => t('common.actions.view'))
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
