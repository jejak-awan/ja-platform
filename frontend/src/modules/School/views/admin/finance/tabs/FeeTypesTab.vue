<template>
  <div>
    <DataTable
      :table="feeTypesTable"
      :loading="loading"
    />

    <FeeTypeFormDialog 
      v-model:open="dialogFeeType" 
      :initial-data="selectedItem" 
      @save="fetchFeeTypes" 
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import { useI18n } from 'vue-i18n';
import { storeToRefs } from 'pinia';
import { Button, LucideIcon, DataTable } from '@/components/ui';
import { useFinanceStore } from '../../../../stores/finance';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import { createColumnHelper, useVueTable, getCoreRowModel } from '@tanstack/vue-table';
import FeeTypeFormDialog from '../components/FeeTypeFormDialog.vue';
import type { FeeType } from '@/types';

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();
const financeStore = useFinanceStore();
const { loading, feeTypes } = storeToRefs(financeStore);

const dialogFeeType = ref(false);
const selectedItem = ref<FeeType | null>(null);

const columnHelper = createColumnHelper<FeeType>();

const feeTypeColumns = [
  columnHelper.accessor('name', { header: t('features.school.finance.labels.feeName') }),
  columnHelper.accessor('amount', { 
    header: t('features.school.finance.labels.defaultAmount'), 
    cell: info => new Intl.NumberFormat(t('common.language') === 'id' ? 'id-ID' : 'en-US', { style: 'currency', currency: 'IDR' }).format(Number(info.getValue())) 
  }),
  columnHelper.accessor('period', { header: t('features.school.finance.labels.period') }),
  columnHelper.display({
    id: 'actions',
    header: t('common.labels.status'),
    cell: ({ row }) => h('div', { class: 'flex gap-2' }, [
      h(Button, {
        size: 'icon',
        variant: 'ghost',
        onClick: () => {
          selectedItem.value = row.original;
          dialogFeeType.value = true;
        }
      }, () => h(LucideIcon, { name: 'Pencil', class: 'w-4 h-4' })),
      h(Button, {
        size: 'icon',
        variant: 'ghost',
        class: 'text-destructive',
        onClick: () => handleDeleteFeeType(row.original)
      }, () => h(LucideIcon, { name: 'Trash2', class: 'w-4 h-4' }))
    ])
  })
];

const feeTypesTable = useVueTable({
  get data() { return feeTypes.value || [] },
  get columns() { return feeTypeColumns as any },
  getCoreRowModel: getCoreRowModel(),
});

const fetchFeeTypes = async () => {
  await financeStore.fetchFeeTypes();
};

const handleDeleteFeeType = async (item: FeeType) => {
  if (await confirm({ 
      title: t('common.actions.delete'), 
      description: `${t('common.actions.delete')} ${item.name}?`, 
      variant: 'destructive' 
  })) {
    try {
      await financeStore.deleteFeeType(item.id);
      toast.success.action(t('features.school.academic.messages.deleteSuccess'));
    } catch (e) {
      toast.error.fromResponse(e);
    }
  }
};

// Expose methods for parent invocation (e.g. from the 'Add' action in the Index.vue header)
const openAddDialog = () => {
  selectedItem.value = null;
  dialogFeeType.value = true;
};
defineExpose({ fetchFeeTypes, openAddDialog });

onMounted(() => {
  fetchFeeTypes();
});
</script>
