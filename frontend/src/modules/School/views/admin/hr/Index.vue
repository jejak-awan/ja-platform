<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center text-left">
      <div>
        <h1 class="text-2xl font-bold text-foreground">
          {{ $t('features.school.hr.labels.advancedHR') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ $t('features.school.hr.subtitle') }}
        </p>
      </div>
    </div>

    <Card>
      <CardContent class="p-0">
        <Tabs
          v-model="activeTab"
          class="w-full"
        >
          <TabsList class="p-6 border-b bg-muted/20 flex justify-start h-auto gap-4">
            <TabsTrigger value="leaves">
              {{ $t('features.school.hr.labels.leaveApplications') }}
            </TabsTrigger>
            <TabsTrigger value="shifts">
              {{ $t('features.school.hr.labels.shiftSettings') }}
            </TabsTrigger>
          </TabsList>

          <TabsContent
            value="leaves"
            class="p-0"
          >
            <div class="p-6 border-b flex justify-between items-center">
              <Select
                v-model="filters.status"
                class="w-40"
              >
                <SelectTrigger><SelectValue :placeholder="$t('common.labels.status')" /></SelectTrigger>
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
              <Button @click="dialogs.leave = true">
                <LucideIcon
                  name="Plus"
                  class="w-4 h-4 mr-2"
                />
                {{ $t('features.school.hr.actions.inputLeave') }}
              </Button>
            </div>
            <DataTable
              :table="leavesTable"
              :loading="loading"
            />
          </TabsContent>

          <TabsContent
            value="shifts"
            class="p-0"
          >
            <div class="p-6 border-b flex justify-end">
              <Button @click="handleAddShift">
                <LucideIcon
                  name="Plus"
                  class="w-4 h-4 mr-2"
                />
                {{ $t('features.school.hr.actions.addShift') }}
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
</template>

<script setup lang="ts">
import { ref, onMounted, h, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { storeToRefs } from 'pinia';
import {
  Card, CardContent, Button, LucideIcon, DataTable, Tabs, TabsList, TabsTrigger, TabsContent,
  Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/components/ui';
import { useHRStore } from '../../../stores/hr';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
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
  columnHelper.accessor('staff.full_name', { header: t('features.school.hr.labels.staffName') }),
  columnHelper.accessor('type', { header: t('features.school.hr.labels.type') }),
  columnHelper.accessor('start_date', { 
    header: t('features.school.hr.labels.from'), 
    cell: info => new Date(info.getValue()).toLocaleDateString(t('common.language') === 'id' ? 'id-ID' : 'en-US') 
  }),
  columnHelper.accessor('end_date', { 
    header: t('features.school.hr.labels.to'), 
    cell: info => new Date(info.getValue()).toLocaleDateString(t('common.language') === 'id' ? 'id-ID' : 'en-US') 
  }),
  columnHelper.accessor('status', {
    header: t('common.labels.status'),
    cell: info => {
      const status = info.getValue() as string;
      const colors: Record<string, string> = {
        approved: 'bg-success/10 text-success',
        rejected: 'bg-destructive/10 text-destructive',
        pending: 'bg-warning/10 text-warning'
      };
      const labels: Record<string, string> = {
          approved: t('common.labels.approved'),
          rejected: t('common.labels.rejected'),
          pending: t('common.labels.pending')
      };
      return h('span', { class: `px-2 py-1 rounded-full text-xs font-medium ${colors[status]}` }, 
        labels[status] || status.toUpperCase()
      );
    }
  }),
  columnHelper.display({
    id: 'actions',
    header: t('common.labels.actions'),
    cell: ({ row }) => row.original.status === 'pending' ? h('div', { class: 'flex gap-2' }, [
      h(Button, {
        size: 'sm',
        variant: 'outline',
        class: 'text-success',
        onClick: () => handleUpdateLeave(row.original.id, 'approved')
      }, t('common.actions.approve')),
      h(Button, {
        size: 'sm',
        variant: 'outline',
        class: 'text-destructive',
        onClick: () => handleUpdateLeave(row.original.id, 'rejected')
      }, t('common.actions.reject'))
    ]) : null
  })
];

const shiftColumns = [
  columnHelper.accessor('name', { header: t('features.school.hr.labels.shiftSettings') }),
  columnHelper.accessor('start_time', { header: t('common.labels.start') }),
  columnHelper.accessor('end_time', { header: t('common.labels.end') }),
  columnHelper.display({
    id: 'actions',
    header: t('common.labels.actions'),
    cell: ({ row }) => h('div', { class: 'flex gap-2' }, [
      h(Button, {
        size: 'icon',
        variant: 'ghost',
        onClick: () => {
          selectedShift.value = row.original;
          dialogs.value.shift = true;
        }
      }, () => h(LucideIcon, { name: 'Pencil', class: 'w-4 h-4' })),
      h(Button, {
        size: 'icon',
        variant: 'ghost',
        class: 'text-destructive',
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
    toast.success.action(t('features.school.academic.messages.updateSuccess'));
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
      description: t('features.school.academic.messages.deleteConfirm'), 
      variant: 'destructive' 
  })) {
    try {
      await hrStore.deleteShift(id);
      toast.success.action(t('features.school.academic.messages.deleteSuccess'));
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
