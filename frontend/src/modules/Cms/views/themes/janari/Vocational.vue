<template>
  <div class="min-h-screen bg-background">
    <!-- If Enabled -->
    <template v-if="isEnabled">
      <!-- Hero Section -->
      <section class="relative py-24 bg-primary text-primary-foreground overflow-hidden">
        <!-- Decor -->
        <div class="absolute inset-0 opacity-10">
          <div class="grid grid-cols-6 h-full">
            <div
              v-for="i in 24"
              :key="i"
              class="border-[0.5px] border-white/20"
            />
          </div>
        </div>
        
        <div class="container mx-auto px-4 relative z-10 text-center">
          <h1 class="text-4xl md:text-7xl font-black mb-6 uppercase tracking-tight">
            {{ pageTitle || programsLabel }}
          </h1>
          <p class="text-xl opacity-90 max-w-2xl mx-auto font-medium">
            {{ pageSubtitle || 'Mempersiapkan tenaga terampil andal untuk masa depan industri yang kompetitif.' }}
          </p>
        </div>
      </section>

      <!-- Programs Grid -->
      <section class="py-20 -mt-16 relative z-20">
        <div class="container mx-auto px-4">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div 
              v-for="major in majors" 
              :key="major.title"
              class="group p-10 rounded-[3.5rem] bg-card border border-border shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 relative overflow-hidden"
            >
              <!-- Decorative Accent -->
              <div class="absolute -top-10 -right-10 w-40 h-40 bg-primary/5 rounded-full group-hover:bg-primary/10 transition-colors" />
            
              <div class="mb-8 p-4 rounded-3xl bg-primary/10 w-fit group-hover:scale-110 transition-transform duration-500">
                <component
                  :is="major.icon"
                  class="w-8 h-8 text-primary"
                />
              </div>
            
              <h3 class="text-2xl font-bold mb-4 group-hover:text-primary transition-colors">
                {{ major.title }}
              </h3>
              <p class="text-muted-foreground leading-relaxed mb-8">
                {{ major.description }}
              </p>
            
              <div class="flex items-center gap-4 pt-6 border-t border-border/50">
                <div class="flex -space-x-2">
                  <div
                    v-for="i in 3"
                    :key="i"
                    class="w-8 h-8 rounded-full bg-muted border-2 border-card flex items-center justify-center text-[10px] font-bold text-muted-foreground"
                  >
                    {{ String.fromCharCode(64 + i) }}
                  </div>
                </div>
                <span class="text-xs font-bold uppercase tracking-widest text-muted-foreground">24+ Siswa Bergabung</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Why Us / Stats -->
      <section class="py-20 bg-muted/30">
        <div class="container mx-auto px-4">
          <div class="flex flex-col md:flex-row items-center gap-16">
            <div class="flex-1 space-y-6">
              <span class="text-primary font-black uppercase text-xs tracking-widest">Keunggulan Vokasi</span>
              <h2 class="text-4xl font-bold leading-tight">
                Kurikulum Yang <br><span class="text-primary italic underline decoration-wavy underline-offset-8">Terintegrasi</span> Dengan Industri
              </h2>
              <p class="text-muted-foreground text-lg leading-relaxed">
                Kami menjalin kemitraan strategis dengan puluhan industri ternama untuk memastikan kurikulum kami selalu relevan dan lulusan kami memiliki daya serap tinggi di dunia kerja.
              </p>
              <div class="grid grid-cols-2 gap-8 pt-8">
                <div>
                  <div class="text-3xl font-black mb-1">
                    98%
                  </div>
                  <div class="text-xs font-bold text-muted-foreground uppercase tracking-widest">
                    Lulusan Bekerja
                  </div>
                </div>
                <div>
                  <div class="text-3xl font-black mb-1">
                    50+
                  </div>
                  <div class="text-xs font-bold text-muted-foreground uppercase tracking-widest">
                    Mitra Industri
                  </div>
                </div>
              </div>
            </div>
            <div class="flex-1 w-full flex justify-center lg:justify-end">
              <div class="relative w-full max-w-md aspect-square rounded-[4rem] bg-gradient-to-tr from-primary to-primary/60 p-1">
                <div class="w-full h-full bg-card rounded-[3.8rem] flex items-center justify-center p-12 text-center">
                  <div class="space-y-6">
                    <Award class="w-20 h-20 text-primary mx-auto" />
                    <h4 class="text-2xl font-bold">
                      Terakreditasi A
                    </h4>
                    <p class="text-sm text-muted-foreground">
                      Kualitas pendidikan yang diakui secara nasional dengan standar mutu tinggi.
                    </p>
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
      :title="(pageTitle as string) || 'Program Keahlian'" 
      :message="(getSetting('disabled_page_message') as string)" 
    />
  </div>
