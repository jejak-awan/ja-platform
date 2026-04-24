<template>
  <div class="space-y-8 p-6 animate-in fade-in duration-700">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="text-4xl font-black tracking-tighter text-foreground/90 uppercase">
          {{ $t('features.school.finance.title') }}
        </h1>
        <p class="text-muted-foreground font-medium italic">
          {{ $t('features.school.finance.subtitle') }}
        </p>
      </div>
      <div class="flex gap-2">
        <Button
          v-if="activeTab === 'bills'"
          class="rounded-2xl shadow-lg shadow-primary/20 bg-indigo-600 hover:bg-indigo-700 h-11 px-6"
          @click="dialogs.generate = true"
        >
          <LucideIcon
            name="PlusCircle"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.finance.actions.generateBills') }}
        </Button>
        <Button
          v-if="activeTab === 'fee-types'"
          class="rounded-2xl bg-indigo-600 hover:bg-indigo-700 h-11 px-6"
          @click="feeTypesTabRef?.openAddDialog()"
        >
          <LucideIcon
            name="Plus"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.finance.actions.addFeeType') }}
        </Button>
        <Button
          v-if="activeTab === 'expenses'"
          class="rounded-2xl bg-indigo-600 hover:bg-indigo-700 h-11 px-6"
          @click="expensesTabRef?.openAddDialog()"
        >
          <LucideIcon
            name="Plus"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.finance.actions.addExpense') }}
        </Button>
        <Button
          v-if="activeTab === 'budgeting'"
          class="rounded-2xl bg-indigo-600 hover:bg-indigo-700 h-11 px-6"
          @click="budgetsTabRef?.openAddDialog()"
        >
          <LucideIcon
            name="Plus"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.finance.actions.addBudget') }}
        </Button>
        <Button
          variant="outline"
          class="rounded-2xl h-11 px-4"
          @click="refreshAll"
        >
          <LucideIcon
            name="refresh-cw"
            :class="{ 'animate-spin': loading }"
            class="w-4 h-4"
          />
        </Button>
      </div>
    </div>

    <!-- Financial Summary Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <Card
        v-for="(stat, idx) in financialStats"
        :key="idx" 
        class="relative overflow-hidden border-none bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl rounded-[2rem] shadow-sm hover:shadow-xl hover:translate-y-[-4px] transition-all duration-500 group"
      >
        <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
          <LucideIcon
            :name="stat.icon"
            class="w-24 h-24 rotate-12"
          />
        </div>
        <CardContent class="p-6 relative z-10">
          <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center mb-4 shadow-inner', stat.bgClass]">
            <LucideIcon
              :name="stat.icon"
              class="w-6 h-6"
            />
          </div>
          <p class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/60 mb-1">
            {{ stat.label }}
          </p>
          <h2 class="text-2xl font-black tracking-tight flex items-baseline gap-1">
            <span class="text-xs opacity-50 font-medium">Rp</span>
            {{ formatNumber(stat.value) }}
          </h2>
          <div class="mt-4 flex items-center gap-2">
            <div class="h-1 flex-1 bg-muted rounded-full overflow-hidden">
              <div
                class="h-full group-hover:bg-primary transition-colors duration-500"
                :class="stat.progressColor"
                :style="{ width: stat.percentage + '%' }"
              />
            </div>
            <span class="text-[10px] font-bold opacity-60">{{ Math.round(stat.percentage) }}%</span>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card class="border-none bg-white/60 dark:bg-slate-900/60 backdrop-blur-md rounded-[2.5rem] overflow-hidden shadow-2xl shadow-indigo-500/5">
      <CardContent class="p-0">
        <Tabs
          v-model="activeTab"
          class="w-full"
        >
          <TabsList class="p-2 m-4 bg-muted/50 rounded-2xl inline-flex h-auto gap-2 overflow-x-auto w-[calc(100%-2rem)] md:w-auto">
            <TabsTrigger
              value="bills"
              class="rounded-xl px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
            >
              {{ $t('features.school.finance.tabs.bills') }}
            </TabsTrigger>
            <TabsTrigger
              value="fee-types"
              class="rounded-xl px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
            >
              {{ $t('features.school.finance.tabs.feeTypes') }}
            </TabsTrigger>
            <TabsTrigger
              value="expenses"
              class="rounded-xl px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
            >
              {{ $t('features.school.finance.tabs.expenses') }}
            </TabsTrigger>
            <TabsTrigger
              value="budgeting"
              class="rounded-xl px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
            >
              {{ $t('features.school.finance.tabs.budgeting') }}
            </TabsTrigger>
          </TabsList>

          <TabsContent
            value="bills"
            class="p-0"
          >
            <BillsTab ref="billsTabRef" />
          </TabsContent>

          <TabsContent
            value="fee-types"
            class="p-0"
          >
            <FeeTypesTab ref="feeTypesTabRef" />
          </TabsContent>

          <TabsContent
            value="expenses"
            class="p-0"
          >
            <ExpensesTab ref="expensesTabRef" />
          </TabsContent>

          <TabsContent
            value="budgeting"
            class="p-0"
          >
            <BudgetsTab ref="budgetsTabRef" />
          </TabsContent>
        </Tabs>
      </CardContent>
    </Card>

    <!-- Global Dialogs like Generate Bill are retained at Parent level because they are not strictly tied to a single row action -->
    <GenerateBillDialog 
      v-model:open="dialogs.generate" 
      @save="refreshBillsAndSummary" 
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { storeToRefs } from 'pinia';
import {
  Card, CardContent, Button, LucideIcon, Tabs, TabsList, TabsTrigger, TabsContent
} from '@/components/ui';
import { useFinanceStore } from '../../../stores/finance';

