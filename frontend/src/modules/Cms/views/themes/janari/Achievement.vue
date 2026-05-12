<template>
  <div class="min-h-screen bg-background">
    <!-- If Enabled -->
    <template v-if="isEnabled">
      <!-- Hero / Title -->
      <section class="py-24 text-center">
        <div class="container mx-auto px-4">
          <div class="inline-flex items-center justify-center p-4 bg-amber-100 rounded-3xl mb-8 group overflow-hidden relative">
            <Trophy class="w-8 h-8 text-amber-600 relative z-10 group-hover:scale-125 transition-transform duration-500" />
            <div class="absolute inset-0 bg-amber-200 opacity-0 group-hover:opacity-40 transition-opacity" />
          </div>
          <h1 class="text-4xl md:text-6xl font-black mb-6">
            {{ pageTitle || 'Ruang Apresiasi' }}
          </h1>
          <p class="text-lg text-muted-foreground max-w-2xl mx-auto font-medium">
            {{ pageSubtitle || 'Merayakan setiap langkah perjuangan, dedikasi, dan prestasi gemilang warga sekolah di kancah nasional maupun internasional.' }}
          </p>
        </div>
      </section>

      <!-- Achievement Grid -->
      <section class="py-12">
        <div class="container mx-auto px-4">
          <!-- Categories Filter -->
          <div class="flex flex-wrap items-center justify-center gap-4 mb-16">
            <button 
              v-for="cat in categories" 
              :key="cat"
              :class="[
                'px-6 py-2.5 rounded-full text-sm font-bold transition-all duration-300',
                activeCategory === cat 
                  ? 'bg-foreground text-background shadow-lg scale-105' 
                  : 'bg-muted text-muted-foreground hover:bg-muted/80'
              ]"
              @click="activeCategory = cat"
            >
              {{ cat }}
            </button>
          </div>

          <!-- Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <div 
              v-for="item in filteredAchievements" 
              :key="item.id"
              class="group bg-card rounded-[3rem] border border-border overflow-hidden hover:shadow-2xl transition-all duration-500 hover:-translate-y-2"
            >
              <!-- Image Area -->
              <div class="aspect-[4/5] bg-muted relative overflow-hidden">
                <div class="absolute inset-0 flex items-center justify-center text-muted-foreground font-bold italic">
                  {{ item.imagePlaceholder }}
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-60 group-hover:opacity-100 transition-opacity duration-500" />
               
                <div class="absolute bottom-6 left-6 right-6 text-white translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                  <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-0.5 rounded-lg bg-amber-500 text-[10px] font-black uppercase tracking-widest">{{ item.level }}</span>
                    <span class="text-[10px] font-bold uppercase opacity-80">{{ item.year }}</span>
                  </div>
                  <h3 class="text-xl font-bold leading-snug">
                    {{ item.title }}
                  </h3>
                </div>
              </div>

              <div class="p-8">
                <p class="text-muted-foreground text-sm leading-relaxed mb-6">
                  {{ item.description }}
                </p>
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                    {{ item.winner.substring(0, 1) }}
                  </div>
                  <div>
                    <div class="text-sm font-bold">
                      {{ item.winner }}
                    </div>
                    <div class="text-[10px] font-bold text-muted-foreground uppercase">
                      {{ item.role }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </template>

    <!-- If Disabled -->
    <PageDisabled 
      v-else 
      :title="(pageTitle as string) || 'Prestasi'" 
      :message="(getSetting('disabled_page_message') as string)" 
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useTheme } from '@/shared/composables/useTheme';
import PageDisabled from './components/PageDisabled.vue';
import { useThemeDataBindings } from '@/modules/Cms/composables/useThemeDataBindings';
import Trophy from 'lucide-vue-next/dist/esm/icons/trophy.js';

const { getSetting } = useTheme();
const router = useRouter();

const isEnabled = computed(() => getSetting('enable_achievement', true));
const behavior = computed(() => getSetting('disabled_page_behavior', 'message'));
const pageTitle = computed(() => getSetting('page_achievement_title') as string);
const pageSubtitle = computed(() => getSetting('page_achievement_subtitle') as string);

onMounted(() => {
    if (!isEnabled.value && behavior.value === 'redirect') {
        router.push('/');
    }
});

const activeCategory = ref('Semua');
const categories = ['Semua', 'Akademik', 'Olahraga', 'Seni', 'Vokasi'];

// Theme data bindings for achievements list
const { data: dynamicAchievements, hasBinding } = useThemeDataBindings('achievements', 'list');

const mockAchievements = [
  { 
    id: 1, 
    cat: 'Akademik',
    level: 'Nasional', 
    year: '2025',
    title: 'Medali Emas Olimpiade Sains Nasional (OSN)',
    description: 'Keberhasilan luar biasa dalam kompetisi sains tingkat nasional bidang Fisika.',
    winner: 'Adryan Syahputra',
    role: 'Siswa Kelas XI MIPA',
    imagePlaceholder: 'OSN Gold Photo'
  },
  { 
    id: 2, 
    cat: 'Olahraga',
    level: 'Provinsi', 
    year: '2024',
    title: 'Juara 1 Basket Putra O2SN 2024',
    description: 'Tim basket sekolah berhasil mempertahankan gelar juara bertahan di tingkat provinsi.',
    winner: 'Tim Basket Sekolah',
    role: 'Klub Olahraga',
    imagePlaceholder: 'Basket Champion Photo'
  }
];

const achievements = computed(() => {
  if (hasBinding.value && dynamicAchievements.value.length > 0) {
    return dynamicAchievements.value.map((item: any, idx: number) => {
      const raw = item._raw || item;
      return {
        id: raw.id || idx + 1,
        cat: raw.meta?.category_label || 'Akademik',
        level: raw.meta?.level || 'Nasional',
        year: raw.published_at ? new Date(raw.published_at).getFullYear() : '2026',
        title: item.title,
        description: item.excerpt || item.description || raw.description,
        winner: raw.meta?.winner || 'Siswa Berprestasi',
        role: raw.meta?.role || 'Siswa',
        image: raw.featured_image || raw.thumbnail,
        imagePlaceholder: 'Achievement Photo'
      };
    });
  }
  return mockAchievements;
});

const filteredAchievements = computed(() => {
  if (activeCategory.value === 'Semua') return achievements.value;
  return achievements.value.filter(a => a.cat === activeCategory.value);
});
</script>
