<template>
  <div>
    <DataTable
      :table="budgetsTable"
      :loading="loading"
    />

    <BudgetDialog
      v-model:open="dialogBudget"
      :loading="loading"
      @submit="handleSaveBudget"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import { useI18n } from 'vue-i18n';
import { storeToRefs } from 'pinia';
import { DataTable, LucideIcon } from '@/components/ui';
import { useFinanceStore } from '../../../../stores/finance';
import { useSchoolStore } from '../../../../stores/school';
import { useLevelStore } from '../../../../stores/level';
import { useToast } from '@/composables/useToast';
import { createColumnHelper, useVueTable, getCoreRowModel } from '@tanstack/vue-table';
import BudgetDialog from '../components/BudgetDialog.vue';
import type { Budget } from '@/types';

const { t } = useI18n();
const toast = useToast();
const financeStore = useFinanceStore();
const schoolStore = useSchoolStore();
const levelStore = useLevelStore();
const { loading, budgets } = storeToRefs(financeStore);

const dialogBudget = ref(false);

const columnHelper = createColumnHelper<Budget>();

const budgetColumns = [
  columnHelper.accessor((row: any) => row.academic_year?.year, { id: 'academic_year', header: t('features.school.academic.tabs.years') }),
  columnHelper.accessor('category', { header: t('common.labels.category') }),
  columnHelper.accessor('planned_amount', { 
    header: t('features.school.finance.labels.plannedBudget'), 
    cell: info => new Intl.NumberFormat(t('common.language') === 'id' ? 'id-ID' : 'en-US', { style: 'currency', currency: 'IDR' }).format(Number(info.getValue())) 
  }),
  columnHelper.accessor('actual_amount', { 
    header: t('features.school.finance.labels.actualBudget'), 
    cell: info => {
      const actual = Number(info.getValue()) || 0;
      const planned = Number(info.row.original.planned_amount);
      const isWarning = info.row.original.is_warning;
      const percentage = planned > 0 ? (actual / planned) * 100 : 0;
      
      let color = 'text-foreground';
      if (isWarning || percentage > 90) color = 'text-warning';
      if (percentage > 100) color = 'text-destructive';
      
      return h('div', { class: 'space-y-1' }, [
        h('div', { class: 'flex items-center gap-1' }, [
            h('div', { class: `font-bold ${color}` }, new Intl.NumberFormat(t('common.language') === 'id' ? 'id-ID' : 'en-US', { style: 'currency', currency: 'IDR' }).format(actual)),
            isWarning ? h(LucideIcon, { name: 'AlertTriangle', class: 'w-3 h-3 text-warning' }) : null
        ]),
        h('div', { class: 'text-[10px] text-muted-foreground' }, `${percentage.toFixed(1)}% ${t('features.school.finance.labels.ofBudget')}`)
      ]);
    }
  }),
];

const budgetsTable = useVueTable({
    get data() { return budgets.value || [] },
    get columns() { return budgetColumns as any },
    getCoreRowModel: getCoreRowModel(),
});

const fetchBudgets = async () => {
    await financeStore.fetchBudgets();
}

const handleSaveBudget = async (formData: any) => {
    try {
        await financeStore.saveBudget({ ...formData, school_id: schoolStore.currentSchool?.id, school_level_id: levelStore.activeLevelId });
        toast.success.action(t('features.school.academic.messages.addSuccess'));
        dialogBudget.value = false;
    } catch (e) {
        toast.error.fromResponse(e);
    }
}

const openAddDialog = () => {
  dialogBudget.value = true;
};
defineExpose({ fetchBudgets, openAddDialog });

onMounted(() => {
  fetchBudgets();
});
</script>
