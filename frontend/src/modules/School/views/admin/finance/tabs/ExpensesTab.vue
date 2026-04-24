<template>
  <div>
    <DataTable
      :table="expensesTable"
      :loading="loading"
    />

    <ExpenseDialog
      v-model:open="dialogExpense"
      :is-edit="!!selectedItem"
      :initial-data="selectedItem"
      :loading="loading"
      @submit="handleSaveExpense"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import { useI18n } from 'vue-i18n';
import { storeToRefs } from 'pinia';
import { Button, LucideIcon, DataTable } from '@/components/ui';
import { useFinanceStore } from '../../../../stores/finance';
import { useSchoolStore } from '../../../../stores/school';
import { useLevelStore } from '../../../../stores/level';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import { createColumnHelper, useVueTable, getCoreRowModel } from '@tanstack/vue-table';
import ExpenseDialog from '../components/ExpenseDialog.vue';
import type { Expense } from '@/types';

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();
const financeStore = useFinanceStore();
const schoolStore = useSchoolStore();
const levelStore = useLevelStore();
const { loading, expenses } = storeToRefs(financeStore);

const dialogExpense = ref(false);
const selectedItem = ref<Expense | null>(null);

const columnHelper = createColumnHelper<Expense>();

const expenseColumns = [
  columnHelper.accessor('category', { header: t('common.labels.category') }),
  columnHelper.accessor('description', { header: t('common.labels.description') }),
  columnHelper.accessor('amount', { 
    header: t('common.labels.amount'), 
    cell: info => new Intl.NumberFormat(t('common.language') === 'id' ? 'id-ID' : 'en-US', { style: 'currency', currency: 'IDR' }).format(Number(info.getValue())) 
  }),
  columnHelper.accessor('date', { 
    header: t('common.labels.date'),
    cell: info => new Date(info.getValue() as string).toLocaleDateString(t('common.language') === 'id' ? 'id-ID' : 'en-US')
  }),
  columnHelper.display({
    id: 'actions',
    header: t('common.labels.status'),
    cell: ({ row }) => h('div', { class: 'flex gap-2' }, [
      h(Button, {
        size: 'icon',
        variant: 'ghost',
        onClick: () => {
          selectedItem.value = row.original;
          dialogExpense.value = true;
        }
      }, () => h(LucideIcon, { name: 'Pencil', class: 'w-4 h-4' })),
      h(Button, {
        size: 'icon',
        variant: 'ghost',
        class: 'text-destructive',
        onClick: () => handleDeleteExpense(row.original)
      }, () => h(LucideIcon, { name: 'Trash2', class: 'w-4 h-4' }))
    ])
  })
];

const expensesTable = useVueTable({
  get data() { return expenses.value || [] },
  get columns() { return expenseColumns as any },
  getCoreRowModel: getCoreRowModel(),
});

const fetchExpenses = async () => {
  await financeStore.fetchExpenses();
};

const handleSaveExpense = async (formData: any) => {
    try {
        const data = selectedItem.value 
            ? { ...formData, id: selectedItem.value.id } 
            : { ...formData, school_id: schoolStore.currentSchool?.id, school_level_id: levelStore.activeLevelId };
        await financeStore.saveExpense(data, selectedItem.value?.id || null);
        toast.success.action(selectedItem.value ? t('features.school.academic.messages.updateSuccess') : t('features.school.academic.messages.addSuccess'));
        dialogExpense.value = false;
    } catch (e) {
        toast.error.fromResponse(e);
    }
};

const handleDeleteExpense = async (item: Expense) => {
    if (await confirm({ 
        title: t('common.actions.delete'), 
        description: `${t('common.actions.delete')} ${item.description}?`, 
        variant: 'destructive' 
    })) {
        try {
            await financeStore.deleteExpense(item.id);
            toast.success.action(t('features.school.academic.messages.deleteSuccess'));
        } catch (e) {
            toast.error.fromResponse(e);
        }
    }
};

const openAddDialog = () => {
  selectedItem.value = null;
  dialogExpense.value = true;
};
defineExpose({ fetchExpenses, openAddDialog });

onMounted(() => {
  fetchExpenses();
});
</script>
