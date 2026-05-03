<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-foreground">
          {{ $t('features.school.operations.visitors.title') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ $t('features.school.operations.visitors.subtitle') }}
        </p>
      </div>
      <Button @click="dialogs.checkIn = true">
        <LucideIcon
          name="UserPlus"
          class="w-4 h-4 mr-2"
        />
        {{ $t('features.school.operations.visitors.btnAdd') }}
      </Button>
    </div>

    <Card>
      <CardContent class="p-0">
        <div class="p-6 border-b flex justify-between items-center">
          <div class="flex gap-4 items-center">
            <Select
              v-model="filters.status"
              class="w-40"
            >
              <SelectTrigger><SelectValue :placeholder="$t('features.school.operations.visitors.labels.status')" /></SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ $t('features.school.operations.visitors.filters.all') }}
                </SelectItem>
                <SelectItem value="checked_in">
                  {{ $t('features.school.operations.visitors.filters.checked_in') }}
                </SelectItem>
                <SelectItem value="checked_out">
                  {{ $t('features.school.operations.visitors.filters.checked_out') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
        <DataTable
          :table="visitorsTable"
          :loading="loading"
        />
      </CardContent>
    </Card>

    <VisitorCheckInDialog 
      v-model:open="dialogs.checkIn" 
      :loading="loading"
      @submit="handleCheckIn" 
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  Card, CardContent, Button, LucideIcon, DataTable, Badge,
  Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/components/ui';
import { OperationsService } from '@/modules/School/services/OperationsService';
import { parseResponse } from '@/utils/responseParser';
import { useToast } from '@/composables/useToast';
import { createColumnHelper, useVueTable, getCoreRowModel } from '@tanstack/vue-table';
import VisitorCheckInDialog from './components/VisitorCheckInDialog.vue';

const { t } = useI18n();
const toast = useToast();
const loading = ref(false);
const visitors = ref<any[]>([]);
const dialogs = ref({ checkIn: false });
const filters = ref({ status: 'checked_in' });

const columnHelper = createColumnHelper<any>();

const visitorColumns = [
  columnHelper.accessor('name', { 
    header: t('features.school.operations.visitors.labels.visitorName'),
    cell: info => h('div', { class: 'flex items-center gap-3' }, [
      info.row.original.photo_path ? h('img', { 
        src: `/storage/${info.row.original.photo_path}`, 
        class: 'w-8 h-8 rounded-full object-cover border' 
      }) : h('div', { class: 'w-8 h-8 rounded-full bg-muted flex items-center justify-center' }, [
        h(LucideIcon, { name: 'User', class: 'w-4 h-4' })
      ]),
      h('div', [
        h('p', { class: 'font-bold' }, info.getValue()),
        h('p', { class: 'text-[10px] text-muted-foreground' }, info.row.original.institution || 'Personal')
      ])
    ])
  }),
  columnHelper.accessor('purpose', { header: t('features.school.operations.visitors.labels.purpose') }),
  columnHelper.accessor('target_person', { header: t('features.school.operations.visitors.labels.targetPerson') }),
  columnHelper.accessor('check_in', { 
    header: t('features.school.operations.visitors.labels.checkIn'),
    cell: info => new Date(info.getValue() as string).toLocaleString(t('common.language') === 'id' ? 'id-ID' : 'en-US', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: 'short' })
  }),
  columnHelper.accessor('status', {
    header: t('features.school.operations.visitors.labels.status'),
    cell: info => {
      const status = info.getValue() as string;
      return h(Badge, { variant: status === 'checked_in' ? 'warning' : 'success' }, 
        status === 'checked_in' ? t('features.school.operations.visitors.status.checked_in') : t('features.school.operations.visitors.status.checked_out')
      );
    }
  }),
  columnHelper.display({
    id: 'actions',
    header: t('common.labels.status'),
    cell: ({ row }) => h('div', { class: 'flex gap-2' }, [
      row.original.status === 'checked_in' ? h(Button, {
        size: 'sm',
        variant: 'outline',
        class: 'text-xs',
        onClick: () => handleCheckOut(row.original.id)
      }, t('features.school.operations.visitors.actions.checkOut')) : h('p', { class: 'text-[10px] text-muted-foreground italic' }, 
        `Out: ${new Date(row.original.check_out).toLocaleTimeString(t('common.language') === 'id' ? 'id-ID' : 'en-US', {hour:'2-digit', minute:'2-digit'})}`
      )
    ])
  })
];

const visitorsTable = useVueTable({
  get data() { return visitors.value },
  get columns() { return visitorColumns },
  getCoreRowModel: getCoreRowModel(),
});

const fetchVisitors = async () => {
    loading.value = true;
    try {
        const params: any = { ...filters.value };
        if (params.status === 'all') delete params.status;
        const response = await OperationsService.getVisitors(params);
        const result = parseResponse(response) as any;
        visitors.value = result.data?.data || [];
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        loading.value = false;
    }
}

const handleCheckIn = async (formData: FormData) => {
    loading.value = true;
    try {
        formData.append('school_id', '1');
        formData.append('school_unit_id', '1');
        await OperationsService.checkInVisitor(formData);
        toast.success.action(t('features.school.operations.visitors.messages.registerSuccess'));
        dialogs.value.checkIn = false;
        fetchVisitors();
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        loading.value = false;
    }
}

const handleCheckOut = async (id: number) => {
    if (confirm(t('features.school.operations.visitors.actions.checkOutConfirm'))) {
        loading.value = true;
        try {
            await OperationsService.checkOutVisitor(id);
            toast.success.action(t('features.school.operations.visitors.messages.checkOutSuccess'));
            fetchVisitors();
        } catch (e) {
            toast.error.fromResponse(e);
        } finally {
            loading.value = false;
        }
    }
}

watch(filters, fetchVisitors, { deep: true });

onMounted(fetchVisitors);
</script>
