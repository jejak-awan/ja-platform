<template>
  <section class="py-32 bg-background border-collapse">
    <div class="container mx-auto px-6">
      <div class="mb-24 flex flex-col md:flex-row items-end justify-between gap-12">
        <div class="max-w-3xl">
          <span class="text-foreground font-black tracking-[0.5em] uppercase text-[10px] block mb-6">
            {{ badgeText }}
          </span>
          <h2 class="text-3xl md:text-8xl font-heading font-black leading-[0.85] uppercase tracking-tighter text-foreground">
            <JanariSplitText :text="titleText" />
          </h2>
        </div>
        <router-link
          to="/jurusan"
          class="px-8 py-3 border border-border text-[9px] font-black tracking-[0.4em] uppercase text-foreground/90 hover:text-foreground transition-all"
        >
          {{ buttonText }}
        </router-link>
      </div>
      
      <div
        ref="gridRef"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 border border-border divide-y md:divide-y-0 md:divide-x divide-border"
      >
        <div
          v-for="(major, idx) in items"
          :key="idx"
          class="motion-card group relative p-12 bg-card hover:bg-primary transition-all duration-700 cursor-pointer min-h-[400px] flex flex-col justify-between overflow-hidden"
        >
          <div class="relative z-10 flex justify-between">
            <div class="w-16 h-16 border border-border flex items-center justify-center group-hover:border-primary-foreground/25">
              <component
                :is="major.icon"
                class="w-7 h-7 text-primary group-hover:text-primary-foreground"
              />
            </div>
            <span class="text-[10px] font-black text-foreground/20 group-hover:text-primary-foreground/70">0{{ idx + 1 }}</span>
          </div>
          <div class="relative z-10">
            <h3 class="text-3xl font-heading font-black mb-6 uppercase text-foreground group-hover:text-primary-foreground">
              {{ major.title }}
            </h3>
            <p class="text-foreground/60 group-hover:text-primary-foreground/90 leading-relaxed text-sm">
              {{ major.description }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, markRaw } from 'vue'
import JanariSplitText from '@/modules/Cms/views/themes/janari/components/JanariSplitText.vue'
import { useTheme } from '@/modules/Cms/composables/useTheme'
import { useThemeMotion } from '@/modules/Cms/composables/useThemeMotion'
import { useThemeDataBindings } from '@/modules/Cms/composables/useThemeDataBindings'
import Monitor from 'lucide-vue-next/dist/esm/icons/monitor.js'
import Cpu from 'lucide-vue-next/dist/esm/icons/cpu.js'
import Layers from 'lucide-vue-next/dist/esm/icons/layers.js'
import Briefcase from 'lucide-vue-next/dist/esm/icons/briefcase.js'
import Heart from 'lucide-vue-next/dist/esm/icons/heart.js'
import BookOpen from 'lucide-vue-next/dist/esm/icons/book-open.js'

const { getSetting } = useTheme()
const { staggerChildren } = useThemeMotion()

const gridRef = ref<HTMLElement>()
const { data: dynamicItems } = useThemeDataBindings('majors', 'programs')

const iconMap: Record<string, any> = {
    Monitor: markRaw(Monitor), Cpu: markRaw(Cpu), Layers: markRaw(Layers), 
    Briefcase: markRaw(Briefcase), Heart: markRaw(Heart), BookOpen: markRaw(BookOpen)
};

const badgeText = computed(() => (getSetting('majors_badge') as string) || 'EXPLORE OPPORTUNITIES')
const titleText = computed(() => (getSetting('majors_title') as string) || 'KREATIFITAS TANPA BATAS')
const buttonText = computed(() => (getSetting('majors_button_text') as string) || 'VIEW ALL MAJORS')

const items = computed(() => dynamicItems.value.map((item: any) => ({ 
    icon: iconMap[item._raw?.meta?.program_icon] || BookOpen, 
    title: item.title, 
    description: item.excerpt || item.description 
})))

onMounted(() => {
    if (gridRef.value) staggerChildren(gridRef.value, '.motion-card', { distance: 60, stagger: 0.15 })
})
</script>
