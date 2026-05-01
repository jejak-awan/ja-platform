<template>
  <div class="p-6 space-y-8 animate-in fade-in duration-700">
    <Breadcrumbs />
    
    <!-- Premium Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
      <div class="space-y-1">
        <h1 class="text-3xl font-black tracking-tight text-foreground uppercase">
          {{ $t('features.school.hr.staff.title') }}
        </h1>
        <div class="flex items-center gap-2">
          <Badge variant="secondary" class="bg-primary/10 text-primary border-primary/20 shrink-0 font-bold">
            {{ pagination?.total || 0 }} {{ $t('features.school.hr.staff.tabs.staff') }}
          </Badge>
          <p class="text-sm text-muted-foreground italic font-medium">
            {{ $t('features.school.hr.staff.subtitle') }}
          </p>
        </div>
      </div>
      
      <div class="flex flex-wrap gap-3">
        <Button
          v-if="activeTab === 'payroll'"
          size="lg"
          class="rounded-xl shadow-sm hover:shadow-md transition-all active:scale-95 font-bold h-11 px-6"
          @click="handleGeneratePayroll"
        >
          <LucideIcon name="Coins" class="w-5 h-5 mr-2" />
          {{ $t('features.school.hr.staff.btnGeneratePayroll') }}
        </Button>

        <Button
          variant="outline"
          size="lg"
          class="rounded-xl border-border/50 hover:bg-accent/50 transition-colors h-11 px-6 font-bold"
          :disabled="loading || staff.length === 0"
          @click="handleExport"
        >
          <LucideIcon name="Download" class="w-5 h-5 mr-2" />
          {{ $t('features.school.students.actions.export') }}
        </Button>

        <router-link :to="{ name: 'staff.create' }">
          <Button size="lg" class="rounded-xl shadow-sm hover:shadow-md transition-all active:scale-95 font-bold h-11 px-8">
            <LucideIcon name="UserPlus" class="w-5 h-5 mr-2" />
            {{ $t('features.school.hr.staff.btnAdd') }}
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
            {{ $t('features.school.hr.staff.tabs.staff') }}
          </TabsTrigger>
          <TabsTrigger 
            value="salary" 
            class="rounded-xl data-[state=active]:bg-background data-[state=active]:shadow-sm px-6 py-2.5 transition-all font-bold"
          >
            <LucideIcon name="Wallet" class="w-4 h-4 mr-2" />
            {{ $t('features.school.hr.staff.tabs.salary') }}
          </TabsTrigger>
          <TabsTrigger 
            value="payroll" 
            class="rounded-xl data-[state=active]:bg-background data-[state=active]:shadow-sm px-6 py-2.5 transition-all font-bold"
          >
            <LucideIcon name="FileDigit" class="w-4 h-4 mr-2" />
            {{ $t('features.school.hr.staff.tabs.payroll') }}
          </TabsTrigger>
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
                {{ $t('features.school.hr.staff.labels.total', { total: pagination?.total || 0 }) }}
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

    <!-- Dialogs -->
    <SalaryStructureDialog 
      v-model:open="dialogs.salary"
      :staff-name="selectedStaff?.full_name || ''"
      :initial-data="selectedSalaryStructure"
      :loading="saving"
      @submit="handleSaveSalaryStructure"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { HRService } from '@/modules/School/services/HRService';
import {
  Card, CardContent, Button, LucideIcon, DataTable, Pagination, Tabs, TabsList, TabsTrigger, Badge, Input
} from '@/components/ui';
import Breadcrumbs from '@/modules/Core/components/layout/Breadcrumbs.vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { 
  useVueTable, 
  getCoreRowModel, 
  createColumnHelper,
} from '@tanstack/vue-table';
import { useRouter } from 'vue-router';
import { exportToCSV } from '@/utils/exportUtils';
import SalaryStructureDialog from './components/SalaryStructureDialog.vue';
import { parseResponse } from '@/utils/responseParser';
import _ from 'lodash';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const router = useRouter();

const staff = ref<any[]>([]);
const payrolls = ref<any[]>([]);
const activeTab = ref('staff');
const loading = ref(false);
const saving = ref(false);
const searchQuery = ref('');
const selectedStaff = ref<any>(null);
const selectedSalaryStructure = ref<any>(null);
const dialogs = ref({ salary: false });
const pagination = ref({ total: 0, per_page: 20, current_page: 1 });

const handleExport = () => {
  exportToCSV(staff.value, `data-staff-export-${new Date().toISOString().split('T')[0]}`);
  toast.success.action(t('features.school.hr.staff.messages.exportSuccess'));
};

const columnHelper = createColumnHelper<any>();

