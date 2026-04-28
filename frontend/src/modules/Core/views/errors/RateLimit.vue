<template>
  <ErrorLayout>
    <template #icon>
      <div class="h-20 w-20 rounded-2xl bg-yellow-500/10 border border-yellow-500/20 flex items-center justify-center">
        <AlertTriangle class="h-10 w-10 text-yellow-600 dark:text-yellow-500" />
      </div>
    </template>

    <template #title>
      429
    </template>

    <template #message>
      {{ t('features.errors.429.title') }}
    </template>

    <template #description>
      {{ t('features.errors.429.message') }}
    </template>

    <template #actions>
      <button
        class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-border text-sm font-medium rounded-2xl text-foreground bg-muted hover:bg-muted/80 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-yellow-500 transition-[background-color,transform] active:scale-95"
        @click="goBack"
      >
        <ArrowLeft class="w-4 h-4 mr-2 text-muted-foreground" />
        {{ t('features.errors.404.back') }}
      </button>
            
      <router-link
        to="/"
        class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-2xl text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-yellow-500 shadow-sm transition-[background-color,transform] active:scale-95"
      >
        <Home class="w-4 h-4 mr-2" />
        {{ t('features.errors.404.home') }}
      </router-link>
    </template>

    <template #footer>
      <div class="flex items-center justify-center gap-3">
        <span>Error Code: 429</span>
        <span class="w-1 h-1 rounded-full bg-border" />
        <span class="font-mono opacity-60">{{ traceId }}</span>
      </div>
    </template>
  </ErrorLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/modules/Core/stores/auth';
import { SECURITY_ROUTES } from '@/config/security';
import ErrorLayout from '@/layouts/core/ErrorLayout.vue';
import AlertTriangle from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import Home from 'lucide-vue-next/dist/esm/icons/house.js';
import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { t } = useI18n();
const traceId = ref(`TRC-${Date.now().toString().slice(-6)}-${Math.random().toString(36).substring(7).toUpperCase()}`);

const goBack = () => {
  if (!authStore.isAuthenticated) {
    router.push({
      path: SECURITY_ROUTES.login,
      query: { 
        redirect: route.fullPath !== '/429' ? route.fullPath : undefined,
        timeout: '1'
      }
    });
    return;
  }

  if (window.history.length > 1) {
    router.go(-1);
  } else {
    router.push('/');
  }
};
</script>
