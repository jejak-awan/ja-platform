<template>
  <div class="space-y-10 animate-in fade-in slide-in-from-right-5 duration-1000 p-2">
    <!-- Curriculum Hero -->
    <div class="relative overflow-hidden group">
      <div class="absolute inset-0 bg-gradient-to-br from-emerald-600 via-teal-700 to-cyan-800 opacity-95 rounded-[3rem] shadow-2xl shadow-emerald-500/20" />
      <div class="absolute -left-20 -bottom-20 w-96 h-96 bg-white/10 rounded-full blur-[80px] group-hover:bg-white/15 transition-all duration-700" />
      
      <div class="relative z-10 p-10 flex flex-col lg:flex-row justify-between items-center gap-10 text-white">
        <div class="space-y-6 text-center lg:text-left">
          <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-md rounded-full border border-white/20">
            <LucideIcon
              name="BookOpen"
              class="w-4 h-4 text-emerald-300"
            />
            <span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-90">{{ $t('features.school.dashboard.v2.academic.hero_badge') }}</span>
          </div>
          <h1 class="text-5xl font-black tracking-tighter leading-none">
            {{ $t('features.school.dashboard.v2.academic.hero_title') }}
          </h1>
          <p class="text-white/60 max-w-xl text-lg font-medium leading-relaxed italic">
            {{ $t('features.school.dashboard.v2.academic.hero_subtitle') }}
          </p>
          <div class="flex flex-wrap justify-center lg:justify-start gap-4 pt-4">
            <Button class="bg-white text-emerald-700 hover:bg-white/90 font-black rounded-2xl h-14 px-10 shadow-xl shadow-white/10 transition-transform active:scale-95">
              {{ $t('features.school.dashboard.v2.academic.actions.schedule') }}
            </Button>
            <Button
              variant="ghost"
              class="text-white hover:bg-white/10 border border-white/10 font-bold rounded-2xl h-14 px-8"
            >
              {{ $t('features.school.dashboard.v2.academic.actions.bank') }}
            </Button>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div
            v-for="(stat, idx) in curriculumStats"
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
      <!-- Subject Distribution -->
      <Card class="lg:col-span-2 border-none bg-white/40 dark:bg-slate-900/40 backdrop-blur-2xl rounded-[3rem] shadow-sm hover:shadow-2xl transition-all duration-700 group">
        <CardHeader class="flex flex-row items-center justify-between p-10 pb-4">
          <div>
            <CardTitle class="text-2xl font-black tracking-tight text-foreground/90 uppercase">
              {{ $t('features.school.dashboard.v2.academic.popular_subjects') }}
            </CardTitle>
            <CardDescription class="font-medium italic">
              {{ $t('features.school.dashboard.v2.academic.popular_desc') }}
            </CardDescription>
          </div>
        </CardHeader>
        <CardContent class="px-10 pb-10">
          <div class="space-y-6">
            <div
              v-for="(subject, idx) in subjects"
              :key="idx"
              class="space-y-2"
            >
              <div class="flex justify-between items-end">
                <div class="flex items-center gap-3">
                  <div class="w-2 h-8 bg-emerald-500 rounded-full" />
                  <div>
                    <h5 class="font-black text-sm">
                      {{ subject.name }}
                    </h5>
                    <p class="text-[9px] font-bold text-muted-foreground uppercase">
                      {{ subject.teacher }}
                    </p>
                  </div>
                </div>
                <span class="text-sm font-black">{{ subject.avg }}%</span>
              </div>
              <div class="h-2 bg-muted rounded-full overflow-hidden">
                <div
                  class="h-full bg-emerald-500 rounded-full transition-all duration-1000"
                  :style="{ width: subject.avg + '%' }"
                />
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Academic Events -->
      <Card class="border-none bg-emerald-600 text-white rounded-[3rem] shadow-2xl shadow-emerald-600/20 overflow-hidden relative group">
        <div class="absolute right-[-10%] top-[-10%] opacity-10 group-hover:-rotate-12 transition-transform duration-700">
          <LucideIcon
            name="Calendar"
            class="w-48 h-48"
          />
        </div>
        <CardHeader class="p-8 pb-4 relative z-10">
          <CardTitle class="text-xs font-black uppercase tracking-[0.3em] opacity-60">
            {{ $t('features.school.dashboard.v2.academic.agenda') }}
          </CardTitle>
        </CardHeader>
        <CardContent class="p-8 relative z-10">
          <div class="space-y-8">
            <div
              v-for="event in events"
              :key="event.title"
              class="flex gap-4"
            >
              <div class="w-12 h-14 rounded-2xl bg-white/10 flex flex-col items-center justify-center shrink-0">
                <span class="text-lg font-black leading-none">{{ event.day }}</span>
                <span class="text-[8px] font-bold uppercase tracking-widest opacity-60">{{ event.month }}</span>
              </div>
              <div>
                <h5 class="font-black text-sm leading-snug">
                  {{ event.title }}
                </h5>
                <p class="text-[10px] opacity-60 font-medium italic mt-1">
                  {{ event.location }}
                </p>
              </div>
            </div>
          </div>
        </CardContent>
        <CardFooter class="p-8 pt-0 relative z-10">
          <Button
            variant="ghost"
            class="w-full text-xs font-black uppercase tracking-widest h-14 rounded-2xl bg-white/10 hover:bg-white/20 hover:text-white border-0"
          >
            {{ $t('features.school.dashboard.v2.academic.actions.calendar') }}
            <LucideIcon
              name="ArrowRight"
              class="w-4 h-4 ml-2"
            />
          </Button>
        </CardFooter>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter,
  Button, LucideIcon
} from '@/components/ui';

interface CurriculumStat {
  label: string;
  value: string;
  icon: string;
}

interface Subject {
  name: string;
  teacher: string;
  avg: number;
}

interface Event {
  day: string;
  month: string;
  title: string;
  location: string;
}

const curriculumStats: CurriculumStat[] = [
  { label: 'Mata Pelajaran', value: '42', icon: 'Book' },
  { label: 'Rombel Aktif', value: '32', icon: 'Layers' },
  { label: 'Total Guru', value: '78', icon: 'UserCheck' },
  { label: 'Nilai Rataan', value: '82.5', icon: 'TrendingUp' },
];

const subjects: Subject[] = [
    { name: 'Pemrograman Web (RPL)', teacher: 'Dedi Kurnia, M.Kom', avg: 88 },
    { name: 'Basis Data Terdistribusi', teacher: 'Rina Maryana, S.T', avg: 76 },
    { name: 'Fisika Terapan', teacher: 'Agus Santoso, M.Pd', avg: 65 },
    { name: 'Bahasa Inggris Bisnis', teacher: 'Linda Sari, M.Hum', avg: 92 },
];

const events: Event[] = [
    { day: '24', month: 'MAR', title: 'Ujian Tengah Semester Ganjil', location: 'Seluruh Ruang Kelas' },
    { day: '02', month: 'APR', title: 'Workshop Kurikulum Merdeka', location: 'Aula Utama' },
    { day: '15', month: 'APR', title: 'Rapat Pleno Kenaikan Kelas', location: 'Ruang Multimedia' },
];
</script>
