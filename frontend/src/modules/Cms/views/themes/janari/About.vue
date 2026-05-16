<template>
  <div class="min-h-screen flex flex-col">
    <div
      v-if="!isEnabled"
      class="flex-1"
    >
      <PageDisabled 
        :title="(pageTitle as string) || 'Tentang Kami'" 
        :message="(getSetting('disabled_page_message') as string)" 
      />
    </div>

    <div
      v-else-if="loading"
      class="flex-1 flex items-center justify-center min-h-[60vh]"
    >
      <div class="flex flex-col items-center gap-4">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary" />
        <span class="text-muted-foreground text-sm">Loading...</span>
      </div>
    </div>
    
    <template v-else>
      <div class="flex-1">
        <!-- Page Body Content if available -->
        <SafeHtml
          v-if="pageData && pageData.body"
          class="container mx-auto px-4 py-16 cms-content"
          :html="pageData.body"
          mode="cms"
        />

        <!-- Header -->
        <section
          ref="headerSection"
          class="py-24 bg-gradient-to-b from-primary/10 to-background border-b border-border/50"
        >
          <div class="container mx-auto px-4 text-center">
            <span class="motion-fade text-primary font-bold tracking-wider uppercase text-sm mb-4 block">{{ pageTitle || 'Profil Sekolah' }}</span>
            <h1
              ref="aboutTitle"
              class="text-4xl md:text-6xl font-extrabold mb-6 text-foreground"
            >
              <JanariSplitText :text="pageTitle || 'sekolahk2.id'" />
            </h1>
            <p class="motion-fade text-xl text-muted-foreground max-w-2xl mx-auto leading-relaxed font-medium">
              {{ pageSubtitle || 'Membangun masa depan generasi vokasi yang unggul, kompeten, dan berdaya saing global.' }}
            </p>
          </div>
        </section>

        <!-- Mission/Content -->
        <section class="py-20 bg-background">
          <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
              <div
                ref="contentLeft"
                class="space-y-8"
              >
                <div class="motion-fade space-y-4">
                  <h2 class="text-3xl font-bold text-foreground">
                    Visi & Misi Kami
                  </h2>
                  <p class="text-muted-foreground text-lg leading-relaxed">
                    Visi kami adalah menjadi pusat pendidikan kejuruan yang menghasilkan lulusan berakhlak mulia, 
                    cakap dalam teknologi, dan siap menjawab tantangan industri masa kini.
                  </p>
                  <p class="text-muted-foreground text-lg leading-relaxed">
                    Berdiri sejak tahun 2004, sekolahk2.id terus berinovasi dalam kurikulum 
                    dan fasilitas untuk memberikan pengalaman belajar terbaik bagi putra-putri daerah.
                  </p>
                </div>
                            
                <!-- Stats -->
                <div
                  ref="aboutStats"
                  class="grid grid-cols-3 gap-8 pt-8 border-t border-border"
                >
                  <div class="motion-stat-item">
                    <div class="text-4xl font-black text-primary">
                      20+
                    </div>
                    <div class="text-xs font-bold text-muted-foreground uppercase mt-2 tracking-widest">
                      Tahun Berdiri
                    </div>
                  </div>
                  <div class="motion-stat-item">
                    <div class="text-4xl font-black text-primary">
                      1k+
                    </div>
                    <div class="text-xs font-bold text-muted-foreground uppercase mt-2 tracking-widest">
                      Siswa Aktif
                    </div>
                  </div>
                  <div class="motion-stat-item">
                    <div class="text-4xl font-black text-primary">
                      50+
                    </div>
                    <div class="text-xs font-bold text-muted-foreground uppercase mt-2 tracking-widest">
                      Guru Ahli
                    </div>
                  </div>
                </div>
              </div>
                        
              <!-- Image Layout -->
              <div
                ref="imageBlock"
                class="relative"
              >
                <div class="absolute -inset-4 bg-primary/10 rounded-3xl -rotate-2" />
                <div class="relative rounded-3xl bg-muted overflow-hidden h-[500px] shadow-2xl border border-border">
                  <div class="absolute inset-0 flex items-center justify-center text-muted-foreground font-bold">
                    Image Placeholder: Gedung SMKN 1 Cijulang
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Team Section (Kepala Sekolah & Staff) -->
        <section class="py-20 bg-muted/30">
          <div class="container mx-auto px-4 text-center">
            <span class="motion-fade text-primary font-bold tracking-wider uppercase text-sm mb-4 block">Manajemen Sekolah</span>
            <h2
              ref="teamTitle"
              class="text-3xl font-bold mb-12 text-foreground"
            >
              <JanariSplitText text="Struktur Manajemen" />
            </h2>
            <div
              ref="teamGrid"
              class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8"
            >
              <div
                v-for="member in teamMembers"
                :key="member.name"
                class="motion-team-card group"
              >
                <div class="w-full aspect-[3/4] bg-card rounded-2xl mb-4 overflow-hidden relative border border-border transition-all duration-300 group-hover:shadow-xl group-hover:-translate-y-1">
                  <div class="absolute inset-0 flex items-center justify-center text-muted-foreground text-xs p-4 text-center font-bold">
                    Photo Placeholder
                  </div>
                  <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
                </div>
                <h3 class="font-bold text-lg text-foreground">
                  {{ member.name }}
                </h3>
                <p class="text-sm text-primary font-semibold uppercase tracking-wider mt-1">
                  {{ member.role }}
                </p>
              </div>
            </div>
          </div>
        </section>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import JanariSplitText from './components/JanariSplitText.vue'
