<template>
  <div class="min-h-screen bg-background pb-20">
    <!-- If Enabled -->
    <template v-if="isEnabled">
      <!-- Hero Section -->
      <section class="relative py-20 overflow-hidden">
        <div class="absolute inset-0 bg-primary/5 -z-10" />
        <div class="container mx-auto px-4 text-center">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold uppercase tracking-widest mb-6">
            <BookOpen class="w-3 h-3" />
            {{ pageTitle || 'Akademik & Kurikulum' }}
          </div>
          <h1 class="text-4xl md:text-6xl font-black text-foreground mb-6">
            <span v-if="pageTitle">{{ pageTitle }}</span>
            <template v-else>
              Membangun <span class="text-primary italic">Keunggulan</span> Intelektual
            </template>
          </h1>
          <p class="text-lg text-muted-foreground max-w-2xl mx-auto leading-relaxed">
            {{ pageSubtitle || 'Sistem pembelajaran yang inovatif, berpusat pada siswa, dan didukung oleh kurikulum yang relevan dengan tantangan masa depan.' }}
          </p>
        </div>
      </section>

      <!-- Content Sections -->
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Sidebar / Navigation -->
          <div class="lg:col-span-1 space-y-6">
            <div class="p-6 rounded-[2rem] bg-card border border-border shadow-sm sticky top-24">
              <h3 class="font-bold text-xl mb-6 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center">
                  <MenuIcon class="w-4 h-4 text-primary" />
                </div>
                Navigasi Akademik
              </h3>
              <div class="space-y-2">
                <button 
                  v-for="tab in tabs" 
                  :key="tab.id"
                  :class="[
                    'w-full flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 font-medium text-sm',
                    activeTab === tab.id 
                      ? 'bg-primary text-primary-foreground shadow-lg shadow-primary/20 scale-[1.02]' 
                      : 'text-muted-foreground hover:bg-muted'
                  ]"
                  @click="activeTab = tab.id"
                >
                  <component
                    :is="tab.icon"
                    class="w-5 h-5"
                  />
                  {{ tab.label }}
                </button>
              </div>
            </div>
          </div>

          <!-- Main Content area -->
          <div class="lg:col-span-2 space-y-8 min-h-[400px]">
            <transition 
              name="fade"
              mode="out-in"
              enter-active-class="transition duration-300 ease-out"
              enter-from-class="opacity-0 translate-y-4"
              enter-to-class="opacity-100 translate-y-0"
              leave-active-class="transition duration-200 ease-in"
              leave-from-class="opacity-100"
              leave-to-class="opacity-0"
            >
              <div
                :key="activeTab"
                class="space-y-8"
              >
                <!-- Content Stability Wrapper -->
                <div class="academic-tab-content">
                  <!-- Kurikulum View -->
                  <div
                    v-if="activeTab === 'curriculum'"
                    class="space-y-8"
                  >
                    <div class="p-8 rounded-[3rem] bg-gradient-to-br from-card to-muted border border-border relative overflow-hidden group">
                      <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:scale-110 transition-transform duration-700">
                        <GraduationCap class="w-32 h-32" />
                      </div>
                      <h2 class="text-3xl font-bold mb-4">
                        Kurikulum Merdeka
                      </h2>
                      <p class="text-muted-foreground leading-relaxed mb-6">
                        Implementasi kurikulum yang memberikan fleksibilitas bagi pendidik untuk menciptakan pembelajaran yang berkualitas yang sesuai dengan kebutuhan dan lingkungan belajar peserta didik.
                      </p>
                      <ul class="space-y-4">
                        <li
                          v-for="(point, index) in curriculumPoints"
                          :key="index"
                          class="flex items-start gap-3"
                        >
                          <CheckCircle2 class="w-5 h-5 text-primary shrink-0 mt-0.5" />
                          <span class="text-sm font-medium text-foreground">{{ point }}</span>
                        </li>
                      </ul>
                    </div>
                  </div>

                  <!-- Page Body Fallback (from CMS) -->
                  <SafeHtml
                    v-else-if="pageData?.body"
                    class="p-8 rounded-[2rem] bg-card border border-border prose prose-lg prose-primary max-w-none"
                    :html="pageData.body"
                    mode="cms"
                  />
                  <!-- Empty State if no specific data -->
                  <div
                    v-else
                    class="p-20 text-center rounded-[3rem] bg-muted/30 border-2 border-dashed border-border/50"
                  >
                    <div class="w-16 h-16 rounded-2xl bg-muted flex items-center justify-center mx-auto mb-6">
                      <FileText class="w-8 h-8 text-muted-foreground" />
                    </div>
                    <h3 class="text-xl font-bold mb-2">
                      Konten Sedang Disiapkan
                    </h3>
                    <p class="text-muted-foreground">
                      Bagian ini akan segera diperbarui dengan informasi terbaru.
                    </p>
                  </div>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </div>
    </template>

    <!-- If Disabled -->
    <PageDisabled 
      v-else 
      :title="(pageTitle as string) || 'Akademik'" 
      :message="(getSetting('disabled_page_message') as string)" 
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, markRaw } from 'vue';
import SafeHtml from '@/modules/Core/components/ui/SafeHtml.vue';
import { useRouter } from 'vue-router';
import { useTheme } from '@/shared/composables/useTheme';
import PageDisabled from './components/PageDisabled.vue';
import api from '@/engine/api/client';
import { useThemeDataBindings } from '@/modules/Cms/composables/useThemeDataBindings';
import BookOpen from 'lucide-vue-next/dist/esm/icons/book-open.js';
import GraduationCap from 'lucide-vue-next/dist/esm/icons/graduation-cap.js';
import Calendar from 'lucide-vue-next/dist/esm/icons/calendar.js';
import Users from 'lucide-vue-next/dist/esm/icons/users.js';
import CheckCircle2 from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import MenuIcon from 'lucide-vue-next/dist/esm/icons/menu.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';