</template>

<script setup lang="ts">
import { computed, markRaw, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useTheme } from '@/composables/useTheme';
import PageDisabled from './components/PageDisabled.vue';
import { useThemeDataBindings } from '@/modules/Cms/composables/useThemeDataBindings';
import Cpu from 'lucide-vue-next/dist/esm/icons/cpu.js';
import Car from 'lucide-vue-next/dist/esm/icons/car.js';
import Code from 'lucide-vue-next/dist/esm/icons/code.js';
import PenTool from 'lucide-vue-next/dist/esm/icons/pen-tool.js';
import Camera from 'lucide-vue-next/dist/esm/icons/camera.js';
import Laptop from 'lucide-vue-next/dist/esm/icons/laptop.js';
import Award from 'lucide-vue-next/dist/esm/icons/award.js';
import BookOpen from 'lucide-vue-next/dist/esm/icons/book-open.js';
import Monitor from 'lucide-vue-next/dist/esm/icons/monitor.js';
import Layers from 'lucide-vue-next/dist/esm/icons/layers.js';
import Briefcase from 'lucide-vue-next/dist/esm/icons/briefcase.js';
import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';

const { getSetting } = useTheme();
const router = useRouter();

const isEnabled = computed(() => getSetting('enable_vocation', true));
const behavior = computed(() => getSetting('disabled_page_behavior', 'message'));
const pageTitle = computed(() => getSetting('page_vocation_title') as string);
const pageSubtitle = computed(() => getSetting('page_vocation_subtitle') as string);

// Theme data bindings for programs section
const { data: dynamicMajors, hasBinding } = useThemeDataBindings('majors', 'programs');

// Icon Mapping
const iconMap: Record<string, any> = {
  Cpu: markRaw(Cpu), 
  Car: markRaw(Car), 
  Code: markRaw(Code), 
  PenTool: markRaw(PenTool), 
  Camera: markRaw(Camera), 
  Laptop: markRaw(Laptop), 
  Award: markRaw(Award), 
  BookOpen: markRaw(BookOpen), 
  Monitor: markRaw(Monitor), 
  Layers: markRaw(Layers), 
  Briefcase: markRaw(Briefcase), 
  Globe: markRaw(Globe), 
  Settings: markRaw(Settings)
};

onMounted(() => {
    if (!isEnabled.value && behavior.value === 'redirect') {
        router.push('/');
    }
});

const getIcon = (name: string) => iconMap[name] || iconMap['BookOpen'];

const schoolUnit = computed(() => getSetting('school_unit', 'smk') as string);

const programsLabel = computed(() => {
  if (schoolUnit.value === 'smk') return 'Program Keahlian';
  if (schoolUnit.value === 'sma') return 'Program Peminatan';
  return 'Program Unggulan';
});

// Mock Fallback
const mockMajors = computed(() => {
  const level = schoolUnit.value;
  if (level === 'smk') {
    return [
      { title: 'T. Komputer & Jaringan', icon: markRaw(Laptop), description: 'Mempelajari cara merakit, menginstalasi jaringan, dan administrasi server.' },
      { title: 'Teknik Otomotif', icon: markRaw(Car), description: 'Keahlian dalam perawatan dan perbaikan mesin kendaraan bermotor modern.' },
      { title: 'Rekayasa Perangkat Lunak', icon: markRaw(Code), description: 'Fokus pada pengembangan aplikasi web, mobile, dan sistem informasi.' },
    ];
  }
  return [
    { title: 'Kurikulum Unggulan', icon: markRaw(Award), description: 'Sistem pembelajaran berbasis kompetensi standar internasional.' }
  ];
});

const majors = computed(() => {
  if (hasBinding.value && dynamicMajors.value.length > 0) {
    return dynamicMajors.value.map((item: any) => {
      const raw = item._raw || item;
      return {
        title: item.title,
        description: item.excerpt || item.description || raw.description,
        icon: getIcon(raw.meta?.program_icon || 'BookOpen')
      };
    });
  }
  return mockMajors.value;
});
</script>
