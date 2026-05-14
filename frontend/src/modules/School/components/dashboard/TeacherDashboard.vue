<template>
  <div class="space-y-8 animate-in fade-in duration-700">
    <!-- Header: Clean & Standard -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2 px-2">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20">
            <LucideIcon
              name="UserCog"
              class="w-5 h-5 text-primary"
            />
          </div>
          <h1 class="text-3xl font-bold tracking-tight text-foreground uppercase">
            {{ t('modules.school.teacher_dashboard.welcome', { name: authStore.user?.name }) }}
          </h1>
        </div>
        <p class="text-muted-foreground text-sm font-medium">
          {{ t('modules.school.teacher_dashboard.subtitle') }}
        </p>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 px-2">
      <Card
        v-for="stat in stats"
        :key="stat.key"
        class="border-border/40 bg-card shadow-none rounded-xl hover:bg-muted/30 transition-all duration-300 group"
      >
        <CardContent class="p-6">
          <div class="flex items-start justify-between">
            <div class="space-y-1">
              <p class="text-xs font-bold text-muted-foreground uppercase tracking-wider">
                {{ t('modules.school.teacher_dashboard.stats.' + stat.key) }}
              </p>
              <p class="text-3xl font-black text-foreground">
                {{ stat.value }}
              </p>
            </div>
            <div :class="['p-2.5 rounded-xl transition-transform group-hover:scale-110', stat.colorClass.replace('bg-', 'bg-').concat('/10'), stat.iconClass]">
              <LucideIcon
                :name="stat.icon"
                class="w-5 h-5"
              />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 px-2">
      <!-- Main Content Area -->
      <div class="lg:col-span-2 space-y-8">
        <!-- Schedule / Active Lessons -->
        <Card class="bg-card border-border/40 shadow-none">
          <CardHeader class="flex flex-row items-center justify-between">
            <div>
              <CardTitle>{{ t('modules.school.teacher_dashboard.sections.schedule.title') }}</CardTitle>
              <CardDescription>{{ t('modules.school.teacher_dashboard.sections.schedule.desc') }}</CardDescription>
            </div>
            <Button
              variant="ghost"
              size="sm"
            >
              {{ t('common.actions.viewAll') }}
            </Button>
          </CardHeader>
          <CardContent class="space-y-4">
            <div
              v-for="schedule in todaySchedules"
              :key="schedule.id"
              class="flex items-center p-4 rounded-xl bg-muted/30 border border-border/20 hover:bg-muted/50 transition-colors cursor-pointer group"
            >
              <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center font-bold text-primary mr-4 group-hover:scale-110 transition-transform text-xs">
                {{ schedule.start_time?.substring(0, 5) }}
              </div>
              <div class="flex-1">
                <h4 class="font-bold">
                  {{ schedule.subject?.name }} - {{ schedule.study_group?.name }}
                </h4>
                <p class="text-xs text-muted-foreground">
                  {{ schedule.room?.name || t('modules.school.academic.room') }} • {{ schedule.room?.location || 'Gedung' }}
                </p>
              </div>
              <Badge
                variant="outline"
                class="bg-primary/5"
              >
                {{ schedule.is_active ? t('common.status.active') : t('common.status.inactive') }}
              </Badge>
            </div>
            <div
              v-if="todaySchedules.length === 0"
              class="py-10 text-center text-muted-foreground italic"
            >
              {{ t('modules.school.teacher_dashboard.sections.schedule.empty') }}
            </div>
          </CardContent>
        </Card>

        <!-- Recent Journals -->
        <Card class="bg-card border-border/40 shadow-none">
          <CardHeader>
            <CardTitle>{{ t('modules.school.teacher_dashboard.sections.recentJournals.title') }}</CardTitle>
            <CardDescription>{{ t('modules.school.teacher_dashboard.sections.recentJournals.desc') }}</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-6">
              <div
                v-for="journal in recentJournals"
                :key="journal.id"
                class="relative pl-6 border-l-2 border-primary/20 last:border-0 pb-6 last:pb-0"
              >
                <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-primary border-4 border-background shadow-sm" />
                <div class="flex justify-between items-start">
                  <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-primary">{{ dayjs(journal.date).format('DD MMM, HH:mm') }}</span>
                    <h5 class="font-bold mt-1">
                      {{ journal.material_summary }}
                    </h5>
                    <p class="text-sm text-muted-foreground mt-1 line-clamp-2">
                      {{ journal.notes }}
                    </p>
                    <p class="text-[10px] text-muted-foreground/60 mt-1 uppercase">
                      {{ journal.schedule?.subject?.name }} • {{ journal.schedule?.study_group?.name }}
                    </p>
                  </div>
                </div>
              </div>
              <div
                v-if="recentJournals.length === 0"
                class="py-10 text-center text-muted-foreground italic"
              >
                {{ t('modules.school.teacher_dashboard.sections.recentJournals.empty') }}
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Sidebar Area -->
      <div class="space-y-8">
        <!-- Attendance Alerts -->
        <Card class="bg-card border-border/40 border-warning/30 shadow-none">
          <CardHeader class="flex flex-row items-center space-x-2">
            <LucideIcon
              name="AlertCircle"
              class="w-5 h-5 text-warning"
            />
            <CardTitle class="text-base">
              {{ t('modules.school.teacher_dashboard.sections.actionRequired.title') }}
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="p-3 rounded-xl bg-warning/10 border border-warning/20">
              <p class="text-xs text-warning-foreground leading-relaxed">
                <span class="font-bold">{{ t('modules.school.teacher_dashboard.sections.actionRequired.attendanceShortage', { count: 5, class: 'Bahasa Inggris (X-C)' }) }}</span>
              </p>
            </div>
            <div class="p-3 rounded-xl bg-primary/10 border border-primary/20">
              <p class="text-xs text-primary leading-relaxed">
                <span class="font-bold">{{ t('modules.school.teacher_dashboard.sections.actionRequired.pendingTasks', { count: 12 }) }}</span>
              </p>
            </div>
          </CardContent>
          <CardFooter>
            <Button
              variant="outline"
              class="w-full text-xs h-9"
            >
              {{ t('modules.school.teacher_dashboard.sections.actionRequired.btnAction') }}
            </Button>
          </CardFooter>
        </Card>

        <!-- Quick Links -->
        <div class="space-y-3">
          <h4 class="text-sm font-bold text-muted-foreground px-2">
            {{ t('modules.school.teacher_dashboard.sections.quickLinks.title') }}
          </h4>
          <Button
            v-for="link in quickLinks"
            :key="link.key"
            variant="ghost"
            class="w-full justify-start h-11 rounded-xl bg-muted/20 hover:bg-muted/40 border border-border/20 group"
          >
            <LucideIcon
              :name="link.icon"
              class="w-4 h-4 mr-3 text-muted-foreground group-hover:text-primary transition-colors"
            />
            {{ t('modules.school.teacher_dashboard.sections.quickLinks.' + link.key) }}
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/modules/Core/stores/auth';
import {
  Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter,
  Button, LucideIcon, Badge
} from '@/shared/components/ui';
import api from '@/engine/api/client';
import { parseResponse } from '@/shared/utils/responseParser';
import dayjs from 'dayjs';