const { getSetting } = useTheme();
const router = useRouter();
const ACADEMIC_CONTENT_CACHE_KEY = 'janari_academic_content_slug_v1';
const ACADEMIC_CONTENT_MISS_KEY = 'janari_academic_content_miss_v1';
const ACADEMIC_CONTENT_MISS_TTL_MS = 10 * 60 * 1000;
let academicContentPromise: Promise<AcademicPageData | null> | null = null;

const isEnabled = computed(() => getSetting('enable_academic', true));
const behavior = computed(() => getSetting('disabled_page_behavior', 'message'));
const pageTitle = computed(() => getSetting('page_academic_title') as string);
const pageSubtitle = computed(() => getSetting('page_academic_subtitle') as string);

interface AcademicPageData {
    body?: string;
    [key: string]: any;
}

const pageData = ref<AcademicPageData | null>(null);
const activeTab = ref('curriculum');

// Optional: Binding for sub-categories if we want to make tabs dynamic
// For now we keep the structure but allow individual content blocks to be dynamic
const { data: curriculumItems } = useThemeDataBindings('academic', 'curriculum');

const tabs = [
  { id: 'curriculum', label: 'Kurikulum', icon: markRaw(GraduationCap) },
  { id: 'subjects', label: 'Mata Pelajaran', icon: markRaw(BookOpen) },
  { id: 'calendar', label: 'Kalender Akademik', icon: markRaw(Calendar) },
  { id: 'teachers', label: 'Tenaga Pengajar', icon: markRaw(Users) },
];

const defaultCurriculumPoints = [
  'Pembelajaran berbasis Proyek (Pencapaian Profil Pelajar Pancasila)',
  'Fokus pada materi esensial (Literasi dan Numerasi)',
  'Fleksibilitas bagi guru untuk melakukan pengajaran yang terdiferensiasi',
  'Pemanfaatan Teknologi Informasi dalam pembelajaran'
];

const curriculumPoints = computed(() => {
  if (curriculumItems.value.length > 0) {
    return curriculumItems.value.map((item: any) => item.title);
  }
  return defaultCurriculumPoints;
});

const readMissState = () => {
  try {
    const raw = sessionStorage.getItem(ACADEMIC_CONTENT_MISS_KEY);
    if (!raw) return null;
    const parsed = JSON.parse(raw) as { expiresAt?: number };
    if (!parsed?.expiresAt || parsed.expiresAt <= Date.now()) {
      sessionStorage.removeItem(ACADEMIC_CONTENT_MISS_KEY);
      return null;
    }
    return parsed;
  } catch {
    return null;
  }
};

const writeMissState = () => {
  try {
    sessionStorage.setItem(
      ACADEMIC_CONTENT_MISS_KEY,
      JSON.stringify({ expiresAt: Date.now() + ACADEMIC_CONTENT_MISS_TTL_MS })
    );
  } catch {
    // ignore storage errors
  }
};

const clearMissState = () => {
  try {
    sessionStorage.removeItem(ACADEMIC_CONTENT_MISS_KEY);
  } catch {
    // ignore storage errors
  }
};

const readCachedSlug = (): string | null => {
  try {
    return sessionStorage.getItem(ACADEMIC_CONTENT_CACHE_KEY);
  } catch {
    return null;
  }
};

const writeCachedSlug = (slug: string) => {
  try {
    sessionStorage.setItem(ACADEMIC_CONTENT_CACHE_KEY, slug);
  } catch {
    // ignore storage errors
  }
};

const loadAcademicContent = async (): Promise<AcademicPageData | null> => {
  if (readMissState()) return null;
  if (academicContentPromise) return academicContentPromise;

  academicContentPromise = (async () => {
    const cachedSlug = readCachedSlug();
    const orderedCandidates = cachedSlug
      ? [cachedSlug, 'akademik', 'academic', 'profil-akademik'].filter((value, index, arr) => arr.indexOf(value) === index)
      : ['akademik', 'academic', 'profil-akademik'];

    for (const slug of orderedCandidates) {
      try {
        const response = await api.get(`/ja/contents/${slug}`);
        writeCachedSlug(slug);
        clearMissState();
        return response.data as AcademicPageData;
      } catch (error: any) {
        if (error?.response?.status !== 404) {
          console.warn(`[Academic] failed to fetch slug "${slug}"`, error);
        }
      }
    }

    writeMissState();
    return null;
  })();

  try {
    return await academicContentPromise;
  } finally {
    academicContentPromise = null;
  }
};

onMounted(async () => {
  if (!isEnabled.value && behavior.value === 'redirect') {
    router.push('/');
    return;
  }
  
  if (!isEnabled.value) return;

  pageData.value = await loadAcademicContent();
});

</script>
