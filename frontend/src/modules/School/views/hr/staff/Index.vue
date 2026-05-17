<template>
  <div class="p-6 space-y-8 animate-in fade-in duration-700">
    <BreadcrumbTrail />
    
    <!-- Premium Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
      <div class="space-y-1">
        <h1 class="text-3xl font-black tracking-tight text-foreground uppercase">
          {{ $t('modules.school.hr.staff.title') }}
        </h1>
        <div class="flex items-center gap-2">
          <Badge variant="secondary" class="bg-primary/10 text-primary border-primary/20 shrink-0 font-bold">
            {{ pagination?.total || 0 }} {{ $t('modules.school.hr.staff.tabs.staff') }}
          </Badge>
          <p class="text-sm text-muted-foreground italic font-medium">
            {{ $t('modules.school.hr.staff.subtitle') }}
          </p>
        </div>
      </div>
      
      <div class="flex flex-wrap gap-3">
        <!-- Removed Generate Payroll Button (BOS Compliance) -->

        <Button
          variant="outline"
          size="lg"
          class="rounded-xl border-border/50 hover:bg-accent/50 transition-colors h-11 px-6 font-bold"
          :disabled="loading || staff.length === 0"
          @click="handleExport"
        >
          <LucideIcon name="Download" class="w-5 h-5 mr-2" />
          {{ $t('modules.school.students.actions.export') }}
        </Button>

        <router-link :to="{ name: 'staff.create' }">
          <Button size="lg" class="rounded-xl shadow-sm hover:shadow-md transition-all active:scale-95 font-bold h-11 px-8">
            <LucideIcon name="UserPlus" class="w-5 h-5 mr-2" />
            {{ $t('modules.school.hr.staff.btnAdd') }}
          </Button>
        </router-link>
      </div>
    </div>

    <!-- Main Content Area with Animated Tabs -->
    <Tabs v-model="activeTab" class="w-full space-y-6">
      <div class="flex items-center justify-between gap-4">
        <TabsList class="bg-muted/50 p-1.5 rounded-2xl border border-border/40 inline-flex h-auto gap-1">
          <TabsTrigger 
            value="staff" 
            class="rounded-xl data-[state=active]:bg-background data-[state=active]:shadow-sm px-6 py-2.5 transition-all font-bold"
          >
            <LucideIcon name="Users" class="w-4 h-4 mr-2" />
            {{ $t('modules.school.hr.staff.tabs.staff') }}
          </TabsTrigger>
          <!-- Salary and Payroll tabs removed for BOS compliance -->
        </TabsList>

        <div v-if="activeTab === 'staff'" class="hidden lg:flex items-center gap-2 max-w-sm w-full">
           <Input 
             v-model="searchQuery" 
             :placeholder="$t('common.labels.search')" 
             class="bg-background/50 border-border/50 focus:ring-primary/20 rounded-xl h-11"
             @input="handleSearch"
           >
             <template #prefix>
               <LucideIcon name="Search" class="w-4 h-4 text-muted-foreground" />
             </template>
           </Input>
        </div>
      </div>

      <Card class="overflow-hidden border-border/50 bg-background/50 backdrop-blur-sm shadow-sm rounded-2xl">
        <CardContent class="p-0">
          <DataTable
            :table="table"
            :loading="loading"
            class="border-none"
          />
          
          <div class="p-6 bg-muted/20 border-t border-border/50 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
              <div class="h-8 w-1 bg-primary rounded-full"></div>
              <p class="text-xs font-black text-muted-foreground uppercase tracking-widest">
                {{ $t('modules.school.hr.staff.labels.total', { total: pagination?.total || 0 }) }}
              </p>
              <span class="text-xs text-muted-foreground opacity-30">|</span>
              <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-tighter">{{ $t('common.labels.showing_page', { current: pagination?.current_page || 1 }) }}</span>
            </div>
            
            <Pagination
              v-if="pagination && (pagination.total || 0) > (pagination.per_page || 0)"
              :total-items="pagination.total || 0"
              :per-page="pagination.per_page || 20"
              :current-page="pagination.current_page || 1"
              @page-change="handlePageChange"
              class="w-full sm:w-auto"
            />
          </div>
        </CardContent>
      </Card>
    </Tabs>
  </div>
</template>

<script setup lang="ts">

