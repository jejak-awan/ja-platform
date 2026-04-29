<template>
  <div class="min-h-screen bg-background">
    <!-- Hero Section (matches PPDB.vue pattern exactly) -->
    <section class="relative py-24 flex items-center justify-center overflow-hidden">
      <!-- Animated Background Circles -->
      <div class="absolute top-0 left-0 w-96 h-96 bg-primary/20 rounded-full blur-[100px] -translate-x-1/2 -translate-y-1/2 animate-pulse" />
      <div class="absolute bottom-0 right-0 w-80 h-80 bg-primary/20 rounded-full blur-[80px] translate-x-1/2 translate-y-1/2" />

      <div class="container mx-auto px-4 relative z-10 text-center space-y-8">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-primary/10 text-primary font-bold text-sm">
          <GraduationCap class="w-4 h-4" />
          {{ getSetting('graduation_hero_badge') || `Tahun Ajaran 2025/2026` }}
        </div>
        <h1 class="text-5xl md:text-7xl font-black tracking-tighter">
          {{ (getSetting('graduation_hero_title') as string)?.split('<br/>')[0] || 'Pengumuman' }} <br>
          <span class="bg-gradient-to-r from-primary via-primary/80 to-foreground bg-clip-text text-transparent">
            {{ (getSetting('graduation_hero_title') as string)?.split('<br/>')[1] || 'Kelulusan Siswa' }}
          </span>
        </h1>
        <p class="text-xl text-muted-foreground max-w-2xl mx-auto font-medium leading-relaxed">
          {{ getSetting('graduation_hero_desc') || 'Masukkan NISN/NIS dan Tanggal Lahir untuk melihat hasil kelulusan Anda.' }}
        </p>
      </div>
    </section>

    <!-- ===================== COUNTDOWN / CLOSED STATE ===================== -->
    <section
      v-if="!isOpen"
      class="py-20"
    >
      <div class="container mx-auto px-4 max-w-2xl">
        <div class="p-1 rounded-[4rem] border border-border/40 bg-card shadow-2xl">
          <div class="bg-card rounded-[3.8rem] p-12 text-center space-y-10">
            <!-- Lock Icon -->
            <div class="w-24 h-24 rounded-[2rem] bg-muted flex items-center justify-center mx-auto">
              <Lock class="w-12 h-12 text-muted-foreground" />
            </div>

            <div class="space-y-3">
              <p class="text-[10px] font-black uppercase tracking-[0.4em] text-primary">
                Pengumuman Belum Dibuka
              </p>
              <h2 class="text-2xl md:text-3xl font-black tracking-tight">
                {{ getSetting('graduation_closed_message') || 'Pengumuman kelulusan belum tersedia saat ini.' }}
              </h2>
            </div>

            <!-- Countdown Timer (if schedule exists) -->
            <div
              v-if="hasSchedule && countdown"
              class="space-y-6"
            >
              <p class="text-xs font-bold uppercase tracking-[0.3em] text-muted-foreground">
                Akan dibuka dalam
              </p>
              <div class="grid grid-cols-4 gap-3 max-w-md mx-auto">
                <div
                  v-for="unit in countdownUnits"
                  :key="unit.label"
                  class="space-y-2"
                >
                  <div class="p-4 rounded-[1.5rem] bg-foreground text-background font-black text-3xl md:text-4xl tabular-nums">
                    {{ String(unit.value).padStart(2, '0') }}
                  </div>
                  <p class="text-[9px] font-black uppercase tracking-[0.3em] text-muted-foreground">
                    {{ unit.label }}
                  </p>
                </div>
              </div>
              <p class="text-sm text-muted-foreground font-medium">
                Jadwal pembukaan: <span class="text-foreground font-bold">{{ formattedOpenDate }}</span>
              </p>
            </div>

            <div
              v-else
              class="pt-4"
            >
              <p class="text-sm text-muted-foreground font-medium leading-relaxed max-w-md mx-auto">
                Silakan cek kembali nanti atau hubungi pihak sekolah untuk informasi lebih lanjut.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== OPEN STATE: CHECK FORM ===================== -->
    <template v-else>
      <section class="py-12 relative z-20">
        <div class="container mx-auto px-4 max-w-xl">
          <Card class="border border-border/40 bg-card shadow-2xl rounded-[3rem] overflow-hidden p-1 bg-gradient-to-b from-border/50 to-transparent">
            <CardContent class="p-10 bg-card rounded-[2.8rem]">
              <form
                class="space-y-8"
                @submit.prevent="handleCheck"
              >
                <div class="space-y-3">
                  <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-primary opacity-70 ml-2">
                    {{ $t('features.school.operations.graduation.check.identifier') }}
                  </Label>
                  <div class="relative group">
                    <UserIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground group-focus-within:text-primary transition-colors" />
                    <Input
                      v-model="form.identifier"
                      placeholder="NISN / NIS"
                      class="rounded-2xl h-14 pl-12 bg-muted/30 border-transparent focus:bg-background focus:ring-primary/20 transition-all text-lg font-bold"
                      required
                    />
                  </div>
                </div>

                <div class="space-y-3">
                  <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-primary opacity-70 ml-2">
                    {{ $t('features.school.operations.graduation.check.dob') }}
                  </Label>
                  <div class="relative group">
                    <CalendarIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground group-focus-within:text-primary transition-colors" />
                    <Input
                      v-model="form.dob"
                      type="date"
                      class="rounded-2xl h-14 pl-12 bg-muted/30 border-transparent focus:bg-background focus:ring-primary/20 transition-all text-lg font-bold"
                      required
                    />
                  </div>
                </div>

                <Button
                  type="submit"
                  class="w-full h-16 rounded-2xl font-black uppercase tracking-widest text-lg shadow-xl shadow-primary/25 bg-primary hover:scale-[1.02] active:scale-95 transition-all"
                  :loading="loading"
                >
                  {{ $t('features.school.operations.graduation.check.btnCheck') }}
                </Button>
              </form>
            </CardContent>
          </Card>
        </div>
      </section>

      <!-- Results Section -->
      <section
        v-if="result"
        class="py-20"
      >
        <div class="container mx-auto px-4 max-w-2xl">
          <div :class="`p-1 rounded-[4rem] border shadow-2xl ${result.is_graduated ? 'border-success/40 bg-success/5 shadow-success/10' : 'border-destructive/40 bg-destructive/5 shadow-destructive/10'}`">
            <div class="bg-card rounded-[3.8rem] p-12 text-center space-y-8">
              <div class="space-y-2">
                <p class="text-[10px] font-black uppercase tracking-[0.4em] opacity-40">
                  {{ $t('features.school.operations.graduation.check.resultTitle') }}
                </p>
                <h2 class="text-4xl font-black tracking-tight">
                  {{ result.full_name }}
                </h2>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-muted text-[10px] font-bold font-mono opacity-60 uppercase tracking-widest">
                  NISN: {{ result.nisn }}
                </div>
              </div>

              <div :class="`py-10 px-6 rounded-[3rem] border-2 border-dashed ${result.is_graduated ? 'bg-success/5 border-success/30 text-success' : 'bg-destructive/5 border-destructive/30 text-destructive'} `">
                <div :class="`w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg ${result.is_graduated ? 'bg-success text-success-foreground' : 'bg-destructive text-destructive-foreground'}`">
                  <component
                    :is="result.is_graduated ? PartyPopper : CircleAlert"
                    class="w-10 h-10"
                  />
                </div>
                <p class="text-2xl font-black leading-tight uppercase italic tracking-tight">
                  {{ result.is_graduated ? $t('features.school.operations.graduation.check.graduated') : $t('features.school.operations.graduation.check.notGraduated') }}
                </p>
              </div>

              <div
                v-if="result.is_graduated"
                class="space-y-6 pt-4"
              >
                <p class="text-sm text-muted-foreground font-medium leading-relaxed">
                  {{ $t('features.school.operations.graduation.check.downloadHint') }}
                </p>
                <Button
                  variant="outline"
                  class="w-full h-16 rounded-2xl border-success/30 text-success hover:bg-success/5 font-black uppercase tracking-widest text-lg"
                  @click="downloadSkl"
                >
                  <Download class="w-6 h-6 mr-3" />
                  {{ $t('features.school.operations.graduation.labels.btnDownloadSkl') }}
                </Button>
              </div>
            </div>
          </div>
        </div>
      </section>
    </template>

    <!-- Contact CTA (same pattern as PPDB.vue) -->
    <section class="py-20">
      <div class="container mx-auto px-4">
        <div class="p-12 rounded-[4rem] bg-foreground text-background relative overflow-hidden">
          <div class="absolute top-0 right-0 w-64 h-64 bg-primary/10 rounded-full blur-[60px]" />
          <div class="flex flex-col md:flex-row items-center justify-between gap-12 relative z-10">
            <div class="space-y-4 text-center md:text-left">
              <h2 class="text-3xl font-black">
                {{ getSetting('graduation_cta_title') || 'Butuh Bantuan?' }}
              </h2>
              <p class="text-background/70 font-medium">
                {{ getSetting('graduation_cta_desc') || 'Hubungi pihak sekolah untuk pertanyaan terkait kelulusan dan SKL.' }}
              </p>
            </div>
            <a
              v-if="whatsAppAdminUrl"
              :href="whatsAppAdminUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="px-10 py-4 rounded-3xl bg-primary text-primary-foreground hover:bg-primary/90 transition-colors flex items-center gap-3 font-bold text-lg"
            >
              <PhoneCall class="w-6 h-6" />
              Hubungi Sekolah
            </a>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useTheme } from '@/composables/useTheme';