const { t } = useI18n();
const authStore = useAuthStore();
const loading = ref(true);

const stats = ref([
  { key: 'totalClasses', label: 'Total Kelas', value: '0', icon: 'School', colorClass: 'bg-primary', iconClass: 'text-primary' },
  { key: 'activeStudents', label: 'Siswa Aktif', value: '0', icon: 'Users', colorClass: 'bg-emerald-500', iconClass: 'text-emerald-500' },
  { key: 'submittedJournals', label: 'Jurnal Masuk', value: '0', icon: 'BookMarked', colorClass: 'bg-blue-500', iconClass: 'text-blue-500' },
  { key: 'gradingQueue', label: 'Koreksi Nilai', value: '0', icon: 'FileCheck', colorClass: 'bg-orange-500', iconClass: 'text-orange-500' },
]);

const todaySchedules = ref<any[]>([]);
const recentJournals = ref<any[]>([]);

const fetchData = async () => {
    loading.value = true;
    try {
        const [statsRes, schedulesRes, journalsRes] = await Promise.all([
            api.get('/admin/teacher/dashboard-stats'),
            api.get('/admin/teacher/schedules', { params: { day: dayjs().format('dddd') } }),
            api.get('/admin/teacher/recent-journals')
        ]);

        const s = parseResponse(statsRes).data as any;
        if (stats.value[0]) stats.value[0].value = String(s.total_classes || 0);
        if (stats.value[1]) stats.value[1].value = String(s.active_students || 0);
        if (stats.value[2]) stats.value[2].value = String(s.journals_count || 0);
        if (stats.value[3]) stats.value[3].value = String(s.grading_queue || 0);

        todaySchedules.value = parseResponse(schedulesRes).data || [];
        recentJournals.value = parseResponse(journalsRes).data || [];

    } catch (error) {
        console.error('Failed to fetch teacher dashboard data', error);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchData);

const quickLinks = [
  { key: 'inputJournal', label: 'Input Jurnal Harian', icon: 'PenLine' },
  { key: 'classAttendance', label: 'Presensi Kelas', icon: 'UserCheck' },
  { key: 'courseManage', label: 'Manajemen Kursus', icon: 'Layout' },
  { key: 'examResults', label: 'Hasil Ujian', icon: 'GraduationCap' },
];
</script>