const staffColumns = [
  columnHelper.accessor('full_name', { 
    header: t('features.school.hr.staff.labels.fullName'),
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
    header: t('features.school.hr.staff.labels.ptkType'),
    cell: info => h(Badge, { variant: 'outline', class: 'bg-accent/30 font-normal border-border/50 transition-colors' }, () => info.getValue())
  }),
  columnHelper.accessor('employment_status', { 
    header: t('features.school.hr.staff.labels.employmentStatus'),
    cell: info => h('span', { class: 'text-sm text-foreground/80' }, info.getValue())
  }),
  columnHelper.accessor('certification_status', { 
    header: t('features.school.hr.staff.labels.certificationStatus'),
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

const salaryColumns = [
    columnHelper.accessor('full_name', { 
        header: t('features.school.hr.staff.labels.fullName'),
        cell: ({ row }) => h('span', { class: 'font-semibold' }, row.original.full_name)
    }),
    columnHelper.accessor('ptk_type', { header: t('features.school.hr.staff.labels.ptkType') }),
    columnHelper.display({
        id: 'actions',
        header: t('features.school.hr.staff.labels.salarySetting'),
        cell: ({ row }) => h(Button, {
            size: 'sm',
            variant: 'secondary',
            class: 'bg-primary/5 text-primary hover:bg-primary/10 transition-colors rounded-lg font-bold',
            onClick: () => handleSetupSalary(row.original)
        }, () => [
            h(LucideIcon, { name: 'Settings2', class: 'w-3 h-3 mr-2' }),
            t('features.school.hr.staff.actions.setupSalary')
        ])
    })
];

const payrollColumns = [
    columnHelper.accessor('staff.full_name', { 
        header: t('features.school.hr.staff.labels.fullName'),
        cell: info => h('span', { class: 'font-semibold' }, info.getValue())
    }),
    columnHelper.accessor('period', { header: t('features.school.hr.staff.labels.period') }),
    columnHelper.accessor('net_salary', { 
        header: t('features.school.hr.staff.labels.netSalary'),
        cell: info => h('span', { class: 'font-mono text-success font-bold' }, 
            new Intl.NumberFormat(t('common.language') === 'id' ? 'id-ID' : 'en-US', { 
                style: 'currency', currency: 'IDR' 
            }).format(info.getValue())
        )
    }),
    columnHelper.accessor('status', { 
        header: t('common.labels.status'),
        cell: info => h(Badge, { 
            variant: info.getValue() === 'Paid' ? 'success' : 'warning',
            class: 'capitalize font-bold rounded-lg' 
        }, () => info.getValue())
    }),
];

const table = useVueTable({
  get data() { 
      if (activeTab.value === 'staff') return staff.value;
      if (activeTab.value === 'salary') return staff.value;
      return payrolls.value;
  },
  get columns() {
      if (activeTab.value === 'staff') return staffColumns;
      if (activeTab.value === 'salary') return salaryColumns;
      return payrollColumns;
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

const handleDelete = async (id: number) => {
  const isConfirmed = await confirm({
    title: t('features.school.hr.staff.messages.deleteConfirmTitle'),
    message: t('features.school.hr.staff.messages.deleteConfirmMessage'),
    variant: 'destructive',
  });

  if (isConfirmed) {
    try {
      await HRService.deleteStaff(id);
      toast.success.action(t('features.school.hr.staff.messages.deleteSuccess'));
      fetchStaff(pagination.value.current_page);
    } catch (e) {
      toast.error.fromResponse(e);
    }
  }
};

const fetchPayroll = async (page = 1) => {
    loading.value = true;
    try {
        const response = await HRService.getPayrolls({ page });
        const res = parseResponse(response);
        payrolls.value = res.data;
        if (res.pagination) {
            pagination.value = {
                total: res.pagination.total,
                per_page: res.pagination.per_page || 20,
                current_page: res.pagination.current_page,
            };
        }
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        loading.value = false;
    }
}

const handleSetupSalary = async (member: any) => {
    selectedStaff.value = member;
    saving.value = true;
    try {
        const response = await HRService.getSalaryStructures(member.id);
        const res = parseResponse(response);
        selectedSalaryStructure.value = res.data.length > 0 ? res.data[0] : null;
        dialogs.value.salary = true;
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        saving.value = false;
    }
}

const handleSaveSalaryStructure = async (formData: any) => {
    saving.value = true;
    try {
        await HRService.storeSalaryStructure({ 
            ...formData, 
            staff_id: selectedStaff.value.id,
            school_id: 15,
            school_level_id: 1,
        });
        toast.success.action(t('features.school.hr.staff.messages.saveSalarySuccess'));
        dialogs.value.salary = false;
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        saving.value = false;
    }
}

const handleGeneratePayroll = async () => {
    const period = new Date().toISOString().slice(0, 7);
    if (await confirm({ title: t('features.school.hr.staff.btnGeneratePayroll'), message: t('features.school.hr.staff.messages.generatePayrollConfirm', { period }), variant: 'info' })) {
        loading.value = true;
        try {
            await HRService.generatePayroll({ 
                period, school_id: 15, school_level_id: 1 
            });
            toast.success.action(t('features.school.hr.staff.messages.generatePayrollSuccess'));
            if (activeTab.value === 'payroll') fetchPayroll();
        } catch (e) {
            toast.error.fromResponse(e);
        } finally {
            loading.value = false;
        }
    }
}

watch(activeTab, (val) => {
    if (val === 'staff' || val === 'salary') fetchStaff();
    if (val === 'payroll') fetchPayroll();
});

onMounted(() => {
  fetchStaff();
});
</script>

<style scoped>
/* Removed redundant scrollbar styles */
</style>
