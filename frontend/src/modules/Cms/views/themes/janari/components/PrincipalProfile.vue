<template>
  <section class="principal-editorial bg-background overflow-hidden border-y border-border">
    <div class="grid grid-cols-1 lg:grid-cols-2">
      <!-- LEFT CARD: THE HEADMASTER VISUAL -->
      <div
        ref="leftCard"
        class="relative min-h-[500px] md:min-h-[650px] overflow-hidden group border-b lg:border-b-0 lg:border-r border-border"
      >
        <div class="absolute inset-0 z-0 overflow-hidden">
          <!-- Premium High-Performance Animated Background (Replaces Heavy Image) -->
          <div 
            class="absolute inset-0 transition-opacity duration-1000"
            :class="[bgStyle === 'mesh' ? 'opacity-100' : 'opacity-0']"
          >
            <div class="mesh-gradient-container">
              <div class="mesh-ball ball-1" />
              <div class="mesh-ball ball-2" />
              <div class="mesh-ball ball-3" />
            </div>
          </div>

          <!-- Legacy Image Support (if specifically selected) -->
          <img 
            v-if="bgStyle === 'image' && sectionBackgroundUrl"
            :src="sanitizeImageUrl(sectionBackgroundUrl)" 
            class="w-full h-full object-cover grayscale opacity-40 group-hover:scale-105 transition-transform duration-1000"
            alt="Latar belakang section kepala sekolah"
            width="1200"
            height="800"
            loading="lazy"
            decoding="async"
            sizes="100vw"
          >

          <!-- Fallback / Gradient Overlay -->
          <div
            class="absolute inset-0 bg-gradient-to-br from-primary/35 via-primary/15 to-black/75 dark:from-primary/45 dark:via-primary/20 dark:to-black/88"
          />
        </div>

        <!-- Headmaster Portrait + themed glass frame (light/dark) -->
        <div class="absolute inset-0 z-10 flex items-center justify-center p-8 md:p-12">
          <div
            class="relative w-full max-w-[300px] md:max-w-[400px] aspect-[3/4] rounded-xl p-2.5 md:p-3 shadow-2xl
              bg-gradient-to-b from-primary/20 via-primary/10 to-background/30
              dark:from-primary/30 dark:via-primary/15 dark:to-black/40
              ring-1 ring-inset ring-primary/25 dark:ring-primary/40
              backdrop-blur-sm"
          >
            <div
              class="relative h-full w-full overflow-hidden rounded-lg
                ring-1 ring-inset ring-foreground/10 dark:ring-white/15
                shadow-inner bg-background/5 dark:bg-black/20"
            >
              <img 
                v-if="photo"
                :src="sanitizeImageUrl(photo)" 
                class="h-full w-full object-cover grayscale-[0.3] brightness-[0.92] group-hover:grayscale-0 group-hover:brightness-100 transition-all duration-700 dark:brightness-[0.88] dark:group-hover:brightness-100"
                alt="Kepala Sekolah"
                width="400"
                height="534"
                loading="lazy"
                decoding="async"
                sizes="(max-width: 768px) 75vw, 400px"
              >
              <!-- Non-image fallback for principal portrait -->
              <div 
                v-else
                class="h-full w-full flex flex-col items-center justify-center bg-muted/30 text-muted-foreground/40"
              >
                <User class="w-20 h-20 mb-4 opacity-20" />
                <span class="text-[10px] font-black tracking-[0.3em] uppercase opacity-40">Identity Undefined</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Headmaster Badge (L'Arc Style) -->
        <div class="absolute top-12 left-12 z-20">
          <div class="bg-primary text-primary-foreground px-4 py-2 flex items-center gap-3 shadow-xl">
            <span class="w-6 h-px bg-primary-foreground/40" />
            <span class="text-[10px] font-black tracking-[0.4em] uppercase">HEADMASTER</span>
          </div>
        </div>

        <!-- Bottom Name Label (Mobile primarily) -->
        <div class="absolute bottom-12 left-12 z-20 lg:hidden">
          <p class="text-3xl font-heading font-black text-white uppercase tracking-tighter">
            {{ name }}
          </p>
          <p class="text-[10px] font-black tracking-[0.3em] text-primary mt-2 uppercase">
            Official School Principal
          </p>
        </div>
      </div>

      <!-- RIGHT CARD: THE LATEST MESSAGE -->
      <div
        ref="rightCard"
        class="relative min-h-[500px] md:min-h-[650px] bg-muted/30 p-8 md:p-16 lg:p-24 flex flex-col justify-center border-l border-border/50"
      >
        <!-- Message Badge -->
        <div class="mb-8 md:mb-12">
          <span class="text-black dark:text-white text-[10px] font-black tracking-[0.5em] uppercase block mb-4">LATEST MESSAGE</span>
          <h2
            ref="headingRef"
            class="text-3xl md:text-6xl font-heading font-black uppercase tracking-tighter leading-[0.9] text-foreground"
          >
            <JanariSplitText text="SELAMAT DATANG DI SMART SCHOOL" />
          </h2>
          <div class="w-12 md:w-16 h-1 bg-primary mt-6 md:mt-8" />
        </div>

        <!-- Dynamic Message Content -->
        <div
          ref="messageContainer"
          class="max-w-xl"
        >
          <div class="text-foreground/60 text-base md:text-lg font-medium leading-relaxed mb-12 italic">
            "{{ message || defaultMessage }}"
          </div>
            
          <!-- Principal Identity & Signature -->
          <div class="flex flex-col gap-1 border-l-2 border-primary pl-4 md:pl-6 mb-12 md:mb-16">
            <span class="text-foreground text-lg md:text-xl font-heading font-black uppercase tracking-tight">{{ name }}</span>
            <span class="text-[9px] md:text-[10px] font-black tracking-[0.3em] text-black/70 dark:text-white/80 uppercase italic">Signature of Excellence</span>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 sm:gap-6">
            <router-link
              :to="principalMessageLink"
              class="px-10 py-5 bg-foreground text-background font-black text-[10px] tracking-[0.35em] text-center uppercase hover:bg-primary hover:text-primary-foreground transition-all duration-300 shadow-xl"
            >
              BACA SAMBUTAN
            </router-link>
            <router-link
              :to="principalBiographyLink"
              class="px-10 py-5 border border-border text-black dark:text-white font-black text-[10px] tracking-[0.4em] text-center uppercase hover:text-foreground hover:border-foreground transition-all duration-300"
            >
              BIOGRAFI
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import JanariSplitText from './JanariSplitText.vue'
import { computed, ref, onMounted, nextTick } from 'vue'
import { useTheme } from '@/composables/useTheme'
import { useThemeMotion } from '@/composables/useThemeMotion'
import User from 'lucide-vue-next/dist/esm/icons/user.js'

const { getSetting } = useTheme()

const props = defineProps<{
  principalName?: string;
  principalPhoto?: string;
  principalMessage?: string;
}>();

const name = computed(() => props.principalName || (getSetting('principal_name') as string) || 'Dr. Ahmad Fauzi');
const message = computed(() => props.principalMessage || (getSetting('principal_message') as string));
const photo = computed(() => props.principalPhoto || (getSetting('principal_image') as string));

const buildContentPath = (slug: string): string => {
    const clean = slug.trim().replace(/^\/+/, '');
    return `/${clean}`;
};

const normalizeInternalLink = (value: string): string => {
    const trimmed = value.trim();
    if (!trimmed) return trimmed;

    // Keep external/protocol-specific links as-is.
    if (!/^https?:\/\//i.test(trimmed)) {
        return trimmed;
    }

    try {
        const parsed = new URL(trimmed);
        if (typeof window !== 'undefined' && parsed.origin === window.location.origin) {
            return `${parsed.pathname}${parsed.search}${parsed.hash}`;
        }
    } catch {
        return trimmed;
    }

    return trimmed;
};

const resolveThemeLink = (
    directLinkKey: string,
    slugKey: string,
    fallbackSlug: string
): string => {
    const direct = (getSetting(directLinkKey) as string | undefined)?.trim();
    if (direct) return normalizeInternalLink(direct);

    const slug = ((getSetting(slugKey) as string | undefined)?.trim()) || fallbackSlug;
    return buildContentPath(slug);
};

const principalMessageLink = computed(() =>
    resolveThemeLink('principal_message_link', 'principal_message_page_slug', 'sambutan-kepala-sekolah')
);

const principalBiographyLink = computed(() =>
    resolveThemeLink('principal_biography_link', 'principal_biography_page_slug', 'profil')
);

/** Principal section background media, then school_image if unset; template uses placeholders when empty. */
const bgStyle = computed(() => (getSetting('principal_background_style') as string) || 'mesh');

const sectionBackgroundUrl = computed(() => {
    const dedicated = getSetting('principal_section_background') as string | undefined;
    return dedicated || (getSetting('school_image') as string) || '';
});

// Remove unused fallback logic and replace with null-safety
const sanitizeImageUrl = (url: string | null | undefined): string | undefined => {
    if (!url) return undefined
    if (url.includes('unsplash.com') || url.includes('placehold.co')) return undefined
    return url
}

const defaultMessage = "Sesuai dengan visi kami, kami berkomitmen untuk menciptakan ekosistem pendidikan yang tidak hanya unggul secara akademis, tetapi juga secara karakter dan kecakapan di era digital industri modern.";

// GSAP
const { splitTextRevealSafe, staggerChildren, createTimeline } = useThemeMotion()
const leftCard = ref<HTMLElement>()
const rightCard = ref<HTMLElement>()
const headingRef = ref<HTMLElement>()
const messageContainer = ref<HTMLElement>()
const isAnimated = ref(false)

onMounted(() => {
    if (isAnimated.value) return
    isAnimated.value = true

    const init = async () => {
        await nextTick()
        if (!leftCard.value) return

        const tl = createTimeline({
            scrollTrigger: {
                trigger: leftCard.value,
                start: 'top 80%',
            }
        })

        if (leftCard.value) {
            tl.from(leftCard.value, { xPercent: -20, opacity: 0, duration: 1.2, ease: 'expo.out' }, 0)
        }

        if (rightCard.value) {
            tl.from(rightCard.value, { xPercent: 20, opacity: 0, duration: 1.2, ease: 'expo.out' }, 0.2)
        }

        if (headingRef.value) {
            splitTextRevealSafe(headingRef.value, { delay: 0.8, stagger: 0.06 })
        }

        if (messageContainer.value) {
            staggerChildren(messageContainer.value, '.text-white, .flex, .flex-row', {
                distance: 30,
                stagger: 0.1,
                delay: 1.2,
                duration: 0.8
            })
        }
    }
    init()
})
</script>

<style scoped>
.mesh-gradient-container {
  position: absolute;
  inset: 0;
  background: var(--theme-background-color, #000);
  filter: blur(80px);
  opacity: 0.4;
  overflow: hidden;
}

.mesh-ball {
  position: absolute;
  border-radius: 50%;
  filter: blur(40px);
  animation: mesh-float 20s infinite alternate ease-in-out;
}

.ball-1 {
  width: 60%;
  height: 60%;
  background: hsl(var(--primary));
  top: -10%;
  left: -10%;
}

.ball-2 {
  width: 50%;
  height: 50%;
  background: hsl(var(--primary) / 0.5);
  bottom: -10%;
  right: -10%;
  animation-delay: -5s;
}

.ball-3 {
  width: 40%;
  height: 40%;
  background: hsl(var(--primary) / 0.3);
  top: 30%;
  left: 30%;
  animation-delay: -10s;
}

@keyframes mesh-float {
  0% { transform: translate(0, 0) rotate(0deg); }
  50% { transform: translate(10%, 15%) rotate(90deg); }
  100% { transform: translate(-5%, 5%) rotate(180deg); }
}

.principal-editorial { border-collapse: separate; }
.text-sharp { -webkit-font-smoothing: antialiased; text-rendering: optimizeLegibility; }
</style>


