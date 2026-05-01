<template>
  <div class="space-y-10 animate-in fade-in slide-in-from-left-5 duration-1000 p-2">
    <!-- Student Affairs Header: Clean -->
    <div class="p-10 rounded-xl bg-card border border-border/50 shadow-sm overflow-hidden relative">
      <div class="relative z-10 flex flex-col lg:flex-row justify-between items-center gap-10">
        <div class="space-y-4 text-center lg:text-left">
          <div class="inline-flex items-center gap-2 px-3 py-1 bg-primary/10 rounded-full border border-primary/20">
            <LucideIcon
              name="Heart"
              class="w-4 h-4 text-primary"
            />
            <span class="text-[10px] font-bold text-primary/80 tracking-wide">{{ $t('features.school.dashboard.v2.student_affairs.hero_badge') }}</span>
          </div>
          <h1 class="text-4xl font-bold tracking-tight leading-tight text-foreground">
            {{ $t('features.school.dashboard.v2.student_affairs.hero_title') }}
          </h1>
          <p class="text-muted-foreground max-w-xl text-lg leading-relaxed">
            {{ $t('features.school.dashboard.v2.student_affairs.hero_subtitle') }}
          </p>
          <div class="flex flex-wrap justify-center lg:justify-start gap-3 pt-2">
            <Button class="rounded-xl h-12 px-8 shadow-sm">
              {{ $t('features.school.dashboard.v2.student_affairs.actions.discipline') }}
            </Button>
            <Button
              variant="outline"
              class="rounded-xl h-12 px-6"
            >
              {{ $t('features.school.dashboard.v2.student_affairs.actions.osis_program') }}
            </Button>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div
            v-for="(stat, idx) in kesiswaanStats"
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
      <!-- Recent Activity Log -->
      <Card class="lg:col-span-2 border-border/40 bg-card shadow-none rounded-xl group">
        <CardHeader class="flex flex-row items-center justify-between p-8 pb-4">
          <div>
            <CardTitle class="text-xl font-bold tracking-tight text-foreground/90 uppercase">
              {{ $t('features.school.dashboard.v2.student_affairs.activity_log') }}
            </CardTitle>
            <CardDescription>
              {{ $t('features.school.dashboard.v2.student_affairs.activity_desc') }}
            </CardDescription>
          </div>
          <Button
            variant="ghost"
            size="icon"
            class="rounded-xl hover:bg-muted"
          >
            <LucideIcon
              name="History"
              class="w-5 h-5 opacity-40"
            />
          </Button>
        </CardHeader>
        <CardContent class="px-8 pb-8">
          <div class="space-y-4">
            <div
              v-for="(log, idx) in activityLogs"
              :key="idx" 
              class="flex items-center gap-4 p-4 rounded-xl bg-muted/20 border border-border/20 hover:border-primary/20 hover:bg-muted/40 transition-all duration-300 group/log cursor-pointer"
            >
              <div :class="`w-12 h-12 rounded-xl flex items-center justify-center text-base font-black ${log.type === 'merit' ? 'bg-success/10 text-success' : 'bg-destructive/10 text-destructive'}`">
                <LucideIcon
                  :name="log.type === 'merit' ? 'Award' : 'Zap'"
                  class="w-5 h-5"
                />
              </div>
              <div class="flex-1 min-w-0">
                <h5 class="font-bold text-foreground truncate text-sm">
                  {{ log.student }}
                </h5>
                <p class="text-[9px] text-muted-foreground uppercase font-black tracking-widest">
                  {{ log.class }} • {{ log.reason }}
                </p>
              </div>
              <div class="flex flex-col items-end gap-1">
                <span :class="`text-sm font-black ${log.type === 'merit' ? 'text-success' : 'text-destructive'}`">{{ log.points > 0 ? '+' : '' }}{{ log.points }} Pts</span>
                <span class="text-[8px] font-bold opacity-40 uppercase">{{ log.time }}</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- OSIS Highlight -->
      <Card class="border-border/40 bg-card text-foreground rounded-xl shadow-none overflow-hidden relative group">
        <CardHeader class="p-8 pb-4 relative z-10">
          <CardTitle class="text-[11px] font-bold text-primary">
            {{ $t('features.school.dashboard.v2.student_affairs.osis_highlight') }}
          </CardTitle>
        </CardHeader>
        <CardContent class="p-8 pt-4 relative z-10 space-y-6">
          <div class="p-5 rounded-xl bg-muted/30 border border-border/20">
            <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-2">
              {{ $t('features.school.dashboard.v2.student_affairs.on_going_program') }}
            </p>
            <div class="flex justify-between items-end mb-2">
              <h3 class="text-2xl font-bold">
                Bakti Sosial
              </h3>
              <span class="text-xs font-bold">65%</span>
            </div>
            <div class="h-1.5 bg-muted rounded-full overflow-hidden">
              <div
                class="h-full bg-primary rounded-full"
                style="width: 65%"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div class="p-4 rounded-xl bg-muted/20 border border-border/20 text-center">
              <p class="text-xl font-black">12</p>
              <p class="text-[8px] font-black uppercase text-muted-foreground">
                {{ $t('features.school.dashboard.v2.student_affairs.stats.osis_candidates') }}
              </p>
            </div>
            <div class="p-4 rounded-xl bg-muted/20 border border-border/20 text-center">
              <p class="text-xl font-black">4</p>
              <p class="text-[8px] font-black uppercase text-muted-foreground">
                {{ $t('features.school.dashboard.v2.student_affairs.stats.upcoming_events') }}
              </p>
            </div>
          </div>
        </CardContent>
        <CardFooter class="p-8 pt-0 relative z-10">
          <Button
            variant="outline"
            class="w-full text-[10px] font-black uppercase tracking-widest h-12 rounded-xl bg-muted/30 hover:bg-muted/50 border-border/40"
          >
            {{ $t('features.school.dashboard.v2.student_affairs.actions.details') }}
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

interface KesiswaanStat {
  label: string;
  value: string;
  icon: string;
}

interface ActivityLog {
  student: string;
  class: string;
  reason: string;
  points: number;
  type: 'merit' | 'infraction';
  time: string;
}

const kesiswaanStats: KesiswaanStat[] = [
  { label: 'Total Siswa', value: '1.240', icon: 'Users' },
  { label: 'Ketidakhadiran', value: '1.2%', icon: 'UserMinus' },
  { label: 'Poin Pelanggaran', value: '450', icon: 'ZapOff' },
  { label: 'Ekstrakurikuler', value: '18', icon: 'Activity' },
];

const activityLogs: ActivityLog[] = [
    { student: 'Ahmad Faisal', class: 'XII TKJ 2', reason: 'Terlambat > 15 Menit', points: -10, type: 'infraction', time: '07:45' },
    { student: 'Siti Rahmawati', class: 'X RPL 1', reason: 'Juara 1 LKS Nasional', points: 100, type: 'merit', time: '09:20' },
    { student: 'Bagas Saputra', class: 'XI OTKP 3', reason: 'Tidak Memakai Atribut', points: -5, type: 'infraction', time: '07:30' },
];
</script>