import { ref, onMounted, h, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { HRService } from '@/modules/School/services/HRService';
import {
  Card, CardContent, Button, LucideIcon, DataTable, Pagination, Badge, Input, Tabs, TabsList, TabsTrigger
} from '@/shared/components/ui';
import BreadcrumbTrail from '@/shared/components/BreadcrumbTrail.vue';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { 
  useVueTable, 
  getCoreRowModel, 
  createColumnHelper,
} from '@tanstack/vue-table';
import { useRouter } from 'vue-router';
import { exportToCSV } from '@/shared/utils/exportUtils';
import { parseResponse } from '@/shared/utils/responseParser';
import _ from 'lodash';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const router = useRouter();

const staff = ref<any[]>([]);
const activeTab = ref('staff');
const loading = ref(false);
const searchQuery = ref('');
const pagination = ref({ total: 0, per_page: 20, current_page: 1 });

const handleExport = () => {
  exportToCSV(staff.value, `data-staff-export-${new Date().toISOString().split('T')[0]}`);
  toast.success.action(t('modules.school.hr.staff.messages.exportSuccess'));
};

const columnHelper = createColumnHelper<any>();

const staffColumns = [
  columnHelper.accessor('full_name', { 
    header: t('modules.school.hr.staff.labels.fullName'),
    cell: ({ row }) => h('div', { class: 'flex items-center gap-3' }, [
      h('div', { class: 'w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold border border-primary/20' }, 
        row.original.full_name.charAt(0)
      ),
      h('div', { class: 'flex flex-col' }, [
        h('span', { class: 'font-semibold text-foreground' }, row.original.full_name),
        h('span', { class: 'text-[10px] text-muted-foreground uppercase tracking-widest' }, row.original.nuptk || 'No NUPTK')
      ])
    ])
  }),
  columnHelper.accessor('ptk_type', { 
    header: t('modules.school.hr.staff.labels.ptkType'),
    cell: info => h(Badge, { variant: 'outline', class: 'bg-accent/30 font-normal border-border/50 transition-colors' }, () => info.getValue())
  }),
  columnHelper.accessor('employment_status', { 
    header: t('modules.school.hr.staff.labels.employmentStatus'),
    cell: info => h('span', { class: 'text-sm text-foreground/80' }, info.getValue())
  }),
  columnHelper.accessor('certification_status', { 
    header: t('modules.school.hr.staff.labels.certificationStatus'),
    cell: ({ row }) => h('div', { class: 'flex items-center justify-center' }, [
      row.original.certification_status 
        ? h(LucideIcon, { name: 'BadgeCheck', class: 'w-5 h-5 text-success' })
        : h(LucideIcon, { name: 'XCircle', class: 'w-5 h-5 text-muted-foreground/30' })
    ])
  }),
  columnHelper.display({
    id: 'actions',
    header: '',
    cell: ({ row }) => h('div', { class: 'flex justify-end gap-1 px-2' }, [
      h(Button, {
        variant: 'ghost', size: 'icon', class: 'h-8 w-8 hover:bg-primary/10 hover:text-primary transition-colors rounded-lg',
        onClick: () => router.push({ name: 'staff.show', params: { id: row.original.id } })
      }, () => h(LucideIcon, { name: 'Eye', class: 'w-4 h-4' })),
      h(Button, {
        variant: 'ghost', size: 'icon', class: 'h-8 w-8 hover:bg-primary/10 hover:text-primary transition-colors rounded-lg',
        onClick: () => router.push({ name: 'staff.edit', params: { id: row.original.id } })
      }, () => h(LucideIcon, { name: 'Edit2', class: 'w-4 h-4' })),
      h(Button, {
        variant: 'ghost', size: 'icon', class: 'h-8 w-8 hover:bg-destructive/10 hover:text-destructive transition-colors rounded-lg',
        onClick: () => handleDelete(row.original.id)
      }, () => h(LucideIcon, { name: 'Trash2', class: 'w-4 h-4' }))
    ]),
  }),
];

/* Payroll columns removed */

const table = useVueTable({
  get data() { 
      return staff.value;
  },
  get columns() {
      return staffColumns;
  },
  getCoreRowModel: getCoreRowModel(),
});

const fetchStaff = async (page = 1) => {
  loading.value = true;
  try {
    const response = await HRService.getStaff({ page, search: searchQuery.value });
    const { data, pagination: pagin } = parseResponse(response);
    staff.value = data;
    if (pagin) {
      pagination.value = {
        total: pagin.total || 0,
        per_page: pagin.per_page || 20,
        current_page: pagin.current_page || 1,
      };
    }
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};

const handleSearch = _.debounce(() => {
    fetchStaff(1);
}, 500);

const handlePageChange = (page: number) => {
  fetchStaff(page);
};

const handleDelete = async (id: string) => {
  const isConfirmed = await confirm({
    title: t('modules.school.hr.staff.messages.deleteConfirmTitle'),
    message: t('modules.school.hr.staff.messages.deleteConfirmMessage'),
    variant: 'destructive',
  });

  if (isConfirmed) {
    try {
      await HRService.deleteStaff(id);
      toast.success.action(t('modules.school.hr.staff.messages.deleteSuccess'));
      fetchStaff(pagination.value.current_page);
    } catch (e) {
      toast.error.fromResponse(e);
    }
  }
};

/* Payroll and Salary methods removed for BOS compliance */

watch(activeTab, () => {
    fetchStaff();
});

onMounted(() => {
  fetchStaff();
});
</script>

<style scoped>
/* Removed redundant scrollbar styles */
</style>
