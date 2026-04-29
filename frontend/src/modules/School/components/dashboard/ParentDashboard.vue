<template>
  <div class="space-y-8 animate-in slide-in-from-right-4 duration-700">
    <!-- Parent Header -->
    <!-- Parent Header: Clean -->
    <div class="p-8 rounded-xl bg-card border border-border/50 shadow-sm flex flex-col md:flex-row justify-between items-center gap-6">
      <div class="flex items-center gap-6">
        <div class="w-16 h-16 rounded-xl bg-primary/10 flex items-center justify-center text-primary border border-primary/20 shrink-0">
          <LucideIcon
            name="ShieldCheck"
            class="w-8 h-8"
          />
        </div>
        <div>
          <h1 class="text-2xl font-bold tracking-tight">
            Parent Monitor
          </h1>
          <p class="text-muted-foreground text-sm">
            Monitoring Akademik & Kehadiran Ananda.
          </p>
        </div>
      </div>
      <div class="flex gap-4">
        <div class="text-right">
          <p class="text-[9px] font-black text-muted-foreground uppercase tracking-[0.2em]">
            Siswa Terhubung
          </p>
          <p class="font-bold text-primary text-sm">
            Muhammad Al-Fatih (XI-RPL-1)
          </p>
        </div>
      </div>
    </div>

    <!-- Monitoring Tiles -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <Card
        v-for="tile in monitorTiles"
        :key="tile.label"
        class="bg-card border-border/40 shadow-none hover:bg-accent/50 transition-colors cursor-pointer rounded-xl"
      >
        <CardContent class="p-6">
          <LucideIcon
            :name="tile.icon"
            :class="['w-5 h-5 mb-3', tile.iconClass]"
          />
          <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest">
            {{ tile.label }}
          </p>
          <div class="flex items-baseline gap-2 mt-1">
            <h3 class="text-2xl font-black">
              {{ tile.value }}
            </h3>
            <span :class="['text-[10px] font-bold', tile.trendClass]">{{ tile.trend }}</span>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Attendance Calendar -->
      <Card class="lg:col-span-2 bg-card border-border/40 shadow-none rounded-xl">
        <CardHeader class="flex flex-row items-center justify-between">
          <div>
            <CardTitle>Kehadiran Bulan Ini</CardTitle>
            <CardDescription>Visualisasi absensi harian Ananda.</CardDescription>
          </div>
          <Badge class="bg-emerald-500/10 text-emerald-600 hover:bg-emerald-500/20 border-emerald-500/10 h-6">
            98% Kehadiran
          </Badge>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-7 gap-2">
            <div
              v-for="d in ['S', 'S', 'R', 'K', 'J', 'S', 'M']"
              :key="d"
              class="text-center text-[10px] font-bold text-muted-foreground p-2"
            >
              {{ d }}
            </div>
            <div
              v-for="i in 31"
              :key="i"
              :class="['aspect-square rounded-lg flex items-center justify-center text-xs font-bold border-2', i === 15 ? 'bg-orange-500/20 border-orange-500/30 text-orange-600' : (i % 7 === 0 ? 'bg-muted/50 border-transparent text-muted-foreground/30' : 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600')]"
            >
              {{ i }}
              <div
                v-if="i === 15"
                class="absolute mt-12 w-2 h-2 rounded-full bg-orange-500 animate-ping"
              />
            </div>
          </div>
          <div class="mt-6 flex gap-4 text-[10px] font-bold uppercase tracking-tight text-muted-foreground px-2">
            <div class="flex items-center gap-1.5">
              <div class="w-3 h-3 rounded bg-emerald-500/20 border border-emerald-500/40" /> Hadir
            </div>
            <div class="flex items-center gap-1.5">
              <div class="w-3 h-3 rounded bg-orange-500/20 border border-orange-500/40" /> Izin/Sakit
            </div>
            <div class="flex items-center gap-1.5">
              <div class="w-3 h-3 rounded bg-muted/50" /> Libur
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Grade Performance -->
      <Card class="bg-card border-border/40 shadow-none rounded-xl">
        <CardHeader>
          <CardTitle>Performa Akademik</CardTitle>
          <CardDescription>Nilai rata-rata per mata pelajaran.</CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
          <div
            v-for="sub in ['Matematika', 'B. Inggris', 'Produktif RPL', 'PAI']"
            :key="sub"
            class="space-y-2"
          >
            <div class="flex justify-between text-xs font-bold">
              <span>{{ sub }}</span>
              <span class="text-emerald-600">89</span>
            </div>
            <div class="h-1 bg-muted rounded-full overflow-hidden">
              <div class="h-full bg-emerald-500 rounded-full w-[89%] shadow-sm" />
            </div>
          </div>
        </CardContent>
        <CardFooter>
          <Button
            variant="ghost"
            class="w-full text-xs gap-2 group"
          >
            Lihat Raport Detail
            <LucideIcon
              name="ArrowRight"
              class="w-3 h-3 group-hover:translate-x-1 transition-transform"
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
  Button, LucideIcon, Badge
} from '@/components/ui';

const monitorTiles = [
  { label: 'Rata-rata Nilai', value: '88.5', trend: '+1.2', trendClass: 'text-emerald-600', icon: 'Calculator', iconClass: 'text-emerald-500' },
  { label: 'Tugas Belum Selesai', value: '2', trend: '-1', trendClass: 'text-emerald-600', icon: 'ListTodo', iconClass: 'text-orange-500' },
  { label: 'Ketidakhadiran', value: '1', trend: 'Stabil', trendClass: 'text-muted-foreground', icon: 'UserX', iconClass: 'text-rose-500' },
  { label: 'Point Pelanggaran', value: '0', trend: 'Aman', trendClass: 'text-emerald-600', icon: 'ShieldAlert', iconClass: 'text-emerald-500' },
];
</script>