import { useJanariIdentity } from '@/modules/Cms/views/themes/janari/composables/useJanariIdentity';
import {
  Card, CardContent, Button, Input, Label
} from '@/components/ui';
import { OperationsService } from '@/modules/School/services/OperationsService';
import { useToast } from '@/composables/useToast';
import { parseResponse } from '@/utils/responseParser';

// Icons — ESM direct imports (tree-shakeable)
import GraduationCap from 'lucide-vue-next/dist/esm/icons/graduation-cap.js';
import UserIcon from 'lucide-vue-next/dist/esm/icons/user.js';
import CalendarIcon from 'lucide-vue-next/dist/esm/icons/calendar.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import PartyPopper from 'lucide-vue-next/dist/esm/icons/party-popper.js';
import CircleAlert from 'lucide-vue-next/dist/esm/icons/circle-alert.js';
import Lock from 'lucide-vue-next/dist/esm/icons/lock.js';
import PhoneCall from 'lucide-vue-next/dist/esm/icons/phone-call.js';

const { getSetting } = useTheme();
const { whatsAppAdminUrl } = useJanariIdentity();
const toast = useToast();
const loading = ref(false);
const form = ref({ identifier: '', dob: '' });
const result = ref<any>(null);

// ======================== SCHEDULE & COUNTDOWN ========================

