<template>
  <div class="space-y-10 animate-in fade-in slide-in-from-bottom-5 duration-1000 p-2">
    <!-- Admin Hero Section (The Command Center) -->
    <div class="relative overflow-hidden group">
      <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-800 opacity-95 rounded-[3rem] shadow-2xl shadow-indigo-500/20" />
      <div class="absolute -right-20 -top-20 w-96 h-96 bg-white/10 rounded-full blur-[80px] group-hover:bg-white/15 transition-all duration-700" />
      
      <div class="relative z-10 p-10 flex flex-col lg:flex-row justify-between items-center gap-10 text-white">
        <div class="space-y-6 text-center lg:text-left">
          <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-md rounded-full border border-white/20">
            <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
            <span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-90">{{ $t('features.school.dashboard.v2.hero.badge') }}</span>
          </div>
          <h1 class="text-5xl font-black tracking-tighter leading-none">
            {{ $t('features.school.dashboard.v2.hero.title') }}
          </h1>
          <p class="text-white/60 max-w-xl text-lg font-medium leading-relaxed italic">
            {{ $t('features.school.dashboard.v2.hero.subtitle') }}
          </p>
          <div class="flex flex-wrap justify-center lg:justify-start gap-4 pt-4">
            <Button class="bg-white text-indigo-700 hover:bg-white/90 font-black rounded-2xl h-14 px-10 shadow-xl shadow-white/10 transition-transform active:scale-95">
              {{ $t('features.school.dashboard.v2.hero.actions.reports') }}
            </Button>
            <Button
              variant="ghost"
              class="text-white hover:bg-white/10 border border-white/10 font-bold rounded-2xl h-14 px-8"
            >
              {{ $t('features.school.dashboard.v2.hero.actions.config') }}
            </Button>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div
            v-for="(stat, idx) in adminQuickStats"
            :key="idx" 
            class="p-6 rounded-[2.5rem] bg-white/5 backdrop-blur-xl border border-white/10 flex flex-col items-center justify-center w-40 h-40 hover:bg-white/10 hover:translate-y-[-5px] transition-all duration-500 cursor-pointer group/stat"
          >
            <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center mb-3 group-hover/stat:rotate-12 transition-transform">
              <LucideIcon
                :name="stat.icon"
                class="w-6 h-6"
              />
            </div>
            <span class="text-3xl font-black tracking-tighter leading-none">{{ stat.value }}</span>
            <span class="text-[9px] font-black uppercase tracking-[0.2em] opacity-50 mt-2 text-center">{{ stat.label }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
      <!-- Personnel Presence (Personnel Hub) -->
      <Card class="lg:col-span-2 border-none bg-white/40 dark:bg-slate-900/40 backdrop-blur-2xl rounded-[3rem] shadow-sm hover:shadow-2xl transition-all duration-700 group">
        <CardHeader class="flex flex-row items-center justify-between p-10 pb-4">
          <div>
            <CardTitle class="text-2xl font-black tracking-tight text-foreground/90 uppercase">
              {{ $t('features.school.dashboard.v2.hr_presence.title') }}
            </CardTitle>
            <CardDescription class="font-medium italic">
              {{ $t('features.school.dashboard.v2.hr_presence.subtitle') }}
            </CardDescription>
          </div>
          <Button
            variant="ghost"
            size="icon"
            class="rounded-2xl hover:bg-white/50"
          >
            <LucideIcon
              name="Filter"
              class="w-5 h-5 opacity-40"
            />
          </Button>
        </CardHeader>
        <CardContent class="px-10 pb-10">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="(staff, idx) in personnelList"
              :key="idx" 
              class="flex items-center gap-5 p-5 rounded-[2rem] bg-white/50 dark:bg-slate-800/50 border border-transparent hover:border-indigo-500/20 hover:bg-white transition-all duration-500 group/staff cursor-pointer"
            >
              <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-600 font-black text-lg transition-transform group-hover/staff:rotate-6">
                {{ staff.initials }}
              </div>
              <div class="flex-1 min-w-0">
                <h5 class="font-black text-foreground truncate">
                  {{ staff.name }}
                </h5>
                <p class="text-[10px] text-muted-foreground uppercase font-black tracking-widest">
                  {{ staff.role }}
                </p>
              </div>
              <div class="flex flex-col items-end gap-1">
                <Badge
                  variant="secondary"
                  class="bg-emerald-500/10 text-emerald-600 border-none rounded-lg font-black text-[9px]"
                >
                  {{ staff.time }}
                </Badge>
                <span class="text-[9px] font-bold opacity-30">{{ $t(`features.school.dashboard.v2.hr_presence.status.${staff.statusKey}`) }}</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Financial Pulse (The Vault) -->
      <div class="space-y-10">
        <Card class="border-none bg-indigo-600 text-white rounded-[3rem] shadow-2xl shadow-indigo-600/20 overflow-hidden relative group">
          <div class="absolute right-[-10%] bottom-[-10%] opacity-10 group-hover:rotate-12 transition-transform duration-700">
            <LucideIcon
              name="Vault"
              class="w-48 h-48"
            />
          </div>
          <CardHeader class="p-8 pb-4 relative z-10">
            <CardTitle class="text-xs font-black uppercase tracking-[0.3em] opacity-60">
              {{ $t('features.school.finance.summary') }}
            </CardTitle>
          </CardHeader>
          <CardContent class="p-8 relative z-10">
            <div class="space-y-8">
              <div>
                <p class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-2">
                  {{ $t('features.school.finance.labels.netBalance') }}
                </p>
                <h3 class="text-5xl font-black tracking-tighter flex items-baseline gap-2">
                  <span class="text-sm opacity-50 font-medium">Rp</span>
                  {{ summary?.net_balance ? formatCurrency(summary.net_balance) : '0' }}
                </h3>
              </div>

              <div class="space-y-6 pt-4 border-t border-white/10">
                <div
                  v-for="metric in financeMetrics"
                  :key="metric.label"
                  class="space-y-3"
                >
                  <div class="flex justify-between items-end">
                    <span class="text-[10px] font-black uppercase tracking-widest opacity-60">{{ metric.label }}</span>
                    <span class="text-sm font-black">{{ metric.percent }}%</span>
                  </div>
                  <div class="h-1.5 bg-white/10 rounded-full overflow-hidden">
                    <div
                      class="h-full bg-white rounded-full transition-all duration-1000"
                      :style="{ width: metric.percent + '%' }"
                    />
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
          <CardFooter class="p-8 pt-0 relative z-10">
            <Button
              variant="ghost"
              class="w-full text-xs font-black uppercase tracking-widest h-14 rounded-2xl bg-white/10 hover:bg-white/20 hover:text-white border-0"
              @click="$router.push({ name: 'finance.index' })"
            >
              {{ $t('features.school.finance.tabs.reports') }}
              <LucideIcon
                name="ArrowRight"
                class="w-4 h-4 ml-2"
              />
            </Button>
          </CardFooter>
        </Card>

        <!-- Strategic Alerts -->
        <Card class="border-none bg-rose-500/5 dark:bg-rose-500/10 border-l-4 border-rose-500 rounded-[2.5rem] p-8 shadow-inner group">
          <h4 class="text-xs font-black uppercase tracking-[0.2em] text-rose-600 flex items-center gap-3 mb-6">
            <div class="w-2 h-2 rounded-full bg-rose-500 group-hover:animate-ping" />
            {{ $t('features.school.dashboard.v2.alerts.title') }}
          </h4>
          <div class="space-y-6">
            <div
              v-for="alert in alertsList"
              :key="alert.id"
              class="flex gap-4 group/alert cursor-pointer"
            >
              <div class="w-10 h-10 rounded-2xl bg-rose-500/10 flex items-center justify-center text-rose-600 transition-transform group-hover/alert:scale-110">
                <LucideIcon
                  :name="alert.icon"
                  class="w-5 h-5"
                />
              </div>
              <div>
                <p class="text-sm font-black text-foreground/80 leading-tight group-hover/alert:text-rose-600 transition-colors">
                  {{ alert.title }}
                </p>
                <p class="text-[10px] font-bold text-muted-foreground/60 mt-1 uppercase leading-none">
                  {{ alert.status }}
                </p>
              </div>
            </div>
          </div>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { storeToRefs } from 'pinia';
import {
  Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter,
  Button, LucideIcon, Badge
} from '@/components/ui';
import { InstitutionService } from '@/modules/School/services/InstitutionService';
import { useFinanceStore } from '../../stores/finance';
import { parseResponse } from '@/utils/responseParser';

const { t } = useI18n();
const financeStore = useFinanceStore();
const { summary } = storeToRefs(financeStore);

const statsData = ref<any[]>([]);
const personnelList = ref<Personnel[]>([]);
const alertsList = ref<any[]>([]);
const loading = ref(true);

const adminQuickStats = computed(() => [
  { label: t('features.school.stats.totalStudents'), value: statsData.value[0]?.value || '1.2k', icon: 'Users' },
  { label: t('features.school.stats.totalStaff'), value: statsData.value[1]?.value || '86', icon: 'UserCog' },
  { label: t('features.school.stats.studyGroups'), value: statsData.value[2]?.value || '32', icon: 'DoorOpen' },
  { label: t('features.school.stats.assets'), value: statsData.value[3]?.value || '150', icon: 'Package' },
]);

interface Personnel {
    initials: string;
    name: string;
    role: string;
    time: string;
    statusKey: string;
}

const financeMetrics = computed(() => [
    { label: t('features.school.finance.income'), percent: summary.value?.collection_rate || 0 },
    { label: t('features.school.finance.expenses'), percent: (summary.value?.total_collections && summary.value?.total_collections > 0) ? Math.round((summary.value?.total_expenses || 0) / summary.value.total_collections * 100) : 0 },
]);

const formatCurrency = (val: number) => {
    if (val >= 1000000000) return (val / 1000000000).toFixed(1) + 'B';
    if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
    return new Intl.NumberFormat('id-ID').format(val);
};

const fetchData = async () => {
    loading.value = true;
    try {
        const statsRes = await InstitutionService.getStats();
        const data = parseResponse(statsRes).data as any;
        
        // Handle new structured response or fallback to old array structure
        if (data.stats) {
            statsData.value = data.stats;
            personnelList.value = data.personnel || [];
            alertsList.value = data.alerts || [];
        } else {
            statsData.value = data;
        }
        
        await financeStore.fetchSummary();
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchData();
});
</script>
