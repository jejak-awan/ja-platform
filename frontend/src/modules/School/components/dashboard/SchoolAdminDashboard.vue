<template>
  <div class="space-y-6 animate-in fade-in duration-700">
    <!-- Header Section: Clean & Flat (Consistent with Core Admin) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2 px-2">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-foreground">
            {{ unitStore.activeUnitId === 0 ? $t('common.labels.ecosystemSummary') : $t('dashboard.v2.hero.title') }}
        </h1>
        <p class="text-muted-foreground text-sm font-medium">
          {{ unitStore.activeUnitId === 0 ? $t('common.messages.ecosystemSubtitle') : $t('dashboard.v2.hero.subtitle') }}
        </p>
      </div>
      <div class="flex items-center gap-2">
        <Button
          variant="outline"
          size="sm"
          class="bg-muted/40 border border-border/40 hover:bg-muted/60"
          @click="fetchData"
        >
          <LucideIcon name="RefreshCw" class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" />
          {{ $t('common.actions.refresh') }}
        </Button>
        <Button
          variant="default"
          size="sm"
          class="rounded-lg shadow-none"
          @click="$router.push({ name: 'schools.index' })"
        >
          <LucideIcon name="Settings" class="w-4 h-4 mr-2" />
          {{ $t('dashboard.v2.hero.actions.config') }}
        </Button>
      </div>
    </div>

    <!-- Quick Stats Grid: Standard Shadcn Style -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 px-2">
      <Card
        v-for="(stat, idx) in adminQuickStats"
        :key="idx" 
        class="border-border/40 bg-card shadow-none rounded-xl hover:bg-muted/30 transition-all duration-200 cursor-pointer group active:scale-[0.98]"
        @click="$router.push({ name: stat.routeName })"
      >
        <CardContent class="p-6">
          <div class="flex items-start justify-between">
            <div class="space-y-1">
              <p class="text-sm font-medium text-muted-foreground">
                {{ stat.label }}
              </p>
              <p class="text-3xl font-bold text-foreground">
                {{ stat.value }}
              </p>
              <div v-if="idx === 0" class="flex items-center gap-1.5 text-xs text-primary font-medium mt-2">
                <LucideIcon name="CheckCircle2" class="w-3 h-3" />
                <span>{{ $t('common.status.active') }}</span>
              </div>
            </div>
            <div class="p-2.5 rounded-xl bg-primary/10 text-primary">
              <LucideIcon
                :name="stat.icon"
                class="w-5 h-5"
              />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 px-2">
      <!-- Main Chart Card -->
      <Card class="border-border/40 bg-card shadow-none rounded-xl">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
          <div class="space-y-1">
            <CardTitle class="text-lg font-bold flex items-center gap-2">
                <LucideIcon name="BarChart3" class="w-5 h-5 text-primary" />
                {{ unitStore.activeUnitId === 0 ? $t('dashboard.v2.charts.studentDist') : $t('dashboard.v2.charts.studentStatus') }}
            </CardTitle>
            <CardDescription>
                {{ unitStore.activeUnitId === 0 ? $t('dashboard.v2.charts.studentDistDesc') : $t('dashboard.v2.charts.studentStatusDesc') }}
            </CardDescription>
          </div>
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

      <!-- Comparative Analytics -->
      <Card class="border-border/40 bg-card shadow-none rounded-xl">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
          <div class="space-y-1">
            <CardTitle class="text-lg font-bold flex items-center gap-2">
                <LucideIcon name="PieChart" class="w-5 h-5 text-primary" />
                {{ $t('dashboard.v2.charts.comparative') }}
            </CardTitle>
            <CardDescription>{{ $t('dashboard.v2.charts.comparativeDesc') }}</CardDescription>
          </div>
        </CardHeader>
        <CardContent class="p-6">
          <div class="h-[300px] flex items-center justify-center">
             <Bar 
              v-if="unitStore.activeUnitId === 0 && staffChartData.labels.length > 0"
              :data="staffChartData" 
              :options="barChartOptions" 
            />
             <div v-else class="flex flex-col items-center gap-4 text-muted-foreground opacity-40">
                <LucideIcon name="ShieldAlert" class="w-10 h-10" />
                <p class="text-xs font-bold uppercase tracking-widest">{{ $t('dashboard.v2.charts.globalOnly') }}</p>
             </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 px-2">
      <!-- Personnel Presence -->
      <Card class="lg:col-span-2 border-border/40 bg-card shadow-none rounded-xl group">
        <CardHeader class="flex flex-row items-center justify-between p-6 pb-2">
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
            class="h-8 w-8 rounded-lg hover:bg-muted"
          >
            <LucideIcon
              name="Filter"
              class="w-4 h-4 opacity-40"
            />
          </Button>
        </CardHeader>
        <CardContent class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="(staff, idx) in personnelList"
              :key="idx" 
              class="flex items-center gap-4 p-4 rounded-xl border border-border/40 hover:bg-muted/40 transition-all duration-300 group/staff cursor-pointer"
            >
              <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-black text-base">
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

      <!-- Strategic Alerts -->
      <Card class="border-border/40 bg-card shadow-none rounded-xl p-6">
        <h4 class="text-xs font-bold text-destructive flex items-center gap-2 mb-4 uppercase tracking-wider">
          <LucideIcon name="AlertTriangle" class="w-4 h-4" />
          {{ $t('dashboard.v2.alerts.title') }}
        </h4>
        
        <div class="space-y-4">
          <div
            v-for="alert in alertsList"
            :key="alert.id"
            class="flex items-start gap-4 p-3 rounded-lg border border-border/40 hover:bg-muted/50 transition-colors cursor-pointer group/item"
          >
            <div class="p-2 rounded-lg bg-destructive/10 text-destructive">
              <LucideIcon
                :name="alert.icon"
                class="w-4 h-4"
              />
            </div>
            <div class="space-y-1">
              <h5 class="text-sm font-bold text-foreground leading-tight">
                {{ alert.title }}
              </h5>
              <p class="text-[10px] font-medium text-muted-foreground uppercase">
                {{ alert.status }}
              </p>
            </div>
          </div>
        </div>

        <Button
          variant="outline"
          class="w-full mt-6 rounded-lg text-xs font-bold border-border hover:bg-muted"
        >
          {{ $t('common.actions.viewAll') }}
        </Button>
      </Card>
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
