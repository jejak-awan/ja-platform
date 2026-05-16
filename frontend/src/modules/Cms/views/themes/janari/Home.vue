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
          <div
            ref="principalRef"
            class="below-fold-section"
          >
            <PrincipalProfile
              v-if="isSectionActive('principal') && mountedSections.principal"
            />
          </div>

          <div
            ref="newsRef"
            class="below-fold-section"
          >
            <UpdateInformation
              v-if="isSectionActive('news') && mountedSections.news"
            />
          </div>

          <div
            ref="programsRef"
            class="below-fold-section"
          >
            <MajorsSection
              v-if="isSectionActive('programs') && mountedSections.programs"
            />
          </div>

          <div
            ref="statsRef"
            class="below-fold-section"
          >
            <StatsSection
              v-if="isSectionActive('stats') && mountedSections.stats"
            />
          </div>

          <div
            ref="partnersRef"
            class="below-fold-section"
          >
            <PartnersSection
              v-if="isSectionActive('partners') && mountedSections.partners"
            />
          </div>

          <div
            ref="testimonialsRef"
            class="below-fold-section"
          >
            <Testimonials
              v-if="isSectionActive('testimonials') && mountedSections.testimonials"
              :items="testimonialData"
            />
          </div>

          <div
            ref="ctaRef"
            class="below-fold-section"
          >
            <CtaSection
              v-if="isSectionActive('cta') && mountedSections.cta"
            />
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, nextTick, onBeforeUnmount, defineAsyncComponent } from 'vue'
import SafeHtml from '@/modules/System/components/ui/SafeHtml.vue'
import api from '@/engine/api/client'

// Above-the-fold (LCP): static import
import Hero from './components/Hero.vue'

const PrincipalProfile = defineAsyncComponent(() => import('@/modules/School/components/public/PrincipalProfile.vue'))
const Testimonials = defineAsyncComponent(() => import('./components/Testimonials.vue'))
const UpdateInformation = defineAsyncComponent(() => import('./components/UpdateInformation.vue'))
const MajorsSection = defineAsyncComponent(() => import('@/modules/School/components/public/MajorsSection.vue'))
const StatsSection = defineAsyncComponent(() => import('@/modules/School/components/public/StatsSection.vue'))
const PartnersSection = defineAsyncComponent(() => import('./components/PartnersSection.vue'))
const CtaSection = defineAsyncComponent(() => import('./components/CtaSection.vue'))

// Helpers
import { useThemeMotion } from '@/modules/Cms/composables/useThemeMotion'
import { useThemeDataBindings } from '@/modules/Cms/composables/useThemeDataBindings'
import { useTheme } from '@/modules/Cms/composables/useTheme';

const { getSetting } = useTheme();
const { ScrollTrigger } = useThemeMotion()

interface Testimonial {
    name: string;
    role: string;
    content: string;
    avatar?: string;
}

interface CmsPageData {
    content?: string;
    [key: string]: any;
}

const isComponentActive = ref(true);
let sectionObserver: IntersectionObserver | null = null;

const { data: dynamicTestimonials } = useThemeDataBindings('testimonials', 'items')

const activeSections = computed(() => (getSetting('home_sections') as string[]) || ['hero', 'principal', 'programs', 'stats', 'partners', 'testimonials', 'news', 'cta']);
const isSectionActive = (section: string) => activeSections.value.includes(section);

const pageData = ref<CmsPageData | null>(null)
const testimonialData = computed<Testimonial[]>(() => dynamicTestimonials.value.map((item: any) => ({ 
    name: item.title, 
    role: item.excerpt || 'Visi Utama', 
    content: item.body || item.content,
    avatar: item._raw?.featured_image || item._raw?.thumbnail || undefined
})))

const principalRef = ref<HTMLElement | null>(null);
const newsRef = ref<HTMLElement | null>(null);
const programsRef = ref<HTMLElement | null>(null);
const statsRef = ref<HTMLElement | null>(null);
const partnersRef = ref<HTMLElement | null>(null);
const testimonialsRef = ref<HTMLElement | null>(null);
const ctaRef = ref<HTMLElement | null>(null);

const mountedSections = ref<Record<string, boolean>>({
    principal: false,
    news: false,
    programs: false,
    stats: false,
    partners: false,
    testimonials: false,
    cta: false,
});

const observeSectionMount = () => {
    if (typeof window === 'undefined' || !('IntersectionObserver' in window)) {
        mountedSections.value = {
            principal: true,
            news: true,
            programs: true,
            stats: true,
            partners: true,
            testimonials: true,
            cta: true,
        };
        return;
    }

    const map: Array<{ key: keyof typeof mountedSections.value; el: HTMLElement | null }> = [
        { key: 'principal', el: principalRef.value },
        { key: 'news', el: newsRef.value },
        { key: 'programs', el: programsRef.value },
        { key: 'stats', el: statsRef.value },
        { key: 'partners', el: partnersRef.value },
        { key: 'testimonials', el: testimonialsRef.value },
        { key: 'cta', el: ctaRef.value },
    ];

    sectionObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            const key = (entry.target as HTMLElement).dataset.sectionKey as keyof typeof mountedSections.value | undefined;
            if (!key) return;
            mountedSections.value[key] = true;
            sectionObserver?.unobserve(entry.target);
        });
    }, { rootMargin: '300px 0px' });

    map.forEach(({ key, el }) => {
        if (!el || mountedSections.value[key]) return;
        el.dataset.sectionKey = key;
        sectionObserver?.observe(el);
    });
};

onMounted(() => {
    isComponentActive.value = true;
    void api.get('/public/cms/contents/home')
        .then((res) => {
            if (isComponentActive.value) {
                pageData.value = res.data;
            }
        })
        .catch((err: any) => {
            if (err.name === 'CanceledError' || err.code === 'ERR_CANCELED' || err.message?.includes('aborted')) return;
            console.error('[Janari] Home Load Error:', err);
        })
        .finally(() => {
            void nextTick().then(() => {
                observeSectionMount();
                setTimeout(() => {
                    if (typeof ScrollTrigger !== 'undefined') {
                        ScrollTrigger.refresh();
                    }
                }, 800);
            });
        });
})

onBeforeUnmount(() => {
    if (sectionObserver) {
        sectionObserver.disconnect();
        sectionObserver = null;
    }
    isComponentActive.value = false
})
</script>

<style scoped>
.cms-content :deep(p) { margin-bottom: 1rem; }
.below-fold-section {
  content-visibility: auto;
  contain-intrinsic-size: 800px;
}
</style>
