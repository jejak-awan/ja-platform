<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-1">
      <h1 class="text-3xl font-bold tracking-tight">
        Halo, {{ dashboardData?.student?.full_name }}! 👋
      </h1>
      <p class="text-muted-foreground">
        Selamat datang di portal siswa Anda. Berikut adalah ringkasan akademis Anda.
      </p>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
      <Card class="bg-primary/5 border-primary/20">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium text-primary">
            Tagihan Belum Lunas
          </CardTitle>
          <LucideIcon
            name="CreditCard"
            class="h-4 w-4 text-primary"
          />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            {{ dashboardData?.summary?.unpaid_bills || 0 }}
          </div>
          <p class="text-xs text-muted-foreground mt-1">
            Segera selesaikan administrasi Anda
          </p>
        </CardContent>
      </Card>

      <Card class="bg-success/5 border-success/20">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium text-success">
            Tingkat Kehadiran
          </CardTitle>
          <LucideIcon
            name="UserCheck"
            class="h-4 w-4 text-success"
          />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            {{ dashboardData?.summary?.attendance_rate || 0 }}%
          </div>
          <div class="h-2 w-full bg-muted rounded-full mt-2 overflow-hidden">
            <div
              class="h-full bg-success transition-all"
              :style="{ width: `${dashboardData?.summary?.attendance_rate || 0}%` }"
            />
          </div>
        </CardContent>
      </Card>

      <Card class="bg-info/5 border-info/20">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium text-info">
            Rata-rata Nilai (GPA)
          </CardTitle>
          <LucideIcon
            name="GraduationCap"
            class="h-4 w-4 text-info"
          />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            {{ dashboardData?.summary?.gpa || 0 }}
          </div>
          <p class="text-xs text-muted-foreground mt-1">
            Berdasarkan hasil ujian terakhir
          </p>
        </CardContent>
      </Card>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
      <Card class="col-span-4">
        <CardHeader>
          <CardTitle>Jadwal Hari Ini</CardTitle>
          <CardDescription>Mata pelajaran yang harus Anda ikuti hari ini.</CardDescription>
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
                {{ item.start_time.substring(0, 5) }}
              </div>
              <div class="flex-1">
                <p class="font-semibold">
                  {{ item.subject?.name }}
                </p>
                <p class="text-xs text-muted-foreground">
                  {{ item.staff?.full_name }} • {{ item.room?.name }}
                </p>
              </div>
            </div>
          </div>
          <div
            v-else
            class="text-center py-12 text-muted-foreground"
          >
            <LucideIcon
              name="Calendar"
              class="w-12 h-12 mx-auto mb-2 opacity-20"
            />
            <p>Tidak ada jadwal untuk hari ini.</p>
          </div>
        </CardContent>
      </Card>

      <Card class="col-span-3">
        <CardHeader>
          <CardTitle>Aksi Cepat</CardTitle>
        </CardHeader>
        <CardContent class="grid gap-4">
          <Button
            variant="outline"
            class="justify-start h-auto py-4"
            @click="$router.push('/school/student/bills')"
          >
            <LucideIcon
              name="Wallet"
              class="w-5 h-5 mr-4 text-primary"
            />
            <div class="text-left">
              <p class="font-semibold">
                Cek Tagihan
              </p>
              <p class="text-[10px] text-muted-foreground font-normal">
                Lihat detil info pembayaran
              </p>
            </div>
          </Button>
          <Button
            variant="outline"
            class="justify-start h-auto py-4"
            @click="$router.push('/school/student/grades')"
          >
            <LucideIcon
              name="FileText"
              class="w-5 h-5 mr-4 text-success"
            />
            <div class="text-left">
              <p class="font-semibold">
                E-Rapor & Nilai
              </p>
              <p class="text-[10px] text-muted-foreground font-normal">
                Pantau perkembangan nilai Anda
              </p>
            </div>
          </Button>
          <Button
            variant="outline"
            class="justify-start h-auto py-4"
            @click="$router.push('/school/student/schedule')"
          >
            <LucideIcon
              name="Clock"
              class="w-5 h-5 mr-4 text-info"
            />
            <div class="text-left">
              <p class="font-semibold">
                Jadwal Mingguan
              </p>
              <p class="text-[10px] text-muted-foreground font-normal">
                Lihat seluruh jadwal pelajaran
              </p>
            </div>
          </Button>
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
} from '@/components/ui';
import api from '@/services/api';
import { parseResponse } from '@/utils/responseParser';

const loading = ref(true);
const dashboardData = ref<any>(null);
const schedule = ref<any[]>([]);

const fetchDashboard = async () => {
  try {
    const response = await api.get('/school/student/dashboard');
    dashboardData.value = parseResponse(response).data;
  } catch (e) {
    console.error(e);
  }
};

const fetchSchedule = async () => {
  try {
    const response = await api.get('/school/student/schedule');
    schedule.value = parseResponse(response).data || [];
  } catch (e) {
    console.error(e);
  }
};

const todaySchedule = computed(() => {
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
  const today = days[new Date().getDay()];
  return schedule.value.filter(item => item.day === today);
});

onMounted(async () => {
  loading.value = true;
  await Promise.all([fetchDashboard(), fetchSchedule()]);
  loading.value = false;
});
</script>
