<template>
  <Transition
    enter-active-class="transition duration-500 ease-out"
    enter-from-class="opacity-0 scale-95"
    enter-to-class="opacity-100 scale-100"
    leave-active-class="transition duration-300 ease-in"
    leave-from-class="opacity-100 scale-100"
    leave-to-class="opacity-0 scale-95"
  >
    <div
      v-if="securityStore.isShieldVisible"
      class="fixed inset-0 z-[9999] flex items-center justify-center bg-background/80 backdrop-blur-md"
    >
      <div class="relative w-full max-w-md p-8 overflow-hidden glass-morphism rounded-2xl shadow-2xl animate-fade-up">
        <!-- Floating background gradients for premium look -->
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-primary/20 rounded-full blur-3xl animate-pulse" />
        <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-accent/20 rounded-full blur-3xl animate-pulse" />

        <div class="relative z-10 flex flex-col items-center text-center">
          <div class="p-4 mb-6 rounded-full bg-primary/10 ring-1 ring-primary/20">
            <ShieldCheck v-if="securityStore.shieldProgress >= 100" class="w-12 h-12 text-success animate-bounce" />
            <Shield v-else class="w-12 h-12 text-primary animate-pulse" />
          </div>

          <h2 class="text-2xl font-bold tracking-tight mb-2 text-foreground">
            {{ $t('features.security.shield.challenge.title') }}
          </h2>
          <p class="text-muted-foreground mb-8 text-sm">
            {{ securityStore.shieldStatus }}
          </p>

          <!-- Progress Bar Container -->
          <div class="w-full h-1.5 bg-muted rounded-full overflow-hidden mb-4">
            <div
              class="h-full bg-primary transition-all duration-500 ease-out"
              :style="{ width: `${securityStore.shieldProgress}%` }"
            />
          </div>

          <div class="flex items-center gap-2 text-xs text-muted-foreground font-mono uppercase tracking-widest">
            <span class="inline-block w-2 h-2 rounded-full bg-primary animate-ping" />
            {{ securityStore.shieldProgress }}% {{ $t('features.security.shield.challenge.status.processing') }}
          </div>
        </div>

        <!-- Decorative corner borders -->
        <div class="absolute top-0 left-0 w-8 h-8 border-t-2 border-l-2 border-primary/30 rounded-tl-2xl" />
        <div class="absolute bottom-0 right-0 w-8 h-8 border-b-2 border-r-2 border-primary/30 rounded-br-2xl" />
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { useSecurityStore } from '@/modules/Core/stores/security';
import Shield from 'lucide-vue-next/dist/esm/icons/shield.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';

const securityStore = useSecurityStore();
</script>

<style scoped>
.glass-morphism {
  background: rgba(var(--background-rgb), 0.7);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.dark .glass-morphism {
  background: rgba(0, 0, 0, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.05);
}
</style>
