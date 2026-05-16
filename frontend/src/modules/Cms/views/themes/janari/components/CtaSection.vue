<template>
  <section class="py-40 bg-muted/20 relative overflow-hidden">
    <div class="container mx-auto px-6">
      <div
        ref="cardRef"
        class="max-w-7xl mx-auto p-12 md:p-32 border border-border bg-card/50 backdrop-blur-3xl relative overflow-hidden"
      >
        <div class="relative z-10 flex flex-col items-center text-center">
          <span class="inline-flex items-center px-4 py-2 bg-background/80 backdrop-blur-md border border-primary/20 text-[9px] font-black tracking-[0.5em] uppercase text-primary mb-12 rounded-full">
            <span class="w-1 h-1 bg-primary rounded-full mr-2" />
            {{ badgeText }}
          </span>
          <h2
            ref="titleRef"
            class="text-5xl md:text-9xl font-heading font-black mb-16 leading-[0.85] uppercase tracking-tighter text-foreground"
          >
            <JanariSplitText :text="titleText" />
          </h2>
          <p
            v-if="subtitleText"
            class="max-w-2xl text-foreground/60 mb-16 text-lg leading-relaxed"
          >
            {{ subtitleText }}
          </p>
          <div class="flex flex-col sm:flex-row items-center gap-10">
            <router-link
              to="/ppdb"
              class="px-10 py-4 text-xs font-bold tracking-[0.5px] uppercase bg-primary text-primary-foreground rounded-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 cubic-bezier(0.37, 0.01, 0, 0.98)"
            >
              {{ buttonText }}
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import JanariSplitText from './JanariSplitText.vue'
import { useTheme } from '@/modules/Cms/composables/useTheme'
import { useThemeMotion } from '@/modules/Cms/composables/useThemeMotion'

const { getSetting } = useTheme()
const { scaleReveal, splitTextRevealSafe } = useThemeMotion()

const cardRef = ref<HTMLElement>()
const titleRef = ref<HTMLElement>()

const badgeText = computed(() => (getSetting('cta_badge') as string) || 'THE NEXT CHAPTER STARTS NOW')
const titleText = computed(() => (getSetting('cta_title') as string) || 'JADILAH BAGIAN DARI KAMI')
const subtitleText = computed(() => (getSetting('cta_subtitle') as string) || '')
const buttonText = computed(() => (getSetting('cta_button_text') as string) || 'REGISTER NOW')

onMounted(() => {
    if (cardRef.value) scaleReveal(cardRef.value)
    if (titleRef.value) splitTextRevealSafe(titleRef.value, { delay: 0.2, stagger: 0.05 })
})
</script>
