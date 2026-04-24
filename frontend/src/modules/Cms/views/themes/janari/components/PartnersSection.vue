<template>
  <section
    v-if="schoolLevel === 'smk'"
    class="py-24 bg-background border-b border-border overflow-hidden"
  >
    <div class="container mx-auto px-6 text-center">
      <h2 class="text-4xl font-heading font-black text-foreground uppercase tracking-tighter mb-20">
        {{ titleText }}
      </h2>
      <div
        ref="marqueeRef"
        class="flex items-center gap-24 whitespace-nowrap opacity-20 hover:opacity-100 transition-opacity duration-1000"
      >
        <!-- Duplicate items natively for seamless marquee loop -->
        <div
          v-for="(partner, idx) in [...items, ...items]"
          :key="partner.name + idx"
          class="grayscale hover:grayscale-0 transition-all cursor-pointer"
        >
          <img
            v-if="partner.image"
            :src="partner.image"
            :alt="partner.name"
            class="h-12 object-contain"
          >
          <span
            v-else
            class="text-2xl font-black text-foreground/40"
          >{{ partner.name }}</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useTheme } from '@/composables/useTheme'
import { useGsapAnimations } from '@/composables/useGsapAnimations'
import { useAdvancedBindings } from '@/modules/Cms/composables/useAdvancedBindings'

const { getSetting } = useTheme()
const { marquee } = useGsapAnimations()

const marqueeRef = ref<HTMLElement>()
const { data: dynamicItems } = useAdvancedBindings('partners', 'partners')

const schoolLevel = computed(() => (getSetting('school_level') as string) || 'smk')
const titleText = computed(() => (getSetting('partners_title') as string) || 'LINK & MATCH INDUSTRI')
const marqueeSpeed = computed(() => parseInt(String(getSetting('partners_marquee_speed', 25)), 10))

const items = computed(() => dynamicItems.value.map((item: any) => ({ 
    name: item.title, 
    image: item._raw?.featured_image || item._raw?.thumbnail 
})))

onMounted(() => {
    if (marqueeRef.value) marquee(marqueeRef.value, { speed: marqueeSpeed.value })
})
</script>
