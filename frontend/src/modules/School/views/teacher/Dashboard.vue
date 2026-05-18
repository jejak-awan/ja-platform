<template>
  <div class="space-y-6 animate-in fade-in duration-700">
    <div class="flex flex-col gap-1">
      <h1 class="text-3xl font-bold tracking-tight">
        Portal Guru
      </h1>
      <p class="text-muted-foreground">
        Ringkasan aktivitas mengajar dan statistik akademis Anda.
      </p>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
      <Card class="bg-primary/5 border-primary/20">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium text-primary">
            Total Kelas
          </CardTitle>
          <LucideIcon
            name="Users"
            class="h-4 w-4 text-primary"
          />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            {{ stats?.total_classes || 0 }}
          </div>
        </CardContent>
      </Card>

      <Card class="bg-success/5 border-success/20">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium text-success">
            Jurnal Terisi
          </CardTitle>
          <LucideIcon
            name="BookOpenCheck"
            class="h-4 w-4 text-success"
          />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            {{ stats?.journals_count || 0 }}
          </div>
        </CardContent>
      </Card>

      <Card class="bg-info/5 border-info/20">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium text-info">
            Siswa Aktif
          </CardTitle>
          <LucideIcon
            name="GraduationCap"
            class="h-4 w-4 text-info"
          />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            {{ stats?.active_students || 0 }}
          </div>
        </CardContent>
      </Card>

      <Card class="bg-warning/5 border-warning/20">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium text-warning">
            Antrean Penilaian
          </CardTitle>
          <LucideIcon
            name="FileEdit"
            class="h-4 w-4 text-warning"
          />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            {{ stats?.grading_queue || 0 }}
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-7">
      <Card class="col-span-4">
        <CardHeader>
          <CardTitle>Jadwal Mengajar Hari Ini</CardTitle>
          <CardDescription>Agenda kelas Anda untuk hari ini.</CardDescription>
        </CardHeader>
        <CardContent>
          <div
            v-if="loading"
            class="space-y-4"
          >
            <SkeletonLoader
              v-for="i in 3"
              :key="i"
              class="h-16 w-full"
            />
          </div>
          <div
            v-else-if="todaySchedule.length > 0"
            class="space-y-4"
          >
            <div
              v-for="item in todaySchedule"
              :key="item.id"
              class="flex items-center gap-4 p-4 border rounded-lg hover:bg-muted/50 transition-colors"
            >
              <div class="bg-primary/10 p-2 rounded text-primary font-bold">
                {{ item.start_time?.substring(0, 5) }} - {{ item.end_time?.substring(0, 5) }}
              </div>
              <div class="flex-1">
                <p class="font-semibold">
                  {{ item.subject?.name }}
                </p>
                <p class="text-xs text-muted-foreground">
                  Kelas {{ item.studyGroup?.name }} • Ruang {{ item.room?.name || '-' }}
                </p>
              </div>
              <div>
                <Button
                  variant="outline"
                  size="sm"
                  @click="$router.push('/school/teacher/schedule')"
                >
                  Isi Jurnal
                </Button>
              </div>
            </div>
          </div>
          <div
            v-else
            class="text-center py-12 text-muted-foreground"
          >
            <LucideIcon
              name="Coffee"
              class="w-12 h-12 mx-auto mb-2 opacity-20"
            />
            <p>Tidak ada jadwal mengajar hari ini.</p>
          </div>
        </CardContent>
      </Card>

      <Card class="col-span-3">
        <CardHeader>
          <CardTitle>Jurnal Terbaru</CardTitle>
          <CardDescription>Catatan pengajaran terakhir divalidasi sistem.</CardDescription>
        </CardHeader>
        <CardContent>
          <div
            v-if="loading"
            class="space-y-4"
          >
            <SkeletonLoader
              v-for="i in 3"
              :key="i"
              class="h-12 w-full"
            />
          </div>
          <div
            v-else-if="recentJournals.length > 0"
            class="space-y-4"
          >
            <div
              v-for="journal in recentJournals"
              :key="journal.id"
              class="flex flex-col gap-1 pb-3 border-b last:border-0"
            >
              <div class="flex justify-between items-start">
                <p class="font-medium text-sm">
                  {{ journal.schedule?.subject?.name }}
                </p>
                <span class="text-[10px] text-muted-foreground">{{ new Date(journal.date).toLocaleDateString() }}</span>
              </div>
              <p class="text-xs text-muted-foreground line-clamp-2">
                {{ journal.topic || journal.notes }}
              </p>
            </div>
          </div>
          <div
            v-else
            class="text-center py-6 text-muted-foreground"
          >
            <p class="text-sm">
              Belum ada jurnal yang diinput.
            </p>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import {
  Card, CardHeader, CardTitle, CardContent, CardDescription,
  Button, LucideIcon, SkeletonLoader
} from '@/shared/components/ui';
import api from '@/engine/api/client';
import { parseResponse } from '@/shared/utils/responseParser';
import { useToast } from '@/shared/composables/useToast';

const toast = useToast();
const loading = ref(true);
const stats = ref<any>(null);
const schedule = ref<any[]>([]);
const recentJournals = ref<any[]>([]);

const todaySchedule = computed(() => {
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
  const today = days[new Date().getDay()];
  return schedule.value.filter(item => item.day === today);
});

const fetchData = async () => {
  try {
    const [statsRes, schedRes, journalsRes] = await Promise.all([
        api.get('/manage/school/teacher/dashboard-stats'),
        api.get('/manage/school/teacher/schedules'),
        api.get('/manage/school/teacher/recent-journals')
    ]);
    
    stats.value = parseResponse(statsRes).data;
    schedule.value = parseResponse(schedRes).data || [];
    recentJournals.value = parseResponse(journalsRes).data || [];
  } catch (e: any) {
    console.error(e);
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>