import BillsTab from './tabs/BillsTab.vue';
import FeeTypesTab from './tabs/FeeTypesTab.vue';
import ExpensesTab from './tabs/ExpensesTab.vue';
import BudgetsTab from './tabs/BudgetsTab.vue';
import GenerateBillDialog from './components/GenerateBillDialog.vue';

const { t } = useI18n();
const financeStore = useFinanceStore();
const { loading, summary } = storeToRefs(financeStore);

const activeTab = ref('bills');

// Template Refs for Tabs
const billsTabRef = ref<InstanceType<typeof BillsTab> | null>(null);
const feeTypesTabRef = ref<InstanceType<typeof FeeTypesTab> | null>(null);
const expensesTabRef = ref<InstanceType<typeof ExpensesTab> | null>(null);
const budgetsTabRef = ref<InstanceType<typeof BudgetsTab> | null>(null);

const dialogs = ref({
  generate: false
});

const financialStats = computed(() => [
    { 
        label: t('features.school.finance.labels.totalReceivables'), 
        value: summary.value?.total_receivables || 0, 
        icon: 'HandCoins', 
        bgClass: 'bg-rose-500/10 text-rose-600',
        progressColor: 'bg-rose-500',
        percentage: 100 - (summary.value?.collection_rate || 0)
    },
    { 
        label: t('features.school.finance.labels.totalCollections'), 
        value: summary.value?.total_collections || 0, 
        icon: 'WalletCards', 
        bgClass: 'bg-emerald-500/10 text-emerald-600',
        progressColor: 'bg-emerald-500',
        percentage: summary.value?.collection_rate || 0
    },
    { 
        label: t('features.school.finance.labels.totalExpenses'), 
        value: summary.value?.total_expenses || 0, 
        icon: 'Receipt', 
        bgClass: 'bg-amber-500/10 text-amber-600',
        progressColor: 'bg-amber-500',
        percentage: (summary.value?.total_collections && summary.value?.total_collections > 0) ? ((summary.value?.total_expenses || 0) / summary.value.total_collections * 100) : 0
    },
    { 
        label: t('features.school.finance.labels.netBalance'), 
        value: summary.value?.net_balance || 0, 
        icon: 'BarChart3', 
        bgClass: 'bg-indigo-500/10 text-indigo-600',
        progressColor: 'bg-indigo-500',
        percentage: 100
    }
]);

const formatNumber = (val: number) => {
    return new Intl.NumberFormat('id-ID').format(val);
};

const refreshAll = async () => {
    await financeStore.fetchSummary();
    if (activeTab.value === 'bills') billsTabRef.value?.fetchBills();
    if (activeTab.value === 'fee-types') feeTypesTabRef.value?.fetchFeeTypes();
    if (activeTab.value === 'expenses') expensesTabRef.value?.fetchExpenses();
    if (activeTab.value === 'budgeting') budgetsTabRef.value?.fetchBudgets();
};

const refreshBillsAndSummary = async () => {
    await financeStore.fetchSummary();
    billsTabRef.value?.fetchBills();
};

watch(activeTab, (val) => {
  if (val === 'bills') billsTabRef.value?.fetchBills();
  if (val === 'fee-types') feeTypesTabRef.value?.fetchFeeTypes();
  if (val === 'expenses') expensesTabRef.value?.fetchExpenses();
  if (val === 'budgeting') budgetsTabRef.value?.fetchBudgets();
});

onMounted(() => {
  financeStore.fetchSummary();
});
</script>
