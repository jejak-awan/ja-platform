<template>
  <div class="pb-8">
    <div class="px-6 space-y-8 animate-in fade-in duration-700">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div>
          <div class="flex items-center gap-3 mb-1">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center border border-primary/20 shadow-sm shadow-primary/10 transition-transform hover:scale-105 duration-300">
              <LucideIcon
                name="Briefcase"
                class="w-6 h-6 text-primary"
              />
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-foreground">
              {{ $t('modules.school.hr.labels.advancedHR') }}
            </h1>
          </div>
          <p class="text-muted-foreground text-sm">
            {{ $t('modules.school.hr.subtitle') }}
          </p>
        </div>
      </div>

      <Card class="border border-border/40 bg-card shadow-none rounded-2xl overflow-hidden">
        <CardContent class="p-0">
          <Tabs
            v-model="activeTab"
            class="w-full"
          >
            <div class="p-4 border-b border-border/40 bg-muted/20">
              <TabsList class="p-1.5 bg-muted/50 rounded-2xl inline-flex h-auto gap-1 border border-border/40">
                <TabsTrigger 
                  value="leaves"
                  class="rounded-xl px-6 py-2 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40 font-bold transition-all"
                >
                  {{ $t('modules.school.hr.labels.leaveApplications') }}
                </TabsTrigger>
                <TabsTrigger 
                  value="shifts"
                  class="rounded-xl px-6 py-2 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40 font-bold transition-all"
                >
                  {{ $t('modules.school.hr.labels.shiftSettings') }}
                </TabsTrigger>
              </TabsList>
            </div>

            <TabsContent
              value="leaves"
              class="p-0 animate-in fade-in slide-in-from-bottom-2 duration-500"
            >
              <div class="p-6 border-b border-border/40 flex justify-between items-center bg-muted/10">
                <Select
                  v-model="filters.status"
                  class="w-40"
                >
                  <SelectTrigger class="rounded-xl bg-background border-border/50 h-10 min-w-[140px]">
                    <SelectValue :placeholder="$t('common.labels.status')" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">
                      {{ $t('common.labels.all') }}
                    </SelectItem>
                    <SelectItem value="pending">
                      {{ $t('common.labels.pending') }}
                    </SelectItem>
                    <SelectItem value="approved">
                      {{ $t('common.labels.approved') }}
                    </SelectItem>
                    <SelectItem value="rejected">
                      {{ $t('common.labels.rejected') }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <Button 
                  class="rounded-xl shadow-sm px-6 h-10 font-bold transition-all active:scale-95"
                  @click="dialogs.leave = true"
                >
                  <LucideIcon
                    name="Plus"
                    class="w-4 h-4 mr-2"
                  />
                  {{ $t('modules.school.hr.actions.inputLeave') }}
                </Button>
              </div>
              <DataTable
                :table="leavesTable"
                :loading="loading"
              />
            </TabsContent>

            <TabsContent
              value="shifts"
              class="p-0 animate-in fade-in slide-in-from-bottom-2 duration-500"
            >
              <div class="p-6 border-b border-border/40 flex justify-end bg-muted/10">
                <Button 
                  class="rounded-xl shadow-sm px-6 h-10 font-bold transition-all active:scale-95"
                  @click="handleAddShift"
                >
                  <LucideIcon
                    name="Plus"
                    class="w-4 h-4 mr-2"
                  />
                  {{ $t('modules.school.hr.actions.addShift') }}
                </Button>
              </div>
              <DataTable
                :table="shiftsTable"
                :loading="loading"
              />
            </TabsContent>
          </Tabs>
        </CardContent>
      </Card>

      <!-- Dialogs -->
      <ShiftFormDialog 
        v-model:open="dialogs.shift"
        :initial-data="selectedShift"
        @save="fetchShifts"
      />
      
      <LeaveFormDialog
        v-model:open="dialogs.leave"
        @save="fetchLeaves"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { storeToRefs } from 'pinia';
import {
  Card, CardContent, Button, LucideIcon, DataTable, Tabs, TabsList, TabsTrigger, TabsContent,
  Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/shared/components/ui';
import { useHRStore } from '../../../stores/hr';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { createColumnHelper, useVueTable, getCoreRowModel } from '@tanstack/vue-table';

import ShiftFormDialog from './components/ShiftFormDialog.vue';
import LeaveFormDialog from './components/LeaveFormDialog.vue';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const hrStore = useHRStore();
const { 
  loading, 
  leaves, 
  shifts 
} = storeToRefs(hrStore);

const activeTab = ref('leaves');
const selectedShift = ref<any>(null);

const dialogs = ref({
  shift: false,
  leave: false
});

const filters = ref({
  status: 'all'
});

const columnHelper = createColumnHelper<any>();

const leaveColumns = [
  columnHelper.accessor('staff.full_name', { 
    header: t('modules.school.hr.labels.staffName'),
    cell: info => h('span', { class: 'font-bold' }, info.getValue())
  }),
  columnHelper.accessor('type', { 
    header: t('modules.school.hr.labels.type'),
    cell: info => h('span', { class: 'text-xs font-medium' }, info.getValue())
  }),
  columnHelper.accessor('start_date', { 
    header: t('modules.school.hr.labels.from'), 
    cell: info => new Date(info.getValue()).toLocaleDateString(t('common.language') === 'id' ? 'id-ID' : 'en-US') 
  }),
  columnHelper.accessor('end_date', { 
    header: t('modules.school.hr.labels.to'), 
    cell: info => new Date(info.getValue()).toLocaleDateString(t('common.language') === 'id' ? 'id-ID' : 'en-US') 
  }),
  columnHelper.accessor('status', {
    header: t('common.labels.status'),
    cell: info => {
      const status = info.getValue() as string;
      const colors: Record<string, string> = {
        approved: 'bg-success/10 text-success border-success/20',
        rejected: 'bg-destructive/10 text-destructive border-destructive/20',
        pending: 'bg-warning/10 text-warning border-warning/20'
      };
      const labels: Record<string, string> = {
          approved: t('common.labels.approved'),
          rejected: t('common.labels.rejected'),
          pending: t('common.labels.pending')
      };
      return h('span', { class: `px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-[0.15em] border ${colors[status]}` }, 
        labels[status] || status.toUpperCase()
      );
    }
  }),
  columnHelper.display({
    id: 'actions',
    header: t('common.labels.action'),
    cell: ({ row }) => row.original.status === 'pending' ? h('div', { class: 'flex gap-2 justify-end px-2' }, [
      h(Button, {
        size: 'sm',
        variant: 'outline',
        class: 'rounded-xl h-8 text-success hover:bg-success/10 hover:text-success border-success/20 font-bold',
        onClick: () => handleUpdateLeave(row.original.id, 'approved')
      }, () => t('common.actions.approve')),
      h(Button, {
        size: 'sm',
        variant: 'outline',
        class: 'rounded-xl h-8 text-destructive hover:bg-destructive/10 hover:text-destructive border-destructive/20 font-bold',
        onClick: () => handleUpdateLeave(row.original.id, 'rejected')
      }, () => t('common.actions.reject'))
    ]) : null
  })
];

const shiftColumns = [
  columnHelper.accessor('name', { 
    header: t('modules.school.hr.labels.shiftSettings'),
    cell: info => h('span', { class: 'font-bold' }, info.getValue())
  }),
  columnHelper.accessor('start_time', { header: t('common.labels.start') }),
  columnHelper.accessor('end_time', { header: t('common.labels.end') }),
  columnHelper.display({
    id: 'actions',
    header: t('common.labels.action'),
    cell: ({ row }) => h('div', { class: 'flex gap-2 justify-end px-2' }, [
      h(Button, {
        size: 'icon',
        variant: 'ghost',
        class: 'h-8 w-8 text-muted-foreground hover:bg-primary/10 hover:text-primary rounded-lg',
        onClick: () => {
          selectedShift.value = row.original;
          dialogs.value.shift = true;
        }
      }, () => h(LucideIcon, { name: 'Edit2', class: 'w-4 h-4' })),
      h(Button, {
        size: 'icon',
        variant: 'ghost',
        class: 'h-8 w-8 text-muted-foreground hover:bg-destructive/10 hover:text-destructive rounded-lg',
        onClick: () => handleDeleteShift(row.original.id)
      }, () => h(LucideIcon, { name: 'Trash2', class: 'w-4 h-4' }))
    ])
  })
];

const leavesTable = useVueTable({
  get data() { return leaves.value || [] },
  get columns() { return leaveColumns },
  getCoreRowModel: getCoreRowModel(),
});

const shiftsTable = useVueTable({
  get data() { return shifts.value || [] },
  get columns() { return shiftColumns },
  getCoreRowModel: getCoreRowModel(),
});

const fetchLeaves = async () => {
  const params: any = { ...filters.value };
  if (params.status === 'all') delete params.status;
  await hrStore.fetchLeaves(params);
};

const fetchShifts = async () => {
  await hrStore.fetchShifts();
};

const handleUpdateLeave = async (id: number, status: string) => {
  try {
    await hrStore.updateLeaveStatus(id, status);
    toast.success.action(t('modules.school.academic.messages.updateSuccess'));
    fetchLeaves();
  } catch (e) {
    toast.error.fromResponse(e);
  }
};

const handleAddShift = () => {
  selectedShift.value = null;
  dialogs.value.shift = true;
};

const handleDeleteShift = async (id: number) => {
  if (await confirm({ 
      title: t('common.actions.delete'), 
      description: t('modules.school.academic.messages.deleteConfirm'), 
      variant: 'destructive' 
  })) {
    try {
      await hrStore.deleteShift(id);
      toast.success.action(t('modules.school.academic.messages.deleteSuccess'));
      fetchShifts();
    } catch (e) {
      toast.error.fromResponse(e);
    }
  }
};

watch(activeTab, (val) => {
  if (val === 'leaves') fetchLeaves();
  if (val === 'shifts') fetchShifts();
});

watch(filters, fetchLeaves, { deep: true });

onMounted(() => {
  fetchLeaves();
});
</script>
