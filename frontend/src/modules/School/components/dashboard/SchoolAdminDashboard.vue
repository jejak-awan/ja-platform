<template>
  <div class="space-y-8 animate-in fade-in duration-700">
    <!-- Header Section: Clean & Standard -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2 px-2">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20">
            <LucideIcon
              :name="unitStore.activeUnitId === 0 ? 'Globe' : 'School'"
              class="w-5 h-5 text-primary"
            />
          </div>
          <h1 class="text-3xl font-bold tracking-tight text-foreground uppercase">
            {{ unitStore.activeUnitId === 0 ? $t('common.labels.ecosystemSummary') : $t('dashboard.v2.hero.title') }}
          </h1>
        </div>
        <p class="text-muted-foreground text-sm font-medium">
          {{ unitStore.activeUnitId === 0 ? $t('common.messages.ecosystemSubtitle') : $t('dashboard.v2.hero.subtitle') }}
        </p>
      </div>
      <div class="flex items-center gap-2">
        <Button
          variant="outline"
          class="rounded-xl h-10 px-6 font-bold shadow-sm"
          @click="$router.push({ name: 'schools.index' })"
        >
          <LucideIcon name="Settings" class="w-4 h-4 mr-2" />
          {{ $t('dashboard.v2.hero.actions.config') }}
        </Button>
      </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 px-2">
      <Card
        v-for="(stat, idx) in adminQuickStats"
        :key="idx" 
        class="border-border/40 bg-card shadow-none rounded-xl hover:bg-muted/30 transition-all duration-300 cursor-pointer group active:scale-[0.98]"
        @click="$router.push({ name: stat.routeName })"
      >
        <CardContent class="p-6">
          <div class="flex items-start justify-between">
            <div class="space-y-1">
              <p class="text-xs font-bold text-muted-foreground uppercase tracking-wider">
                {{ stat.label }}
              </p>
              <p class="text-3xl font-black text-foreground">
                {{ stat.value }}
              </p>
            </div>
            <div class="p-2.5 rounded-xl bg-primary/10 text-primary group-hover:scale-110 transition-transform">
              <LucideIcon
                :name="stat.icon"
                class="w-5 h-5"
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
                {{ $t('features.school.dashboard.v2.hr_presence.empty') }}
            </div>
          </div>
        </CardContent>
      </Card>

      <div class="space-y-10">
        <!-- Strategic Alerts -->
        <Card class="border-border/40 bg-destructive/5 dark:bg-destructive/10 border-l-4 border-destructive rounded-xl p-8 shadow-none group">
          <h4 class="text-xs font-bold text-destructive flex items-center gap-3 mb-6">
            <div class="w-2 h-2 rounded-full bg-destructive group-hover:animate-ping" />
            {{ $t('dashboard.v2.alerts.title') }}
          </h4>
          <div class="space-y-6">
            <div
              v-for="alert in alertsList"
              :key="alert.id"
              class="flex gap-4 group/alert cursor-pointer"
            >
              <div class="w-9 h-9 rounded-xl bg-destructive/10 flex items-center justify-center text-destructive transition-transform group-hover/alert:scale-110">
                <LucideIcon
                  :name="alert.icon"
                  class="w-4 h-4"
                />
              </div>
              <div>
                <p class="text-sm font-bold text-foreground/80 leading-tight group-hover/alert:text-destructive transition-colors">
                  {{ alert.title }}
                </p>
                <p class="text-[10px] font-medium text-muted-foreground/60 mt-1">
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
      label: 'Jumlah Siswa',
      backgroundColor: '#3b82f6',
      borderRadius: 8,
      data: breakdownData.value.map(b => b.student_count)
    }
  ]
}));

const staffChartData = computed(() => ({
  labels: breakdownData.value.map(b => b.name),
  datasets: [
    {
      label: 'Jumlah Guru/PTK',
      backgroundColor: '#10b981',
      borderRadius: 8,
      data: breakdownData.value.map(b => b.staff_count)
    }
  ]
}));

const doughnutChartData = computed(() => ({
  labels: ['Aktif', 'Lulus', 'Keluar'],
  datasets: [
    {
      backgroundColor: ['#3b82f6', '#10b981', '#ef4444'],
      hoverOffset: 4,
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
