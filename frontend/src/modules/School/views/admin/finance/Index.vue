<template>
  <div class="space-y-8 p-6 animate-in fade-in duration-700">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-2">
      <div>
        <h1 class="text-3xl font-black tracking-tight text-foreground uppercase">
          {{ $t('features.school.finance.title') }}
        </h1>
        <p class="text-muted-foreground text-sm font-medium italic">
          {{ $t('features.school.finance.subtitle') }}
        </p>
      </div>
      <div class="flex gap-2">
        <Button
          v-if="activeTab === 'bills'"
          class="rounded-xl shadow-sm h-11 px-6"
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
          class="rounded-xl h-11 px-6"
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
          class="rounded-xl h-11 px-6"
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
          class="rounded-xl h-11 px-6"
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
          class="rounded-xl h-11 px-4 border-border/40"
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
    <!-- Financial Summary Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <Card
        v-for="(stat, idx) in financialStats"
        :key="idx" 
        class="bg-card border border-border/40 rounded-xl shadow-none hover:bg-muted/30 transition-all duration-300 group"
      >
        <CardContent class="p-6 relative z-10">
          <div :class="['w-10 h-10 rounded-xl flex items-center justify-center mb-4 border border-border/40 bg-muted/50', stat.bgClass.split(' ')[1]]">
            <LucideIcon
              :name="stat.icon"
              class="w-5 h-5"
            />
          </div>
          <p class="text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground/60 mb-1">
            {{ stat.label }}
          </p>
          <h2 class="text-2xl font-black tracking-tight text-foreground flex items-baseline gap-1">
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

    <Card class="border border-border/40 bg-card shadow-none rounded-xl overflow-hidden">
      <CardContent class="p-0">
        <Tabs
          v-model="activeTab"
          class="w-full"
        >
          <TabsList class="p-2 m-4 bg-muted/50 rounded-2xl inline-flex h-auto gap-2 overflow-x-auto w-[calc(100%-2rem)] md:w-auto">
            <TabsTrigger
              value="bills"
              class="rounded-lg px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
            >
              {{ $t('features.school.finance.tabs.bills') }}
            </TabsTrigger>
            <TabsTrigger
              value="fee-types"
              class="rounded-lg px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
            >
              {{ $t('features.school.finance.tabs.feeTypes') }}
            </TabsTrigger>
            <TabsTrigger
              value="expenses"
              class="rounded-lg px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
            >
              {{ $t('features.school.finance.tabs.expenses') }}
            </TabsTrigger>
            <TabsTrigger
              value="budgeting"
              class="rounded-lg px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
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
