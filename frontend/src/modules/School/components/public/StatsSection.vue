<template>
  <section class="py-40 bg-muted/30 border-y border-border">
    <div class="container mx-auto px-6 text-center">
      <h2
        v-if="sectionTitle"
        class="text-2xl font-black mb-20 tracking-widest text-foreground/40"
      >
        {{ sectionTitle }}
      </h2>
      <div
        ref="gridRef"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-20"
      >
        <div
          v-for="stat in items"
          :key="stat.label"
          class="motion-stat group flex flex-col items-center"
        >
          <div
            class="motion-stat-value text-5xl md:text-9xl font-heading font-black mb-6 tracking-tighter"
            :data-value="stat.rawValue"
          >
            {{ stat.value }}
          </div>
          <div class="w-12 h-1 bg-primary mb-6 group-hover:w-24 transition-all" />
          <div class="text-foreground/50 font-black tracking-[0.5em] uppercase text-[10px]">
            {{ stat.label }}
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useTheme } from '@/shared/composables/useTheme'
import { useThemeMotion } from '@/shared/composables/useThemeMotion'
import { useThemeDataBindings } from '@/modules/Cms/composables/useThemeDataBindings'

const { getSetting } = useTheme()
const { staggerChildren, counterUp } = useThemeMotion()

const gridRef = ref<HTMLElement>()
const { data: dynamicItems } = useThemeDataBindings('stats', 'counters')

const sectionTitle = computed(() => (getSetting('stats_title') as string) || '')
const items = computed(() => dynamicItems.value.map((item: any) => ({ 
    label: item.title, 
    rawValue: parseInt(item._raw?.meta?.stat_raw || '0'), 
    value: item._raw?.meta?.stat_value || '0' 
})))

onMounted(() => {
    if (gridRef.value) {
        staggerChildren(gridRef.value, '.motion-stat', { distance: 40, stagger: 0.12 })
        gridRef.value.querySelectorAll('.motion-stat-value').forEach((el: any) => {
            const raw = parseInt(el.getAttribute('data-value') || '0', 10)
            if (raw > 0) counterUp(el, raw, { duration: 2.5 })
        })
    }
})
</script>
