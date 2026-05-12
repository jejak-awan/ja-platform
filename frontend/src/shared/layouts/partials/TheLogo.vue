<template>
  <div 
    class="flex items-center gap-2 select-none group"
    :class="[minimized ? 'flex-col justify-center' : 'flex-row']"
  >
    <!-- Logo Container -->
    <div 
      class="relative flex items-center justify-center overflow-hidden"
      :class="[ minimized ? 'w-9 h-9' : 'w-auto h-9 max-w-[120px]' ]"
    >
      <!-- Option A: User uploaded Logo Image -->
      <img 
        v-if="appLogo" 
        :src="appLogo"
        :alt="displayTitle"
        class="w-full h-full object-contain rounded-md"
      >

      <!-- Option B: Stylized Box Fallback -->
      <div 
        v-else
        class="w-full h-full flex flex-col items-center justify-center border-2 border-primary rounded-lg shadow-sm"
      >
        <span class="text-[13px] leading-none font-[900] text-primary tracking-tighter">JA</span>
        <span class="text-[7.5px] leading-tight font-bold text-primary/80 tracking-widest -mt-0.5">CORE</span>
      </div>
    </div>

    <!-- Site Branding (Hidden when minimized) -->
    <div
      v-if="!minimized"
      class="flex flex-col ml-1"
    >
      <span class="text-sm font-black tracking-tight text-foreground leading-none">{{ displayTitle }}</span>
      
      <!-- License Badge -->
      <div class="flex items-center mt-0.5">
        <span 
          class="text-[9px] px-1.5 py-0.5 rounded-full font-bold uppercase tracking-wider leading-none"
          :class="licenseBadgeClasses"
        >
          {{ displaySubtitle }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useCoreStore } from '@/modules/Core/stores/core';

const props = withDefaults(defineProps<{
  minimized?: boolean;
  title?: string | null;
  subtitle?: string | null;
}>(), {
  minimized: false,
  title: null,
  subtitle: null
});

const coreStore = useCoreStore();
const displayTitle = computed(() => props.title || coreStore.appIdentity?.app_name || 'Janari App');

const displaySubtitle = computed(() => {
  if (props.subtitle) return props.subtitle;
  const tier = coreStore.appIdentity?.app_license_tier || 'basic';
  
  // Pretty names for tiers
  const tierNames: Record<string, string> = {
    'basic': 'Basic',
    'pro': 'Pro',
    'pro_plus': 'Pro+',
    'white_label': 'Enterprise'
  };
  
  return tierNames[tier] || tier;
});

const licenseBadgeClasses = computed(() => {
  const tier = coreStore.appIdentity?.app_license_tier || 'basic';
  
  switch(tier) {
    case 'pro':
      return 'bg-blue-500/10 text-blue-600 border border-blue-500/20';
    case 'pro_plus':
      return 'bg-indigo-500/10 text-indigo-600 border border-indigo-500/20';
    case 'white_label':
      return 'bg-purple-500/10 text-purple-600 border border-purple-500/20';
    default:
      return 'bg-muted text-muted-foreground border border-border';
  }
});

const appLogo = computed(() => coreStore.appIdentity?.app_logo || '');
</script>

<style scoped>
.group:hover .bg-primary {
  filter: brightness(1.1);
}
</style>
