<template>
  <div class="min-h-screen bg-background">
    <!-- If Enabled -->
    <template v-if="isEnabled">
      <!-- Header -->
      <header class="py-16 bg-primary text-primary-foreground">
        <div class="container mx-auto px-4">
          <div class="flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="space-y-4 text-center md:text-left">
              <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight">
                {{ pageTitle || 'Pusat Karir & BKK' }}
              </h1>
              <p class="text-primary-foreground/85 text-lg max-w-xl">
                {{ pageSubtitle || 'Menghubungkan talenta muda sekolah dengan jaringan industri terpercaya untuk masa depan yang gemilang.' }}
              </p>
            </div>
            <div class="flex gap-4">
              <div class="p-6 bg-white/10 backdrop-blur-md rounded-3xl border border-white/20 text-center min-w-[120px]">
                <div class="text-3xl font-black">
                  150+
                </div>
                <div class="text-[10px] font-bold uppercase tracking-wider opacity-80 mt-1">
                  Mitra Aktif
                </div>
              </div>
              <div class="p-6 bg-white/10 backdrop-blur-md rounded-3xl border border-white/20 text-center min-w-[120px]">
                <div class="text-3xl font-black">
                  12
                </div>
                <div class="text-[10px] font-bold uppercase tracking-wider opacity-80 mt-1">
                  Loker Baru
                </div>
              </div>
            </div>
          </div>
        </div>
      </header>

      <div class="container mx-auto px-4 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
          <!-- Main Content (Job List) -->
          <div class="lg:col-span-2 space-y-8">
            <div class="flex items-center justify-between">
              <h2 class="text-2xl font-bold flex items-center gap-3">
                <Briefcase class="w-6 h-6 text-primary" />
                Lowongan Pekerjaan Terbaru
              </h2>
              <button class="text-sm font-bold text-primary hover:underline">
                Lihat Semua
              </button>
            </div>

            <!-- Job Cards -->
            <div class="space-y-4">
              <div 
                v-for="job in jobs" 
                :key="job.id"
                class="p-6 rounded-[2rem] bg-card border border-border hover:border-primary/50 transition-all duration-300 hover:shadow-xl group"
              >
                <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                  <div class="w-16 h-16 rounded-2xl bg-muted flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <Building2 class="w-8 h-8 text-muted-foreground" />
                  </div>
                  <div class="flex-1 space-y-1">
                    <div class="flex items-center gap-2">
                      <h3 class="font-bold text-lg">
                        {{ job.title }}
                      </h3>
                      <span
                        v-if="job.isNew"
                        class="px-2 py-0.5 rounded-full bg-primary/12 text-primary text-[10px] font-bold uppercase"
                      >Baru</span>
                    </div>
                    <p class="text-primary font-semibold text-sm">
                      {{ job.company }}
                    </p>
                    <div class="flex flex-wrap gap-4 pt-2">
                      <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <MapPin class="w-3.5 h-3.5" /> {{ job.location }}
                      </div>
                      <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <Clock class="w-3.5 h-3.5" /> {{ job.type }}
                      </div>
                    </div>
                  </div>
                  <button class="px-6 py-2.5 rounded-xl bg-primary text-primary-foreground text-sm font-bold hover:bg-primary/90 transition-colors">
                    Detail
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-8">
            <!-- Hubin Section -->
            <div class="p-8 rounded-[2.5rem] bg-muted/40 dark:bg-muted/20 border border-border">
              <div class="w-12 h-12 rounded-2xl bg-primary text-primary-foreground flex items-center justify-center mb-6 shadow-lg shadow-primary/25">
                <Users2 class="w-6 h-6" />
              </div>
              <h3 class="text-xl font-bold mb-3 text-foreground">
                Hubin & Industrial
              </h3>
              <p class="text-muted-foreground text-sm leading-relaxed mb-6">
                Layanan khusus kemitraan industri, mulai dari sinkronisasi kurikulum, PKL siswa, hingga guru tamu.
              </p>
              <button class="w-full py-3 rounded-xl bg-primary text-primary-foreground font-bold text-sm hover:bg-primary/90 transition-colors">
                Kontak Kemitraan
              </button>
            </div>

            <!-- Resources -->
            <div class="p-8 rounded-[2.5rem] bg-card border border-border">
              <h3 class="font-bold text-lg mb-6">
                Panduan Karir
              </h3>
              <ul class="space-y-4">
                <li
                  v-for="link in [
                    'Tips Membuat CV yang Menarik',
                    'Simulasi Interview Kerja',
                    'Prosedur Pendaftaran BKK',
                    'Informasi Magang ke Luar Negeri'
                  ]"
                  :key="link"
                  class="flex items-center gap-3 text-sm text-muted-foreground hover:text-primary cursor-pointer transition-colors group"
                >
                  <ArrowRight class="w-4 h-4 text-primary group-hover:translate-x-1 transition-transform" />
                  {{ link }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- If Disabled -->
    <PageDisabled 
      v-else 
      :title="(pageTitle as string) || 'Bursa Kerja Khusus'" 
      :message="(getSetting('disabled_page_message') as string)" 
    />
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useTheme } from '@/shared/composables/useTheme';
import PageDisabled from './components/PageDisabled.vue';
import Briefcase from 'lucide-vue-next/dist/esm/icons/briefcase.js';
import Building2 from 'lucide-vue-next/dist/esm/icons/building-2.js';
import MapPin from 'lucide-vue-next/dist/esm/icons/map-pin.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import Users2 from 'lucide-vue-next/dist/esm/icons/users.js';
import ArrowRight from 'lucide-vue-next/dist/esm/icons/arrow-right.js';

const { getSetting } = useTheme();
const router = useRouter();

const isEnabled = computed(() => getSetting('enable_career', true));
const behavior = computed(() => getSetting('disabled_page_behavior', 'message'));
const pageTitle = computed(() => getSetting('page_career_title') as string);
const pageSubtitle = computed(() => getSetting('page_career_subtitle') as string);

onMounted(() => {
    if (!isEnabled.value && behavior.value === 'redirect') {
        router.push('/');
    }
});

// Demo data - in a real app this would come from an API
const jobs = [
  { 
    id: "1", 
    title: 'Junior Web Developer', 
    company: 'PT. Teknologi Kreatif Digital', 
    location: 'Jakarta (Remote)', 
    type: 'Full-time',
    isNew: true
  },
  { 
    id: "2", 
    title: 'Mekanik Alat Berat', 
    company: 'United Tractors Indonesia', 
    location: 'Kalimantan Timur', 
    type: 'Full-time',
    isNew: true
  },
  { 
    id: "3", 
    title: 'Editor Video & Motion', 
    company: 'Arkadia Creative Hub', 
    location: 'Bandung', 
    type: 'Internship',
    isNew: false
  },
];
</script>
