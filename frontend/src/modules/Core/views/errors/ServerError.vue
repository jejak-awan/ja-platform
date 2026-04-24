<template>
  <ErrorLayout>
    <template #icon>
      <div class="h-20 w-20 rounded-2xl bg-destructive/10 border border-destructive/20 flex items-center justify-center">
        <ServerCrash class="h-10 w-10 text-destructive" />
      </div>
    </template>

    <template #title>
      500
    </template>

    <template #message>
      {{ t('features.errors.500.title') }}
    </template>

    <template #description>
      {{ t('features.errors.500.message') }}
    </template>

    <template #actions>
      <button
        :disabled="retrying"
        class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-2xl text-white bg-destructive hover:bg-destructive/90 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-destructive shadow-lg shadow-destructive/20 transition-colors disabled:opacity-70 disabled:cursor-not-allowed active:scale-95"
        @click="retry"
      >
        <RefreshCw
          class="w-4 h-4 mr-2"
          :class="{ 'animate-spin': retrying }"
        />
        {{ retrying ? t('features.errors.500.retrying') : t('features.errors.500.retry') }}
      </button>
            
      <router-link
        to="/"
        class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-border text-sm font-medium rounded-2xl text-foreground bg-muted hover:bg-muted/80 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-destructive transition-[background-color,transform] active:scale-95"
      >
        <Home class="w-4 h-4 mr-2 text-muted-foreground" />
        {{ t('features.errors.404.home') }}
      </router-link>
    </template>

    <template #footer>
      <div class="flex items-center justify-center gap-3">
        <span>Error Code: 500</span>
        <span class="w-1 h-1 rounded-full bg-border" />
        <span class="font-mono opacity-60">{{ errorId }}</span>
      </div>
    </template>
  </ErrorLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ErrorLayout from '@/layouts/core/ErrorLayout.vue';
import ServerCrash from 'lucide-vue-next/dist/esm/icons/server-crash.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Home from 'lucide-vue-next/dist/esm/icons/house.js';

const { t } = useI18n();
const retrying = ref(false);
const errorId = ref(`ERR-${Date.now().toString().slice(-6)}-${Math.random().toString(36).substring(7).toUpperCase()}`);

const retry = async () => {
    retrying.value = true;
    await new Promise(resolve => setTimeout(resolve, 1000));
    window.location.reload();
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 4px;
}
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}
.slide-fade-leave-active {
  transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-5px);
  opacity: 0;
}
</style>

