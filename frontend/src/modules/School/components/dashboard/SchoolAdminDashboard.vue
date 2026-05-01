<template>
  <div class="space-y-10 animate-in fade-in slide-in-from-bottom-5 duration-1000 p-2">
    <!-- Admin Hero Section: Clean & Modern -->
    <div class="p-10 rounded-xl bg-card border border-border/50 shadow-sm overflow-hidden relative">
      <div class="relative z-10 flex flex-col lg:flex-row justify-between items-center gap-10">
        <div class="space-y-4 text-center lg:text-left">
          <div class="inline-flex items-center gap-2 px-3 py-1 bg-primary/10 rounded-full border border-primary/20">
            <div class="w-2 h-2 rounded-full bg-primary animate-pulse" />
            <span class="text-[10px] font-bold text-primary/80">{{ $t('features.school.dashboard.v2.hero.badge') }}</span>
          </div>
          <h1 class="text-4xl font-bold tracking-tight leading-tight text-foreground">
            {{ $t('features.school.dashboard.v2.hero.title') }}
          </h1>
          <p class="text-muted-foreground max-w-xl text-lg leading-relaxed">
            {{ $t('features.school.dashboard.v2.hero.subtitle') }}
          </p>
          <div class="flex flex-wrap justify-center lg:justify-start gap-3 pt-2">
            <Button class="rounded-xl h-12 px-8 shadow-sm">
              {{ $t('features.school.dashboard.v2.hero.actions.reports') }}
            </Button>
            <Button
              variant="outline"
              class="rounded-xl h-12 px-6"
            >
              {{ $t('features.school.dashboard.v2.hero.actions.config') }}
            </Button>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div
            v-for="(stat, idx) in adminQuickStats"
            :key="idx" 
            class="p-6 rounded-xl bg-muted/30 border border-border/40 flex flex-col items-center justify-center w-36 h-36 hover:bg-muted/50 transition-all duration-300 cursor-pointer group/stat"
          >
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center mb-3 group-hover/stat:scale-110 transition-transform text-primary">
              <LucideIcon
                :name="stat.icon"
                class="w-5 h-5"
              />
            </div>
            <span class="text-2xl font-bold tracking-tight leading-none text-foreground">{{ stat.value }}</span>
            <span class="text-[10px] font-medium mt-1 text-center text-muted-foreground">{{ stat.label }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
      <!-- Personnel Presence -->
      <Card class="lg:col-span-2 border-border/40 bg-card shadow-none rounded-xl group">
        <CardHeader class="flex flex-row items-center justify-between p-8 pb-4">
          <div>
            <CardTitle class="text-xl font-bold tracking-tight text-foreground/90">
              {{ $t('features.school.dashboard.v2.hr_presence.title') }}
            </CardTitle>
            <CardDescription>
              {{ $t('features.school.dashboard.v2.hr_presence.subtitle') }}
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
                <span class="text-[8px] font-bold opacity-40">{{ $t(`features.school.dashboard.v2.hr_presence.status.${staff.statusKey}`) }}</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <div class="space-y-10">
        <!-- Strategic Alerts -->
        <Card class="border-border/40 bg-destructive/5 dark:bg-destructive/10 border-l-4 border-destructive rounded-xl p-8 shadow-none group">
          <h4 class="text-xs font-bold text-destructive flex items-center gap-3 mb-6">
            <div class="w-2 h-2 rounded-full bg-destructive group-hover:animate-ping" />
            {{ $t('features.school.dashboard.v2.alerts.title') }}
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
import { InstitutionService } from '@/modules/School/services/InstitutionService';
import { parseResponse } from '@/utils/responseParser';

const { t } = useI18n();


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
