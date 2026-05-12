<template>
  <div class="theme-page-resolver-wrapper w-full h-full flex-1 flex flex-col">
    <component 
      :is="resolvedComponent" 
      v-if="resolvedComponent" 
      v-bind="$attrs" 
    />

    <div
      v-else-if="isLoadingTheme"
      class="min-h-[30vh] flex items-center justify-center p-8"
      aria-live="polite"
      aria-busy="true"
    >
      <div class="flex items-center gap-3 text-muted-foreground">
        <span class="inline-block w-4 h-4 rounded-full border-2 border-current border-t-transparent animate-spin" />
        <span class="text-sm font-medium">Memuat halaman...</span>
      </div>
    </div>
    
    <!-- Resilience: Fallback UI for failed/missing components -->
    <div
      v-else-if="isNotFound"
      class="min-h-[40vh] flex flex-col items-center justify-center p-12 text-center border-2 border-dashed border-border/50 m-4 rounded-[2rem] bg-muted/10"
    >
      <div class="w-16 h-16 rounded-full bg-destructive/10 flex items-center justify-center mb-6">
        <span class="text-3xl font-black text-destructive">!</span>
      </div>
      <h3 class="text-xl font-bold mb-2">
        Halaman Tidak Stabil
      </h3>
      <p class="text-muted-foreground max-w-md">
        Komponen tema gagal dimuat dengan sempurna. Silakan muat ulang halaman atau hubungi administrator.
      </p>
      <button
        class="mt-6 px-6 py-2 bg-primary text-primary-foreground rounded-full text-sm font-bold hover:scale-105 transition-transform"
        @click="resolveView"
      >
        Coba Lagi
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { shallowRef, watch, ref, computed, defineAsyncComponent, onBeforeUnmount, type Component } from 'vue'
import { useTheme } from '@/shared/composables/useTheme'
import { buildThemeViewResolveCandidates, findThemeViewKey } from '@/modules/Cms/utils/themeViewResolver'

// Shared across all resolver instances (header/footer/page content) to avoid
// rebuilding the module map and cache on every route navigation.
const viewModules = import.meta.glob([
    '../../modules/Cms/views/themes/**/*.vue',
    '../../modules/School/views/public/**/*.vue'
]) as Record<string, () => Promise<{ default: Component }>>
const componentCache = new Map<string, Component>()

const props = defineProps<{
  page: string
}>()

const { activeTheme, loading } = useTheme()
const resolvedComponent = shallowRef<Component | null>(null)
const isNotFound = ref(false)
const isDestroyed = ref(false)
let currentResolveId = 0
const isLoadingTheme = computed(() => loading.value || !activeTheme.value)

function resolveView() {
  if (isDestroyed.value) return

  // Jangan tampilkan fallback saat data tema belum siap (initial load),
  // agar landing publik tidak berkedip error palsu.
  if (loading.value || !activeTheme.value) {
    isNotFound.value = false
    resolvedComponent.value = null
    return
  }

  const pageName = props.page
  const themeSlugs = buildThemeViewResolveCandidates(activeTheme.value)
  const matchingKey = findThemeViewKey(viewModules, themeSlugs, pageName)

  const resolveId = ++currentResolveId
  isNotFound.value = false

  if (!matchingKey || !viewModules[matchingKey]) {
    if (resolveId !== currentResolveId || isDestroyed.value) return
    isNotFound.value = true
    resolvedComponent.value = null
    return
  }
  
  const loader = viewModules[matchingKey]

  const cacheKey = `${themeSlugs.join('|')}|${pageName}|${matchingKey}`
  const cached = componentCache.get(cacheKey)
  if (cached) {
    resolvedComponent.value = cached
    return
  }

  const asyncComponent = defineAsyncComponent({
    loader: () => {
      const id = resolveId
      return loader!().then(mod => {
        if (id !== currentResolveId || isDestroyed.value) {
          return { default: { render: () => null } } as any
        }
        return mod
      }).catch(_err => {
        if (id === currentResolveId && !isDestroyed.value) {
          isNotFound.value = true
        }
        return { default: { render: () => null } } as any
      })
    },
    timeout: 15000,
    onError(_err, _retry, fail) {
      if (resolveId === currentResolveId && !isDestroyed.value) {
        isNotFound.value = true
      }
      fail()
    }
  })

  componentCache.set(cacheKey, asyncComponent)
  resolvedComponent.value = asyncComponent
}

const themeResolveKey = computed(() => {
  const t = activeTheme.value
  const slug = t && typeof t.slug === 'string' ? t.slug : ''
  const parent = t && typeof t.parent_theme === 'string' ? t.parent_theme : ''
  return `${slug}\n${parent}`
})

onBeforeUnmount(() => {
  isDestroyed.value = true
  currentResolveId++
})

watch([themeResolveKey, () => props.page], resolveView, { immediate: true })
watch(() => loading.value, resolveView)
</script>

