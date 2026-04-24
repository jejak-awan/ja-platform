<template>
  <div>
    <div class="p-6 border-b flex justify-between items-center bg-card">
      <div class="flex gap-4 items-center">
        <Select
          v-model="filters.status"
          class="w-40"
        >
          <SelectTrigger><SelectValue :placeholder="$t('features.school.finance.placeholders.billStatus')" /></SelectTrigger>
          <SelectContent>
            <SelectItem value="all">
              {{ $t('common.labels.all') }}
            </SelectItem>
            <SelectItem value="unpaid">
              {{ $t('features.school.finance.labels.unpaid') }}
            </SelectItem>
            <SelectItem value="partially_paid">
              {{ $t('features.school.finance.labels.partiallyPaid') }}
            </SelectItem>
            <SelectItem value="paid">
              {{ $t('features.school.finance.labels.paid') }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>
    <DataTable
      :table="billsTable"
      :loading="loading"
    />

    <PaymentDialog
      v-model:open="dialogPayment"
      :bill="selectedBill"
      @save="fetchBills"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, h } from 'vue';
import { useI18n } from 'vue-i18n';
import { storeToRefs } from 'pinia';
import {
  Button, DataTable, Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/components/ui';
import { useFinanceStore } from '../../../../stores/finance';
import { createColumnHelper, useVueTable, getCoreRowModel } from '@tanstack/vue-table';
import PaymentDialog from '../components/PaymentDialog.vue';
import type { Bill } from '@/types';

const { t } = useI18n();
const financeStore = useFinanceStore();
const { loading, bills } = storeToRefs(financeStore);

const dialogPayment = ref(false);
const selectedBill = ref<Bill | null>(null);

const filters = ref({
  status: 'all',
  student_id: null as number | null
});

const columnHelper = createColumnHelper<Bill>();

const billColumns = [
  columnHelper.accessor((row: any) => row.student?.full_name, { id: 'student_name', header: t('common.labels.student') }),
  columnHelper.accessor((row: any) => row.fee_type?.name, { id: 'fee_type_name', header: t('features.school.finance.tabs.feeTypes') }),
  columnHelper.accessor('amount', { 
    header: t('common.labels.total'), 
    cell: info => new Intl.NumberFormat(t('common.language') === 'id' ? 'id-ID' : 'en-US', { style: 'currency', currency: 'IDR' }).format(Number(info.getValue())) 
  }),
  columnHelper.accessor('paid_amount', { 
    header: t('features.school.finance.labels.paidAmount'), 
    cell: info => new Intl.NumberFormat(t('common.language') === 'id' ? 'id-ID' : 'en-US', { style: 'currency', currency: 'IDR' }).format(Number(info.getValue())) 
  }),
  columnHelper.accessor('status', {
    header: t('common.labels.status'),
    cell: info => {
      const status = info.getValue() as string;
      const colors: Record<string, string> = {
        paid: 'bg-success/10 text-success',
        partially_paid: 'bg-warning/10 text-warning',
        unpaid: 'bg-destructive/10 text-destructive'
      };
      const labels: Record<string, string> = {
          paid: t('features.school.finance.labels.paid'),
          partially_paid: t('features.school.finance.labels.partiallyPaid'),
          unpaid: t('features.school.finance.labels.unpaid')
      };
      return h('span', { class: `px-2 py-1 rounded-full text-xs font-medium ${colors[status] || 'bg-muted text-muted-foreground'}` }, 
        labels[status] || status
      );
    }
  }),
  columnHelper.display({
    id: 'actions',
    header: t('common.labels.status'),
    cell: ({ row }) => h('div', { class: 'flex gap-2' }, [
      row.original.status !== 'paid' ? h(Button, {
        size: 'sm',
        variant: 'outline',
        onClick: () => {
          selectedBill.value = row.original;
          dialogPayment.value = true;
        }
      }, () => t('common.actions.pay')) : null
    ])
  })
];

const billsTable = useVueTable({
  get data() { return bills.value || [] },
  get columns() { return billColumns as any },
  getCoreRowModel: getCoreRowModel(),
});

const fetchBills = async () => {
  const params: any = { ...filters.value };
  if (params.status === 'all') delete params.status;
  await financeStore.fetchBills(params);
};

// Expose fetch method to be called from Index.vue on parent refresh
defineExpose({ fetchBills });

watch(filters, fetchBills, { deep: true });

onMounted(() => {
  fetchBills();
});
</script>
