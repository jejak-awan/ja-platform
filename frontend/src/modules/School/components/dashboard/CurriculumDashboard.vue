<template>
  <div class="space-y-10 animate-in fade-in slide-in-from-right-5 duration-1000 p-2">
    <!-- Curriculum Header: Clean & Modern -->
    <div class="p-10 rounded-xl bg-card border border-border/50 shadow-sm overflow-hidden relative">
      <div class="relative z-10 flex flex-col lg:flex-row justify-between items-center gap-10">
        <div class="space-y-4 text-center lg:text-left">
          <div class="inline-flex items-center gap-2 px-3 py-1 bg-primary/10 rounded-full border border-primary/20">
            <LucideIcon
              name="BookOpen"
              class="w-4 h-4 text-primary"
            />
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary opacity-90">{{ $t('features.school.dashboard.v2.academic.hero_badge') }}</span>
          </div>
          <h1 class="text-4xl font-black tracking-tight leading-tight text-foreground">
            {{ $t('features.school.dashboard.v2.academic.hero_title') }}
          </h1>
          <p class="text-muted-foreground max-w-xl text-lg font-medium leading-relaxed italic">
            {{ $t('features.school.dashboard.v2.academic.hero_subtitle') }}
          </p>
          <div class="flex flex-wrap justify-center lg:justify-start gap-3 pt-2">
            <Button class="rounded-xl h-12 px-8 shadow-sm">
              {{ $t('features.school.dashboard.v2.academic.actions.schedule') }}
            </Button>
            <Button
              variant="outline"
              class="rounded-xl h-12 px-6"
            >
              {{ $t('features.school.dashboard.v2.academic.actions.bank') }}
            </Button>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div
            v-for="(stat, idx) in curriculumStats"
            :key="idx" 
            class="p-6 rounded-xl bg-muted/30 border border-border/40 flex flex-col items-center justify-center w-36 h-36 hover:bg-muted/50 transition-all duration-300 cursor-pointer group/stat"
          >
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center mb-3 group-hover/stat:scale-110 transition-transform text-primary">
              <LucideIcon
                :name="stat.icon"
                class="w-5 h-5"
              />
            </div>
            <span class="text-2xl font-black tracking-tight leading-none text-foreground">{{ stat.value }}</span>
            <span class="text-[9px] font-black uppercase tracking-[0.2em] opacity-50 mt-2 text-center text-muted-foreground">{{ stat.label }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
      <!-- Subject Distribution -->
      <!-- Subject Distribution -->
      <Card class="lg:col-span-2 border-border/40 bg-card shadow-none rounded-xl group">
        <CardHeader class="flex flex-row items-center justify-between p-8 pb-4">
          <div>
            <CardTitle class="text-xl font-black tracking-tight text-foreground/90 uppercase">
              {{ $t('features.school.dashboard.v2.academic.popular_subjects') }}
            </CardTitle>
            <CardDescription class="font-medium italic">
              {{ $t('features.school.dashboard.v2.academic.popular_desc') }}
            </CardDescription>
          </div>
        </CardHeader>
        <CardContent class="px-8 pb-8">
          <div class="space-y-6">
            <div
              v-for="(subject, idx) in subjects"
              :key="idx"
              class="space-y-2"
            >
              <div class="flex justify-between items-end text-xs font-bold">
                <div class="flex items-center gap-3">
                  <div class="w-1.5 h-6 bg-primary rounded-full" />
                  <div>
                    <h5 class="font-black text-foreground">
                      {{ subject.name }}
                    </h5>
                    <p class="text-[8px] font-bold text-muted-foreground uppercase">
                      {{ subject.teacher }}
                    </p>
                  </div>
                </div>
                <span class="text-foreground">{{ subject.avg }}%</span>
              </div>
              <div class="h-1.5 bg-muted rounded-full overflow-hidden">
                <div
                  class="h-full bg-primary rounded-full transition-all duration-1000"
                  :style="{ width: subject.avg + '%' }"
                />
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Academic Events -->
      <Card class="border-border/40 bg-card text-foreground rounded-xl shadow-none overflow-hidden relative group">
        <CardHeader class="p-8 pb-4 relative z-10">
          <CardTitle class="text-[10px] font-black uppercase tracking-[0.3em] text-primary">
            {{ $t('features.school.dashboard.v2.academic.agenda') }}
          </CardTitle>
        </CardHeader>
        <CardContent class="p-8 pt-4 relative z-10">
          <div class="space-y-6">
            <div
              v-for="event in events"
              :key="event.title"
              class="flex gap-4"
            >
              <div class="w-12 h-14 rounded-xl bg-primary/10 text-primary flex flex-col items-center justify-center shrink-0 border border-primary/20">
                <span class="text-lg font-black leading-none">{{ event.day }}</span>
                <span class="text-[8px] font-bold uppercase tracking-widest opacity-60">{{ event.month }}</span>
              </div>
              <div>
                <h5 class="font-bold text-sm leading-snug">
                  {{ event.title }}
                </h5>
                <p class="text-[9px] text-muted-foreground font-medium italic mt-1">
                  {{ event.location }}
                </p>
              </div>
            </div>
          </div>
        </CardContent>
        <CardFooter class="p-8 pt-0 relative z-10">
          <Button
            variant="outline"
            class="w-full text-[10px] font-black uppercase tracking-widest h-12 rounded-xl bg-muted/30 hover:bg-muted/50 border-border/40"
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
