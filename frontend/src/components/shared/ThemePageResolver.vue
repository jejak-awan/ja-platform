<template>
  <div class="theme-page-resolver-wrapper w-full h-full flex-1 flex flex-col">
    <component 
      :is="resolvedComponent" 
      v-if="resolvedComponent" 
      v-bind="$attrs" 
    />
    
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
import { useTheme } from '@/composables/useTheme'

const props = defineProps<{
  page: string
}>()

const { activeTheme } = useTheme()
const resolvedComponent = shallowRef<Component | null>(null)
const isNotFound = ref(false)
const isDestroyed = ref(false)
let currentResolveId = 0

// Component cache to prevent creating new defineAsyncComponent instances every time
const componentCache = new Map<string, Component>();

// Glob all theme views
// Glob all theme views using a relative path for better environment compatibility
const viewModules = import.meta.glob('../../modules/Cms/views/themes/**/*.vue') as Record<string, () => Promise<{ default: Component }>>

function resolveView() {
  if (isDestroyed.value) return

  const themeSlug = activeTheme.value?.slug || 'janari'
  const pageName = props.page
  
  // CONSTRUCTION: Try to find a matching module key
  // We search for a key that ends with "themes/{slug}/{page}.vue"
  const expectedSuffix = `themes/${themeSlug}/${pageName}.vue`.toLowerCase()
  const fallbackSuffix = `themes/janari/${pageName}.vue`.toLowerCase()

  let matchingKey = Object.keys(viewModules).find(key => {
    const k = key.toLowerCase();
    return k.endsWith(expectedSuffix) || k.includes(`/${themeSlug}/${pageName.toLowerCase()}.vue`);
  });
  
  if (!matchingKey && themeSlug !== 'janari') {
    matchingKey = Object.keys(viewModules).find(key => {
        const k = key.toLowerCase();
        return k.endsWith(fallbackSuffix) || k.includes(`/janari/${pageName.toLowerCase()}.vue`);
    });
  }

  const resolveId = ++currentResolveId
  isNotFound.value = false

  if (!matchingKey || !viewModules[matchingKey]) {
    if (resolveId !== currentResolveId || isDestroyed.value) return
    isNotFound.value = true
    resolvedComponent.value = null
    return
  }
  
  const loader = viewModules[matchingKey]
  
  // Create a new async component definition and cache it
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

  componentCache.set(`${themeSlug}:${pageName}:${matchingKey}`, asyncComponent)
  resolvedComponent.value = asyncComponent
}

const currentThemeSlug = computed(() => activeTheme.value?.slug || 'janari')

onBeforeUnmount(() => {
  isDestroyed.value = true
  currentResolveId++
})

watch([currentThemeSlug, () => props.page], resolveView, { immediate: true })
</script>