import { ref, onMounted, nextTick, computed } from 'vue'
import SafeHtml from '@/modules/System/components/ui/SafeHtml.vue'
import { useRouter } from 'vue-router'
import { logger } from '@/shared/utils/logger'
import { useTheme } from '@/modules/Cms/composables/useTheme'
import PageDisabled from './components/PageDisabled.vue'
import api from '@/engine/api/client'
import { useThemeMotion } from '@/modules/Cms/composables/useThemeMotion'

interface TeamMember {
    name: string;
    role: string;
    image: string;
}

import type { Content } from '@/modules/Cms/types/cms'

interface PageData extends Content {
    title: string;
}

const { getSetting } = useTheme()
const router = useRouter()
const { fadeInRight, splitTextRevealSafe, staggerChildren } = useThemeMotion()

const isEnabled = computed(() => getSetting('enable_about', true))
const behavior = computed(() => getSetting('disabled_page_behavior', 'message'))
const pageTitle = computed(() => getSetting('page_about_title') as string)
const pageSubtitle = computed(() => getSetting('page_about_subtitle') as string)

const pageData = ref<PageData | null>(null)
const loading = ref(true)
const isAnimated = ref(false)

// Template refs for GSAP
const headerSection = ref<HTMLElement>()
const aboutTitle = ref<HTMLElement>()
const contentLeft = ref<HTMLElement>()
const aboutStats = ref<HTMLElement>()
const imageBlock = ref<HTMLElement>()
const teamTitle = ref<HTMLElement>()
const teamGrid = ref<HTMLElement>()

// Team members data
const teamMembers: TeamMember[] = [
    { name: 'Ari Nurcahya', role: 'Lead Developer', image: '/images/fallback/team-1.png' },
    { name: 'Sarah Amira', role: 'Frontend Engineer', image: '/images/fallback/team-2.png' },
    { name: 'Budi Santoso', role: 'Backend Engineer', image: '/images/fallback/team-3.png' },
    { name: 'Maya Putri', role: 'UI/UX Designer', image: '/images/fallback/team-4.png' }
]

const initAnimations = () => {
    if (isAnimated.value) return
    isAnimated.value = true

    // Header section
    if (headerSection.value) {
        staggerChildren(headerSection.value, '.motion-fade', { distance: 30, stagger: 0.15 })
    }
    if (aboutTitle.value) {
        splitTextRevealSafe(aboutTitle.value, { delay: 0.2, stagger: 0.05 })
    }

    // Content left stagger
    if (contentLeft.value) {
        staggerChildren(contentLeft.value, '.motion-fade', { distance: 40, stagger: 0.2 })
    }

    // Stats stagger
    if (aboutStats.value) {
        staggerChildren(aboutStats.value, '.motion-stat-item', { distance: 30, stagger: 0.1 })
    }

    // Image parallax from right
    if (imageBlock.value) {
        fadeInRight(imageBlock.value, { distance: 60, duration: 0.9 })
    }

    // Team section
    if (teamTitle.value) {
        splitTextRevealSafe(teamTitle.value, { stagger: 0.04 })
    }
    if (teamGrid.value) {
        staggerChildren(teamGrid.value, '.motion-team-card', { distance: 50, stagger: 0.12, duration: 0.7 })
    }
}

onMounted(async () => {
  if (!isEnabled.value && behavior.value === 'redirect') {
    router.push('/')
    return
  }
  
  if (!isEnabled.value) {
    loading.value = false
    return
  }

  try {
    const response = await api.get('/public/cms/contents/about')
    pageData.value = response.data
  } catch (error) {
    logger.error('Failed to fetch about page:', error)
  } finally {
    loading.value = false
    await nextTick()
    initAnimations()
  }
})
</script>

