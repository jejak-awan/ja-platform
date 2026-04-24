<template>
  <div class="min-h-screen bg-background">
    <!-- Hero / Call to Action -->
    <section class="relative py-24 flex items-center justify-center overflow-hidden">
      <!-- Animated Background Circles -->
      <div class="absolute top-0 left-0 w-96 h-96 bg-primary/20 rounded-full blur-[100px] -translate-x-1/2 -translate-y-1/2 animate-pulse" />
      <div class="absolute bottom-0 right-0 w-80 h-80 bg-primary/20 rounded-full blur-[80px] translate-x-1/2 translate-y-1/2" />
      
      <div class="container mx-auto px-4 relative z-10 text-center space-y-8">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-primary/10 text-primary font-bold text-sm">
          <Sparkles class="w-4 h-4" />
          {{ getSetting('ppdb_hero_badge') || `PPDB ${schoolName} 2026/2027` }}
        </div>
        <h1 class="text-5xl md:text-7xl font-black tracking-tighter">
          {{ (getSetting('ppdb_hero_title') as string)?.split('<br/>')[0] || 'Mulai Perjalanan' }} <br>
          <span class="bg-gradient-to-r from-primary via-primary/80 to-foreground bg-clip-text text-transparent">
            {{ (getSetting('ppdb_hero_title') as string)?.split('<br/>')[1] || 'Masa Depanmu' }}
          </span> Di Sini.
        </h1>
        <p class="text-xl text-muted-foreground max-w-2xl mx-auto font-medium leading-relaxed">
          {{ getSetting('ppdb_hero_desc') || 'Bergabunglah dengan komunitas pembelajar yang dinamis dan inovatif. Pendaftaran kini dibuka secara online untuk semua jenjang.' }}
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
          <button class="px-8 py-4 rounded-2xl bg-primary text-primary-foreground font-bold shadow-xl shadow-primary/25 hover:scale-105 transition-transform">
            Daftar Sekarang
          </button>
          <button class="px-8 py-4 rounded-2xl bg-muted font-bold hover:bg-muted/80 transition-colors">
            Unduh Brosur (PDF)
          </button>
        </div>
      </div>
    </section>

    <!-- Steps -->
    <section class="py-20 bg-muted/30">
      <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-16 underline decoration-primary/30 decoration-8 underline-offset-4">
          Alur Pendaftaran
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div
            v-for="(step, i) in ppdbSteps"
            :key="i"
            class="relative group"
          >
            <!-- Connector line -->
            <div
              v-if="i < ppdbSteps.length - 1"
              class="hidden md:block absolute top-12 left-1/2 w-full h-[2px] bg-border group-hover:bg-primary/50 transition-colors -z-10"
            />
                    
            <div class="space-y-4 text-center">
              <div class="w-24 h-24 rounded-[2rem] bg-card border border-border shadow-md mx-auto flex items-center justify-center relative group-hover:border-primary group-hover:shadow-primary/10 transition-all duration-500">
                <span class="text-3xl font-black text-primary/20 group-hover:text-primary transition-colors">{{ i + 1 }}</span>
                <component
                  :is="getIcon(step.icon)"
                  class="w-8 h-8 absolute text-foreground group-hover:scale-110 transition-transform"
                />
              </div>
              <h3 class="font-bold text-lg">
                {{ step.title }}
              </h3>
              <p class="text-xs text-muted-foreground leading-relaxed px-4">
                {{ step.desc }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="py-20">
      <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-3xl font-bold mb-12 flex items-center gap-4">
          <MessageSquareText class="w-8 h-8 text-primary" />
          Pertanyaan Sering Diajukan
        </h2>
        <div class="space-y-4">
          <div 
            v-for="(faq, i) in faqs" 
            :key="i" 
            class="p-6 rounded-[2rem] border border-border bg-card hover:bg-muted/50 transition-all duration-300 cursor-pointer group relative z-10"
            :class="{ 'border-primary/40 shadow-xl shadow-primary/10 bg-muted/30': activeIndex === i }"
            @click="activeIndex = activeIndex === i ? null : i"
          >
            <div class="flex items-center justify-between pointer-events-none">
              <h4
                class="font-bold text-foreground pr-8 transition-colors text-lg"
                :class="{ 'text-primary': activeIndex === i }"
              >
                {{ faq.q }}
              </h4>
              <div
                class="w-10 h-10 rounded-full bg-muted flex items-center justify-center transition-all duration-500"
                :class="{ 'rotate-45 bg-primary text-primary-foreground shadow-lg shadow-primary/20': activeIndex === i }"
              >
                <Plus class="w-6 h-6 shrink-0" />
              </div>
            </div>
            <div 
              class="grid transition-[grid-template-rows,opacity,margin] duration-500 ease-in-out"
              :class="activeIndex === i ? 'grid-rows-[1fr] opacity-100 mt-6' : 'grid-rows-[0fr] opacity-0 mt-0'"
            >
              <div class="overflow-hidden">
                <div class="pt-6 border-t border-border/50 text-muted-foreground text-base md:text-lg leading-relaxed font-medium">
                  {{ faq.a }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact CTA -->
    <section class="py-20">
      <div class="container mx-auto px-4">
        <div class="p-12 rounded-[4rem] bg-foreground text-background relative overflow-hidden">
          <div class="absolute top-0 right-0 w-64 h-64 bg-primary/10 rounded-full blur-[60px]" />
          <div class="flex flex-col md:flex-row items-center justify-between gap-12 relative z-10">
            <div class="space-y-4 text-center md:text-left">
              <h2 class="text-3xl font-black">
                Butuh Bantuan Pendaftaran?
              </h2>
              <p class="text-background/70 font-medium">
                Tim marketing kami siap membantu Anda setiap hari kerja pukul 08:00 - 15:00 WIB.
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
              {{ getSetting('ppdb_wa_text') || 'Chat Admin PPDB' }}
            </a>
            <p
              v-else
              class="text-sm text-background/65 max-w-xs text-center md:text-left"
            >
              Atur nomor WhatsApp di Theme Customizer (bagian PPDB) atau isi Telepon di Contact / Identitas situs.
            </p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useTheme } from '@/composables/useTheme';
import { useJanariIdentity } from '@/modules/Cms/views/themes/janari/composables/useJanariIdentity';
import Sparkles from 'lucide-vue-next/dist/esm/icons/sparkles.js';
import UserPlus from 'lucide-vue-next/dist/esm/icons/user-plus.js';
import FileCheck from 'lucide-vue-next/dist/esm/icons/file-check.js';
import BadgeCheck from 'lucide-vue-next/dist/esm/icons/badge-check.js';
import CreditCard from 'lucide-vue-next/dist/esm/icons/credit-card.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import MessageSquareText from 'lucide-vue-next/dist/esm/icons/message-square-text.js';
import PhoneCall from 'lucide-vue-next/dist/esm/icons/phone-call.js';

const { getSetting } = useTheme();
const { whatsAppAdminUrl } = useJanariIdentity();
const schoolLevel = computed(() => (getSetting('school_level') as string) || 'smk');

const schoolName = computed(() => {
    const names: Record<string, string> = {
        sd: 'SD',
        smp: 'SMP',
        sma: 'SMA',
        smk: 'SMK',
        gabungan: 'Yayasan'
    };
    return names[schoolLevel.value] || 'Sekolah';
});

const activeIndex = ref<number | null>(null);

const ppdbSteps = computed(() => (getSetting('ppdb_steps') as any[]) || [
    { title: 'Daftar Akun', desc: `Isi formulir dasar untuk mendapatkan nomor registrasi ${schoolLevel.value.toUpperCase()} dan login.`, icon: 'UserPlus' },
    { title: 'Lengkapi Data', desc: 'Upload berkas (Rapor, KK, Akta) dan isi data lengkap keluarga.', icon: 'FileCheck' },
    { title: 'Seleksi Mandiri', desc: 'Ikuti tes minat bakat atau wawancara sesuai jadwal.', icon: 'BadgeCheck' },
    { title: 'Daftar Ulang', desc: 'Konfirmasi kelulusan dan lakukan pembayaran seragam/atribut.', icon: 'CreditCard' },
]);

const faqs = computed(() => (getSetting('ppdb_faqs') as any[]) || [
    { 
        q: 'Kapan batas akhir pendaftaran gelombang kedua?', 
        a: 'Pendaftaran gelombang kedua akan dibuka hingga 30 Juni 2026. Namun, pendaftaran dapat ditutup lebih awal jika kuota siswa baru telah terpenuhi.' 
    },
    { 
        q: 'Apa saja persyaratan berkas untuk jalur prestasi?', 
        a: 'Persyaratan meliputi rapor semester 1-5, sertifikat kejuaraan minimal tingkat kabupaten (asli & fotokopi), serta dokumen kependudukan standar seperti KK dan Akta Kelahiran.' 
    },
    { 
        q: 'Apakah ada potongan biaya bagi saudara kandung siswa aktif?', 
        a: 'Ya, kami memberikan potongan biaya pembangunan sebesar 10% bagi calon siswa yang memiliki saudara kandung yang saat ini masih aktif bersekolah di instansi kami.' 
    },
    { 
        q: 'Bagaimana cara melihat hasil pengumuman kelulusan seleksi?', 
        a: 'Hasil seleksi dapat dilihat melalui portal PPDB ini pada menu "Cek Kelulusan" dengan menggunakan nomor pendaftaran dan password yang telah didaftarkan.' 
    },
]);

const getIcon = (iconName: string) => {
    const icons: Record<string, any> = {
        Sparkles, UserPlus, FileCheck, BadgeCheck, CreditCard, Plus, MessageSquareText, PhoneCall
    };
    return icons[iconName] || UserPlus;
};
</script>