/**
 * Settings expected from admin theme customizer:
 *  - graduation_is_open:    boolean  — manual toggle
 *  - graduation_open_at:    string   — ISO datetime e.g. "2026-06-15T08:00:00"
 *  - graduation_close_at:   string   — ISO datetime (optional)
 */
const now = ref(Date.now());
let ticker: ReturnType<typeof setInterval> | null = null;

const openAt = computed(() => {
  const raw = getSetting('graduation_open_at') as string | undefined;
  return raw ? new Date(raw).getTime() : null;
});

const closeAt = computed(() => {
  const raw = getSetting('graduation_close_at') as string | undefined;
  return raw ? new Date(raw).getTime() : null;
});

const manualToggle = computed(() => {
  const val = getSetting('graduation_is_open');
  if (val === true || val === 'true' || val === '1' || val === 1) return true;
  if (val === false || val === 'false' || val === '0' || val === 0) return false;
  return null; // not set — rely on schedule
});

const isOpen = computed(() => {
  // 1. Manual toggle takes priority
  if (manualToggle.value === true) return true;
  if (manualToggle.value === false) return false;

  // 2. Schedule-based
  const currentMs = now.value;
  if (openAt.value && currentMs < openAt.value) return false;
  if (closeAt.value && currentMs > closeAt.value) return false;
  if (openAt.value && currentMs >= openAt.value) return true;

  // 3. Default: closed
  return false;
});

const hasSchedule = computed(() => openAt.value !== null && !isOpen.value);

const countdown = computed(() => {
  if (!openAt.value) return null;
  const diff = openAt.value - now.value;
  if (diff <= 0) return null;
  return diff;
});

const countdownUnits = computed(() => {
  const diff = countdown.value;
  if (!diff) return [];
  const d = Math.floor(diff / (1000 * 60 * 60 * 24));
  const h = Math.floor((diff / (1000 * 60 * 60)) % 24);
  const m = Math.floor((diff / (1000 * 60)) % 60);
  const s = Math.floor((diff / 1000) % 60);
  return [
    { value: d, label: 'Hari' },
    { value: h, label: 'Jam' },
    { value: m, label: 'Menit' },
    { value: s, label: 'Detik' },
  ];
});

const formattedOpenDate = computed(() => {
  if (!openAt.value) return '';
  return new Date(openAt.value).toLocaleString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    timeZoneName: 'short',
  });
});

onMounted(() => {
  ticker = setInterval(() => {
    now.value = Date.now();
  }, 1000);
});

onUnmounted(() => {
  if (ticker) clearInterval(ticker);
});

// ======================== CHECK LOGIC ========================

const handleCheck = async () => {
  loading.value = true;
  result.value = null;
  try {
    const response = await OperationsService.checkPublicGraduation(form.value);
    result.value = parseResponse(response).data;
  } catch (e: any) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};

const downloadSkl = () => {
  if (result.value?.skl_url) {
    window.open(result.value.skl_url, '_blank');
  }
};
</script>
