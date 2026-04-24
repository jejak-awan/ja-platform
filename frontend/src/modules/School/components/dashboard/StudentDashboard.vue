<template>
  <div class="space-y-8 animate-in slide-in-from-bottom-4 duration-700">
    <!-- Student Profile Header -->
    <div class="p-8 rounded-[2rem] bg-gradient-to-br from-indigo-500/10 via-background to-background border border-indigo-500/20 shadow-xl shadow-indigo-500/5 relative overflow-hidden">
      <div class="flex flex-col md:flex-row items-center gap-6 relative z-10">
        <div class="w-24 h-24 rounded-3xl bg-indigo-500/20 flex items-center justify-center border-2 border-indigo-500/30 overflow-hidden shadow-inner">
          <LucideIcon
            name="User"
            class="w-12 h-12 text-indigo-500"
          />
        </div>
        <div class="text-center md:text-left">
          <h1 class="text-3xl font-black tracking-tight text-foreground">
            Halo, {{ authStore.user?.name }}! 👋
          </h1>
          <p class="text-muted-foreground mt-1 font-medium">
            Kelas XI RPL 1 • NISN: 0012345678
          </p>
          <div class="flex flex-wrap justify-center md:justify-start gap-2 mt-4">
            <Badge
              variant="secondary"
              class="bg-emerald-500/10 text-emerald-600 hover:bg-emerald-500/20 border-emerald-500/20 rounded-full px-4"
            >
              Status: Aktif
            </Badge>
            <Badge
              variant="secondary"
              class="bg-indigo-500/10 text-indigo-600 hover:bg-indigo-500/20 border-indigo-500/20 rounded-full px-4 text-xs"
            >
              Peringkat Kelas: #3
            </Badge>
          </div>
        </div>
      </div>
      <div class="absolute right-[-5%] bottom-[-20%] w-64 h-64 bg-indigo-500/5 rounded-full blur-3xl" />
    </div>

    <!-- Learning Progress & Quick Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <Card class="lg:col-span-2 bg-card/40 backdrop-blur-md border-border/50 overflow-hidden">
        <CardHeader class="pb-2">
          <CardTitle>Lanjutkan Belajar</CardTitle>
          <CardDescription>Materi terakhir yang Anda pelajari.</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="p-6 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-500/20 relative group cursor-pointer overflow-hidden">
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
                  <div class="h-1.5 bg-white/20 rounded-full">
                    <div class="h-full bg-white rounded-full w-3/4" />
                  </div>
                </div>
                <Button
                  size="icon"
                  class="bg-white text-indigo-600 hover:bg-indigo-50 rounded-full h-12 w-12 shadow-md"
                >
                  <LucideIcon
                    name="Play"
                    class="w-5 h-5 fill-current"
                  />
                </Button>
              </div>
            </div>
            <LucideIcon
              name="Code"
              class="absolute right-[-10px] bottom-[-20px] w-32 h-32 opacity-10 rotate-12 group-hover:rotate-0 transition-transform duration-500"
            />
          </div>
        </CardContent>
      </Card>

      <!-- Grade Overview Mini -->
      <Card class="bg-card/40 backdrop-blur-md border-border/50">
        <CardHeader class="pb-2 text-center">
          <CardTitle class="text-sm text-muted-foreground font-medium">
            Rata-rata Nilai (IPK)
          </CardTitle>
        </CardHeader>
        <CardContent class="flex flex-col items-center justify-center py-6">
          <div class="w-32 h-32 rounded-full border-[10px] border-indigo-500/10 flex items-center justify-center relative">
            <svg class="absolute inset-0 w-full h-full -rotate-90">
              <circle
                cx="64"
                cy="64"
                r="54"
                fill="transparent"
                stroke="currentColor"
                stroke-width="10"
                class="text-indigo-500"
                stroke-dasharray="339.292"
                stroke-dashoffset="50.89"
              />
            </svg>
            <div class="text-center">
              <span class="text-4xl font-black text-indigo-600">3.85</span>
              <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                Sangat Baik
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Lists Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Upcoming Tasks -->
      <div class="space-y-4">
        <h3 class="text-xl font-bold flex items-center gap-2 px-2">
          <LucideIcon
            name="Clock"
            class="w-5 h-5 text-indigo-500"
          />
          Tugas Mendatang
        </h3>
        <div class="space-y-3">
          <div
            v-for="i in 3"
            :key="i"
            class="p-4 rounded-2xl bg-card border border-border/50 hover:border-indigo-500/30 transition-all hover:shadow-lg hover:shadow-indigo-500/5 group cursor-pointer"
          >
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-xl bg-orange-500/10 flex items-center justify-center text-orange-500">
                <LucideIcon
                  name="FileText"
                  class="w-5 h-5"
                />
              </div>
              <div class="flex-1">
                <h4 class="font-bold group-hover:text-indigo-600 transition-colors">
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
            class="w-5 h-5 text-indigo-500"
          />
          Pengumuman Baru
        </h3>
        <div class="space-y-3">
          <div
            v-for="i in 2"
            :key="i"
            class="p-4 rounded-2xl bg-indigo-500/5 border border-indigo-500/10 relative overflow-hidden"
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
import { useAuthStore } from '@/modules/Core/stores/auth';
import {
  Card, CardContent, CardHeader, CardTitle, CardDescription,
  Button, LucideIcon, Badge
} from '@/components/ui';

const authStore = useAuthStore();
</script>
