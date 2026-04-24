<template>
  <div class="min-h-screen flex flex-col">
    <div class="flex-1 flex flex-col">
      <!-- Dynamic Content if exists (e.g. from CMS page editor) -->
      <SafeHtml 
        v-if="pageData && pageData.content" 
        class="cms-content"
        :html="pageData.content"
        mode="cms"
      />
        
      <!-- Default Janari home: render immediately to avoid full-viewport layout swap (CLS) -->
      <div
        v-else
        class="flex-1 flex flex-col"
      >
        <section class="flex-1 flex flex-col">
          <!-- LCP-critical: keep eager -->
          <Hero v-show="isSectionActive('hero')" />

          <!-- Below-fold: async chunks reduce initial JS + long tasks -->
          <PrincipalProfile v-show="isSectionActive('principal')" />

          <UpdateInformation v-show="isSectionActive('news')" />

          <MajorsSection v-show="isSectionActive('programs')" />

          <StatsSection v-show="isSectionActive('stats')" />

          <PartnersSection v-show="isSectionActive('partners')" />

          <Testimonials
            v-show="isSectionActive('testimonials')"
            :items="testimonialData"
          />

          <CtaSection v-show="isSectionActive('cta')" />
        </section>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, nextTick, onBeforeUnmount, defineAsyncComponent } from 'vue'
import SafeHtml from '@/modules/Core/components/ui/SafeHtml.vue'
import api from '@/services/api'

// Above-the-fold (LCP): static import
import Hero from './components/Hero.vue'

const PrincipalProfile = defineAsyncComponent(() => import('./components/PrincipalProfile.vue'))
const Testimonials = defineAsyncComponent(() => import('./components/Testimonials.vue'))
const UpdateInformation = defineAsyncComponent(() => import('./components/UpdateInformation.vue'))
const MajorsSection = defineAsyncComponent(() => import('./components/MajorsSection.vue'))
const StatsSection = defineAsyncComponent(() => import('./components/StatsSection.vue'))
const PartnersSection = defineAsyncComponent(() => import('./components/PartnersSection.vue'))
const CtaSection = defineAsyncComponent(() => import('./components/CtaSection.vue'))

// Helpers
import { useGsapAnimations } from '@/composables/useGsapAnimations'
import { useThemeDataBindings } from '@/modules/Cms/composables/useThemeDataBindings'
import { useTheme } from '@/composables/useTheme';

const { getSetting } = useTheme();
const { ScrollTrigger } = useGsapAnimations()

interface Testimonial {
    name: string;
    role: string;
    content: string;
    image: string;
}

interface CmsPageData {
    content?: string;
    [key: string]: any;
}

const isComponentActive = ref(true);

const { data: dynamicTestimonials } = useThemeDataBindings('testimonials', 'items')

const activeSections = computed(() => (getSetting('home_sections') as string[]) || ['hero', 'principal', 'programs', 'stats', 'partners', 'testimonials', 'news', 'cta']);
const isSectionActive = (section: string) => activeSections.value.includes(section);

const pageData = ref<CmsPageData | null>(null)
const testimonialData = computed<Testimonial[]>(() => dynamicTestimonials.value.map((item: any) => ({ 
    name: item.title, 
    role: item.excerpt || 'Visi Utama', 
    content: item.body || item.content,
    image: item._raw?.featured_image || item._raw?.thumbnail || '/assets/themes/janari/avatar-placeholder.png'
})))

onMounted(() => {
    isComponentActive.value = true;
    void api.get('/ja/contents/home')
        .then((res) => {
            if (isComponentActive.value) {
                pageData.value = res.data;
            }
        })
        .catch((err) => {
            console.error('[Janari] Home Load Error:', err);
        })
        .finally(() => {
            void nextTick().then(() => {
                setTimeout(() => {
                    if (typeof ScrollTrigger !== 'undefined') {
                        ScrollTrigger.refresh();
                    }
                }, 800);
            });
        });
})

onBeforeUnmount(() => {
    isComponentActive.value = false
})
</script>

<style scoped>
.cms-content :deep(p) { margin-bottom: 1rem; }
</style>
