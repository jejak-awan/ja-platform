<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-1">
      <h1 class="text-3xl font-bold tracking-tight">
        Jadwal Mingguan
      </h1>
      <p class="text-muted-foreground">
        Lihat seluruh jadwal mata pelajaran Anda dalam seminggu.
      </p>
    </div>

    <div
      v-if="loading"
      class="grid grid-cols-1 md:grid-cols-5 gap-4"
    >
      <SkeletonLoader
        v-for="i in 5"
        :key="i"
        class="h-64 w-full"
      />
    </div>
    <div
      v-else
      class="grid grid-cols-1 md:grid-cols-5 gap-4 items-start"
    >
      <Card
        v-for="day in days"
        :key="day"
        :class="{'border-primary bg-primary/5': isToday(day)}"
      >
        <CardHeader class="p-4 bg-muted/50 border-b">
          <CardTitle class="text-sm font-bold text-center">
            {{ day }}
          </CardTitle>
        </CardHeader>
        <CardContent class="p-2 space-y-2">
          <div
            v-for="item in getScheduleForDay(day)"
            :key="item.id"
            class="p-3 border rounded text-xs hover:bg-muted/50 transition-colors"
          >
            <p class="font-bold text-primary mb-1">
              {{ item.start_time.substring(0, 5) }} - {{ item.end_time.substring(0, 5) }}
            </p>
            <p class="font-semibold line-clamp-1">
              {{ item.subject?.name }}
            </p>
            <p class="text-[10px] text-muted-foreground mt-1 line-clamp-1">
              {{ item.staff?.full_name }}
            </p>
            <div class="flex items-center gap-1 mt-1 font-medium">
              <LucideIcon
                name="MapPin"
                class="w-2.5 h-2.5"
              />
              <span>{{ item.room?.name || '-' }}</span>
            </div>
          </div>
          <div
            v-if="getScheduleForDay(day).length === 0"
            class="py-10 text-center text-muted-foreground italic text-[10px]"
          >
            Tidak ada jadwal
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Card, CardHeader, CardTitle, CardContent, SkeletonLoader, LucideIcon } from '@/shared/components/ui';
import api from '@/engine/api/client';
import { parseResponse } from '@/shared/utils/responseParser';

const loading = ref(true);
const schedule = ref<any[]>([]);
const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

const fetchSchedule = async () => {
  try {
    const response = await api.get('/school/student/schedule');
    schedule.value = parseResponse(response).data || [];
  } catch (e) {
    console.error(e);
  }
};

const getScheduleForDay = (day: string) => {
  return schedule.value
    .filter(item => item.day === day)
    .sort((a, b) => a.start_time.localeCompare(b.start_time));
};

const isToday = (day: string) => {
  const dow = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
  return dow[new Date().getDay()] === day;
};

onMounted(async () => {
  loading.value = true;
  await fetchSchedule();
  loading.value = false;
});
</script>
