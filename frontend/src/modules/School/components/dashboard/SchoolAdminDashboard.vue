<template>
  <div class="space-y-8 animate-in fade-in duration-700">
    <!-- Header Section: Premium & Dynamic -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 to-slate-800 p-8 mb-8 shadow-2xl group">
      <!-- Mesh Gradient Background Decoration -->
      <div class="absolute top-0 right-0 w-96 h-96 bg-primary/20 rounded-full -mr-32 -mt-32 blur-[100px] group-hover:bg-primary/30 transition-colors duration-1000" />
      <div class="absolute bottom-0 left-0 w-64 h-64 bg-blue-500/10 rounded-full -ml-32 -mb-32 blur-[80px]" />
      
      <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
          <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20 shadow-inner group-hover:scale-105 transition-transform duration-500">
            <LucideIcon
              :name="unitStore.activeUnitId === 0 ? 'Globe' : 'School'"
              class="w-8 h-8 text-white drop-shadow-md"
            />
          </div>
          <div class="space-y-1">
            <div class="flex items-center gap-2">
              <Badge variant="outline" class="bg-primary/20 text-primary-foreground border-primary/30 text-[10px] font-black tracking-widest uppercase px-2 py-0.5 rounded-md">
                {{ unitStore.activeUnitId === 0 ? $t('common.labels.ecosystem') : $t('common.labels.unitContext') }}
              </Badge>
              <span class="w-1 h-1 rounded-full bg-white/20" />
              <span class="text-white/40 text-[10px] font-bold tracking-widest uppercase">V2.0 PRO</span>
            </div>
            <h1 class="text-4xl font-black tracking-tighter text-white uppercase">
              {{ unitStore.activeUnitId === 0 ? $t('common.labels.ecosystemSummary') : $t('dashboard.v2.hero.title') }}
            </h1>
            <p class="text-white/60 text-sm font-medium max-w-xl leading-relaxed">
              {{ unitStore.activeUnitId === 0 ? $t('common.messages.ecosystemSubtitle') : $t('dashboard.v2.hero.subtitle') }}
            </p>
          </div>
        </div>
        
        <div class="flex items-center gap-3">
          <Button
            variant="outline"
            class="rounded-xl h-12 px-6 font-bold bg-white/5 border-white/10 text-white hover:bg-white/10 hover:border-white/20 transition-all shadow-lg backdrop-blur-sm"
            @click="$router.push({ name: 'schools.index' })"
          >
            <LucideIcon name="Settings2" class="w-4 h-4 mr-2" />
            {{ $t('dashboard.v2.hero.actions.config') }}
          </Button>
          <Button
            variant="default"
            class="rounded-xl h-12 px-6 font-bold bg-primary text-primary-foreground hover:opacity-90 transition-all shadow-xl shadow-primary/20"
          >
            <LucideIcon name="Download" class="w-4 h-4 mr-2" />
            {{ $t('dashboard.v2.hero.actions.reports') }}
          </Button>
        </div>
      </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 px-2">
      <Card
        v-for="(stat, idx) in adminQuickStats"
        :key="idx" 
        class="relative overflow-hidden border-border/40 bg-card/50 backdrop-blur-sm shadow-sm rounded-2xl hover:shadow-xl hover:shadow-primary/5 transition-all duration-500 cursor-pointer group active:scale-[0.98] border-l-4"
        :class="[
          idx === 0 ? 'border-l-blue-500' : 
          idx === 1 ? 'border-l-emerald-500' : 
          idx === 2 ? 'border-l-amber-500' : 'border-l-indigo-500'
        ]"
        @click="$router.push({ name: stat.routeName })"
      >
        <!-- Subtle Background Glow -->
        <div 
          class="absolute -right-8 -top-8 w-32 h-32 blur-3xl opacity-0 group-hover:opacity-20 transition-opacity duration-700 pointer-events-none rounded-full"
          :class="[
            idx === 0 ? 'bg-blue-500' : 
            idx === 1 ? 'bg-emerald-500' : 
            idx === 2 ? 'bg-amber-500' : 'bg-indigo-500'
          ]"
        />

        <CardContent class="p-6 relative z-10">
          <div class="flex items-center justify-between">
            <div class="space-y-1">
              <p class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-[0.2em]">
                {{ stat.label }}
              </p>
              <div class="flex items-baseline gap-1">
                <p class="text-3xl font-black text-foreground tracking-tight leading-none">
                  {{ stat.value }}
                </p>
                <div class="w-1 h-1 rounded-full bg-primary/40 group-hover:animate-ping" />
              </div>
            </div>
            <div 
              class="p-3.5 rounded-xl transition-all duration-500 group-hover:scale-110 group-hover:rotate-6 shadow-sm"
              :class="[
                idx === 0 ? 'bg-blue-500/10 text-blue-500 group-hover:bg-blue-500 group-hover:text-white' : 
                idx === 1 ? 'bg-emerald-500/10 text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white' : 
                idx === 2 ? 'bg-amber-500/10 text-amber-500 group-hover:bg-amber-500 group-hover:text-white' : 
                'bg-indigo-500/10 text-indigo-500 group-hover:bg-indigo-500 group-hover:text-white'
              ]"
            >
              <LucideIcon
                :name="stat.icon"
                class="w-6 h-6"
              />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Visual Analytics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 px-2">
      <!-- Unit Distribution Chart (Global Mode) / Student Status (Unit Mode) -->
      <Card class="border-border/40 bg-card shadow-none rounded-xl">
        <CardHeader class="p-6 pb-2">
          <CardTitle class="text-lg font-bold">
            {{ unitStore.activeUnitId === 0 ? $t('dashboard.v2.charts.studentDist') : $t('dashboard.v2.charts.studentStatus') }}
          </CardTitle>
          <CardDescription>
            {{ unitStore.activeUnitId === 0 ? $t('dashboard.v2.charts.studentDistDesc') : $t('dashboard.v2.charts.studentStatusDesc') }}
          </CardDescription>
        </CardHeader>
        <CardContent class="p-6">
          <div class="h-[300px] flex items-center justify-center">
            <Bar 
              v-if="unitStore.activeUnitId === 0 && chartData.labels.length > 0"
              :data="chartData" 
              :options="barChartOptions" 
            />
            <Doughnut 
              v-else-if="unitStore.activeUnitId !== 0"
              :data="doughnutChartData"
              :options="doughnutChartOptions"
            />
            <div v-else class="text-muted-foreground text-sm italic animate-pulse">
              {{ $t('common.messages.preparingChart') }}
            </div>
          </div>
        </CardContent>
      </Card>
 
      <!-- Activity/Growth Chart -->
      <Card class="border-border/40 bg-card shadow-none rounded-xl">
        <CardHeader class="p-6 pb-2">
          <CardTitle class="text-lg font-bold">{{ $t('dashboard.v2.charts.comparative') }}</CardTitle>
          <CardDescription>{{ $t('dashboard.v2.charts.comparativeDesc') }}</CardDescription>
        </CardHeader>
        <CardContent class="p-6">
          <div class="h-[300px] flex items-center justify-center">
             <Bar 
              v-if="unitStore.activeUnitId === 0 && staffChartData.labels.length > 0"
              :data="staffChartData" 
              :options="barChartOptions" 
            />
             <div v-else class="flex flex-col items-center gap-4 text-muted-foreground opacity-40">
                <LucideIcon name="BarChart3" class="w-12 h-12" />
                <p class="text-xs font-bold uppercase tracking-widest">{{ $t('dashboard.v2.charts.globalOnly') }}</p>
             </div>
          </div>
        </CardContent>
      </Card>
    </div>
 
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
      <!-- Personnel Presence -->
      <Card class="lg:col-span-2 border-border/40 bg-card shadow-none rounded-xl group">
        <CardHeader class="flex flex-row items-center justify-between p-8 pb-4">
          <div>
            <CardTitle class="text-xl font-bold tracking-tight text-foreground/90">
              {{ $t('dashboard.v2.hr_presence.title') }}
            </CardTitle>
            <CardDescription>
              {{ $t('dashboard.v2.hr_presence.subtitle') }}
            </CardDescription>
          </div>
          <Button
            variant="ghost"
            size="icon"
            class="rounded-xl hover:bg-muted"
          >
            <LucideIcon
              name="Filter"
              class="w-5 h-5 opacity-40"
            />
          </Button>
        </CardHeader>
        <CardContent class="px-8 pb-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="(staff, idx) in personnelList"
              :key="idx" 
              class="flex items-center gap-4 p-4 rounded-xl bg-muted/20 border border-border/20 hover:border-primary/20 hover:bg-muted/40 transition-all duration-300 group/staff cursor-pointer"
            >
              <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-black text-base transition-transform group-hover/staff:scale-105">
                {{ staff.initials }}
              </div>
              <div class="flex-1 min-w-0">
                <h5 class="font-bold text-foreground truncate text-sm">
                  {{ staff.name }}
                </h5>
                <p class="text-[10px] text-muted-foreground font-medium">
                  {{ staff.role }}
                </p>
              </div>
              <div class="flex flex-col items-end gap-1">
                <Badge
                  variant="secondary"
                  class="bg-success/10 text-success border-none rounded-lg font-black text-[8px]"
                >
                  {{ staff.time }}
                </Badge>
                <span class="text-[8px] font-bold opacity-40">{{ $t(`dashboard.v2.hr_presence.status.${staff.statusKey}`) }}</span>
              </div>
            </div>
            <!-- Empty state if no personnel -->
            <div v-if="personnelList.length === 0" class="md:col-span-2 p-8 text-center text-muted-foreground text-sm italic opacity-60">
                {{ $t('dashboard.v2.hr_presence.empty') }}
            </div>
          </div>
        </CardContent>
      </Card>

      <div class="space-y-10">
        <!-- Strategic Alerts -->
        <Card class="border-border/40 bg-slate-900 shadow-2xl rounded-3xl p-8 group relative overflow-hidden">
          <!-- Decoration -->
          <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-destructive/10 rounded-full blur-3xl group-hover:bg-destructive/20 transition-colors" />
          
          <h4 class="text-xs font-black text-destructive flex items-center gap-3 mb-8 uppercase tracking-[0.3em]">
            <div class="w-2.5 h-2.5 rounded-full bg-destructive animate-pulse shadow-[0_0_10px_rgba(239,68,68,0.5)]" />
            {{ $t('dashboard.v2.alerts.title') }}
          </h4>
          
          <div class="space-y-6 relative z-10">
            <div
              v-for="alert in alertsList"
              :key="alert.id"
              class="flex items-start gap-5 p-5 rounded-2xl bg-white/5 border border-white/5 hover:bg-white/10 hover:border-white/10 transition-all duration-300 cursor-pointer group/item"
            >
              <div class="p-3 rounded-xl bg-destructive/20 text-destructive group-hover/item:scale-110 transition-transform">
                <LucideIcon
                  :name="alert.icon"
                  class="w-5 h-5"
                />
              </div>
              <div class="space-y-1">
                <h5 class="text-sm font-bold text-white/90 leading-snug">
                  {{ alert.title }}
                </h5>
                <p class="text-[10px] font-black text-white/40 uppercase tracking-widest">
                  {{ alert.status }}
                </p>
              </div>
            </div>
          </div>

          <Button
            variant="ghost"
            class="w-full mt-8 rounded-xl text-white/40 hover:text-white hover:bg-white/5 font-bold text-xs uppercase tracking-widest border border-white/5"
          >
            {{ $t('common.actions.viewAll') }}
            <LucideIcon name="ArrowRight" class="w-3.5 h-3.5 ml-2" />
          </Button>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  Card, CardContent, CardHeader, CardTitle, CardDescription,
  Button, LucideIcon, Badge
} from '@/components/ui';
import { useUnitStore } from '@/modules/School/stores/unit';
import { InstitutionService } from '@/modules/School/services/InstitutionService';
import { parseResponse } from '@/utils/responseParser';
import { Bar, Doughnut } from 'vue-chartjs';
import { 
    Chart as ChartJS, Title, Tooltip, Legend, 
    BarElement, CategoryScale, LinearScale, ArcElement 
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

const { t } = useI18n();
const unitStore = useUnitStore();


const statsData = ref<any[]>([]);
const breakdownData = ref<any[]>([]);
const personnelList = ref<Personnel[]>([]);
const alertsList = ref<any[]>([]);
const loading = ref(true);

const adminQuickStats = computed(() => [
  { label: t('features.school.stats.totalStudents'), value: statsData.value[0]?.value ?? '0', icon: 'Users', routeName: 'students.index' },
  { label: t('features.school.stats.totalStaff'), value: statsData.value[1]?.value ?? '0', icon: 'UserSquare', routeName: 'staff.index' },
  { label: t('features.school.stats.studyGroups'), value: statsData.value[2]?.value ?? '0', icon: 'Layers', routeName: 'academic.index' },
  { label: t('features.school.stats.assets'), value: statsData.value[3]?.value ?? '0', icon: 'Package', routeName: 'sarpras.index' },
]);

// Chart Configurations
const chartData = computed(() => ({
  labels: breakdownData.value.map(b => b.name),
  datasets: [
    {
      label: t('features.school.stats.totalStudents'),
      backgroundColor: '#3b82f6',
      hoverBackgroundColor: '#2563eb',
      borderRadius: 12,
      data: breakdownData.value.map(b => b.student_count)
    }
  ]
}));

const staffChartData = computed(() => ({
  labels: breakdownData.value.map(b => b.name),
  datasets: [
    {
      label: t('features.school.stats.totalStaff'),
      backgroundColor: '#10b981',
      hoverBackgroundColor: '#059669',
      borderRadius: 12,
      data: breakdownData.value.map(b => b.staff_count)
    }
  ]
}));

const doughnutChartData = computed(() => ({
  labels: [t('common.status.active'), t('common.labels.graduated'), t('common.labels.deleted')],
  datasets: [
    {
      backgroundColor: ['#3b82f6', '#10b981', '#ef4444'],
      borderWidth: 0,
      hoverOffset: 15,
      data: [
          parseInt(statsData.value[0]?.value || '0'), 
          0, 
          0
      ]
    }
  ]
}));

const barChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false }
  },
  scales: {
      y: { beginAtZero: true, grid: { display: false } },
      x: { grid: { display: false } }
  }
};

const doughnutChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
      legend: { position: 'bottom' as const }
  }
};

interface Personnel {
    initials: string;
    name: string;
    role: string;
    time: string;
    statusKey: string;
}

const fetchData = async () => {
    loading.value = true;
    try {
        const statsRes = await InstitutionService.getStats();
        const data = parseResponse(statsRes).data as any;
        
        if (data.stats) {
            statsData.value = data.stats;
            breakdownData.value = data.breakdown || [];
            personnelList.value = data.personnel || [];
            alertsList.value = data.alerts || [];
        } else {
            statsData.value = data;
        }
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
