<template>
  <div class="space-y-8 animate-in fade-in duration-700">
    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-4">
      <div class="w-12 h-12 border-4 border-primary/20 border-t-primary rounded-full animate-spin" />
      <p class="text-muted-foreground animate-pulse font-medium">Memuat data dashboard...</p>
    </div>

    <template v-else>
      <!-- Header: Clean & Standard -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2 px-2">
        <div>
          <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20">
              <LucideIcon
                name="User"
                class="w-5 h-5 text-primary"
              />
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-foreground uppercase">
              {{ authStore.user?.name }}
            </h1>
          </div>
          <p class="text-muted-foreground text-sm font-medium">
            NISN: {{ dashboardData?.student?.nisn || '-' }} • NIS: {{ dashboardData?.student?.nis || '-' }}
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Badge
            variant="outline"
            class="bg-success/5 text-success border-success/20 rounded-xl px-4 py-1 text-[10px] font-black uppercase"
          >
            Status: Aktif
          </Badge>
          <Badge
            variant="outline"
            class="bg-primary/5 text-primary border-primary/20 rounded-xl px-4 py-1 text-[10px] font-black uppercase"
          >
            IPK: {{ dashboardData?.summary?.gpa || '0.00' }}
          </Badge>
        </div>
      </div>

      <!-- Learning Progress & Quick Stats -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 px-2">
        <Card class="lg:col-span-2 bg-card border-border/40 shadow-none overflow-hidden rounded-xl">
          <CardHeader class="pb-2">
            <CardTitle>{{ t('Lanjutkan Belajar') }}</CardTitle>
            <CardDescription>{{ t('Materi terakhir yang Anda pelajari.') }}</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="p-6 rounded-xl bg-primary text-primary-foreground shadow-sm relative group cursor-pointer overflow-hidden">
              <div class="relative z-10">
                <span class="text-[10px] font-bold uppercase tracking-widest opacity-80">RPL • Pengembangan Perangkat Lunak</span>
                <h3 class="text-2xl font-bold mt-1">
                  Pemrograman Berorientasi Objek
                </h3>
                <div class="flex items-center mt-4 gap-4">
                  <div class="flex-1">
                    <div class="flex justify-between text-[10px] mb-1 font-bold">
                      <span>PROGRES MATERI</span>
                      <span>75%</span>
                    </div>
                    <div class="h-1.5 bg-primary-foreground/20 rounded-full">
                      <div class="h-full bg-primary-foreground rounded-full w-3/4" />
                    </div>
                  </div>
                  <Button
                    size="icon"
                    class="bg-primary-foreground text-primary hover:bg-primary-foreground/90 rounded-full h-12 w-12 shadow-md"
                  >
                    <LucideIcon
                      name="Play"
                      class="w-5 h-5 fill-current"
                    />
                  </Button>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Grade Overview Mini -->
        <Card class="bg-card border-border/40 shadow-none rounded-xl">
          <CardHeader class="pb-2 text-center">
            <CardTitle class="text-sm text-muted-foreground font-medium">
              Kehadiran (Absensi)
            </CardTitle>
          </CardHeader>
          <CardContent class="flex flex-col items-center justify-center py-6">
            <div class="w-32 h-32 rounded-full border-[10px] border-primary/10 flex items-center justify-center relative">
              <svg class="absolute inset-0 w-full h-full -rotate-90">
                <circle
                  cx="64"
                  cy="64"
                  r="54"
                  fill="transparent"
                  stroke="currentColor"
                  stroke-width="10"
                  class="text-primary"
                  :stroke-dasharray="339.292"
                  :stroke-dashoffset="339.292 * (1 - (dashboardData?.summary?.attendance_rate || 0) / 100)"
                />
              </svg>
              <div class="text-center">
                <span class="text-4xl font-black text-primary">{{ dashboardData?.summary?.attendance_rate || 0 }}%</span>
                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                  Kehadiran
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </template>

    <!-- Lists Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Upcoming Tasks -->
      <div class="space-y-4">
        <h3 class="text-xl font-bold flex items-center gap-2 px-2">
          <LucideIcon
            name="Clock"
            class="w-5 h-5 text-primary"
          />
          Tugas Mendatang
        </h3>
        <div class="space-y-3">
          <div
            v-for="i in 3"
            :key="i"
            class="p-4 rounded-xl bg-card border border-border/40 hover:bg-accent/50 transition-all shadow-sm cursor-pointer"
          >
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                <LucideIcon
                  name="FileText"
                  class="w-5 h-5"
                />
              </div>
              <div class="flex-1">
                <h4 class="font-bold">
                  Analisis Algoritma Sorting
                </h4>
                <p class="text-xs text-muted-foreground">
                  Basis Data • Tenggat: Besok, 23:59
                </p>
              </div>
              <LucideIcon
                name="ChevronRight"
                class="w-4 h-4 text-muted-foreground"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- New Announcements -->
      <div class="space-y-4">
        <h3 class="text-xl font-bold flex items-center gap-2 px-2">
          <LucideIcon
            name="Bell"
            class="w-5 h-5 text-primary"
          />
          Pengumuman Baru
        </h3>
        <div class="space-y-3">
          <div
            v-for="i in 2"
            :key="i"
            class="p-4 rounded-xl bg-primary/5 border border-primary/10 relative overflow-hidden"
          >
            <div class="flex gap-4 relative z-10">
              <div class="w-10 h-10 rounded-full bg-indigo-500/10 flex items-center justify-center text-indigo-500 shrink-0">
                <LucideIcon
                  name="Megaphone"
                  class="w-5 h-5"
                />
              </div>
              <div>
                <h4 class="font-bold text-sm">
                  Libur Awal Ramadhan 1447H
                </h4>
                <p class="text-xs text-muted-foreground mt-1">
                  Diberitahukan kepada seluruh siswa bahwa kegiatan belajar mengajar akan diliburkan mulai tanggal...
                </p>
                <span class="text-[10px] text-indigo-400 font-bold mt-2 inline-block">2 JAM YANG LALU</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/modules/System/stores/auth';
import api from '@/engine/api/client';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import {
  Card, CardContent, CardHeader, CardTitle, CardDescription,
  Button, LucideIcon, Badge
} from '@/shared/components/ui';

const { t } = useI18n();
const authStore = useAuthStore();
const loading = ref(true);
const dashboardData = ref<any>(null);

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await api.get('/student/dashboard');
        dashboardData.value = parseSingleResponse(response);
    } catch (error) {
        console.error('Failed to fetch student dashboard data', error);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchData);
</script>
