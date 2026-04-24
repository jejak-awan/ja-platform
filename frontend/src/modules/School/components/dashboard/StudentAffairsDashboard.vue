<template>
  <div class="space-y-10 animate-in fade-in slide-in-from-left-5 duration-1000 p-2">
    <!-- Student Affairs Hero -->
    <div class="relative overflow-hidden group">
      <div class="absolute inset-0 bg-gradient-to-br from-rose-600 via-pink-700 to-purple-800 opacity-95 rounded-[3rem] shadow-2xl shadow-rose-500/20" />
      <div class="absolute -right-20 -top-20 w-96 h-96 bg-white/10 rounded-full blur-[80px] group-hover:bg-white/15 transition-all duration-700" />
      
      <div class="relative z-10 p-10 flex flex-col lg:flex-row justify-between items-center gap-10 text-white">
        <div class="space-y-6 text-center lg:text-left">
          <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-md rounded-full border border-white/20">
            <LucideIcon
              name="Heart"
              class="w-4 h-4 text-rose-300"
            />
            <span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-90">{{ $t('features.school.dashboard.v2.student_affairs.hero_badge') }}</span>
          </div>
          <h1 class="text-5xl font-black tracking-tighter leading-none">
            {{ $t('features.school.dashboard.v2.student_affairs.hero_title') }}
          </h1>
          <p class="text-white/60 max-w-xl text-lg font-medium leading-relaxed italic">
            {{ $t('features.school.dashboard.v2.student_affairs.hero_subtitle') }}
          </p>
          <div class="flex flex-wrap justify-center lg:justify-start gap-4 pt-4">
            <Button class="bg-white text-rose-700 hover:bg-white/90 font-black rounded-2xl h-14 px-10 shadow-xl shadow-white/10 transition-transform active:scale-95">
              {{ $t('features.school.dashboard.v2.student_affairs.actions.discipline') }}
            </Button>
            <Button
              variant="ghost"
              class="text-white hover:bg-white/10 border border-white/10 font-bold rounded-2xl h-14 px-8"
            >
              {{ $t('features.school.dashboard.v2.student_affairs.actions.osis_program') }}
            </Button>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div
            v-for="(stat, idx) in kesiswaanStats"
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
      <!-- Recent Infractions/Merits -->
      <Card class="lg:col-span-2 border-none bg-white/40 dark:bg-slate-900/40 backdrop-blur-2xl rounded-[3rem] shadow-sm hover:shadow-2xl transition-all duration-700 group">
        <CardHeader class="flex flex-row items-center justify-between p-10 pb-4">
          <div>
            <CardTitle class="text-2xl font-black tracking-tight text-foreground/90 uppercase">
              {{ $t('features.school.dashboard.v2.student_affairs.activity_log') }}
            </CardTitle>
            <CardDescription class="font-medium italic">
              {{ $t('features.school.dashboard.v2.student_affairs.activity_desc') }}
            </CardDescription>
          </div>
          <Button
            variant="ghost"
            size="icon"
            class="rounded-2xl hover:bg-white/50"
          >
            <LucideIcon
              name="History"
              class="w-5 h-5 opacity-40"
            />
          </Button>
        </CardHeader>
        <CardContent class="px-10 pb-10">
          <div class="space-y-4">
            <div
              v-for="(log, idx) in activityLogs"
              :key="idx" 
              class="flex items-center gap-5 p-5 rounded-[2rem] bg-white/50 dark:bg-slate-800/50 border border-transparent hover:border-rose-500/20 hover:bg-white transition-all duration-500 group/log cursor-pointer"
            >
              <div :class="`w-14 h-14 rounded-2xl flex items-center justify-center text-lg font-black ${log.type === 'merit' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-rose-500/10 text-rose-600'}`">
                <LucideIcon
                  :name="log.type === 'merit' ? 'Award' : 'Zap'"
                  class="w-6 h-6"
                />
              </div>
              <div class="flex-1 min-w-0">
                <h5 class="font-black text-foreground truncate">
                  {{ log.student }}
                </h5>
                <p class="text-[10px] text-muted-foreground uppercase font-black tracking-widest">
                  {{ log.class }} • {{ log.reason }}
                </p>
              </div>
              <div class="flex flex-col items-end gap-1">
                <span :class="`text-sm font-black ${log.type === 'merit' ? 'text-emerald-600' : 'text-rose-600'}`">{{ log.points > 0 ? '+' : '' }}{{ log.points }} Pts</span>
                <span class="text-[9px] font-bold opacity-30 uppercase">{{ log.time }}</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- OSIS Highlight -->
      <Card class="border-none bg-gradient-to-br from-rose-600 to-pink-600 text-white rounded-[3rem] shadow-2xl shadow-rose-600/20 overflow-hidden relative group">
        <div class="absolute right-[-10%] bottom-[-10%] opacity-10 group-hover:scale-110 transition-transform duration-700">
          <LucideIcon
            name="Users"
            class="w-48 h-48"
          />
        </div>
        <CardHeader class="p-8 pb-4 relative z-10">
          <CardTitle class="text-xs font-black uppercase tracking-[0.3em] opacity-60">
            {{ $t('features.school.dashboard.v2.student_affairs.osis_highlight') }}
          </CardTitle>
        </CardHeader>
        <CardContent class="p-8 relative z-10 space-y-8">
          <div class="p-6 rounded-3xl bg-white/10 backdrop-blur-md border border-white/20">
            <p class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-2">
              {{ $t('features.school.dashboard.v2.student_affairs.on_going_program') }}
            </p>
            <div class="flex justify-between items-end mb-2">
              <h3 class="text-3xl font-black">
                Bakhti Sosial
              </h3>
              <span class="text-sm font-bold">65%</span>
            </div>
            <div class="h-1.5 bg-white/20 rounded-full overflow-hidden">
              <div
                class="h-full bg-white rounded-full"
                style="width: 65%"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="p-4 rounded-2xl bg-white/5 border border-white/10 text-center">
              <p class="text-2xl font-black">
                12
              </p>
              <p class="text-[8px] font-black uppercase opacity-60">
                {{ $t('features.school.dashboard.v2.student_affairs.stats.osis_candidates') }}
              </p>
            </div>
            <div class="p-4 rounded-2xl bg-white/5 border border-white/10 text-center">
              <p class="text-2xl font-black">
                4
              </p>
              <p class="text-[8px] font-black uppercase opacity-60">
                {{ $t('features.school.dashboard.v2.student_affairs.stats.upcoming_events') }}
              </p>
            </div>
          </div>
        </CardContent>
        <CardFooter class="p-8 pt-0 relative z-10">
          <Button
            variant="ghost"
            class="w-full text-xs font-black uppercase tracking-widest h-14 rounded-2xl bg-white/10 hover:bg-white/20 hover:text-white border-0"
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
