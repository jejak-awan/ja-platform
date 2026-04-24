<template>
  <div 
    v-if="state.isVisible" 
    class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-slate-950/20 backdrop-blur-md animate-in fade-in duration-500"
  >
    <!-- Overlay backdrop clicking should not close critical errors -->
    <div
      class="fixed inset-0 -z-10"
      @click="state.code !== 401 && state.code !== 419 && hideError()"
    />

    <div class="max-w-md w-full bg-card/80 backdrop-blur-xl border border-white/20 rounded-[2.5rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.3)] overflow-hidden animate-in zoom-in-95 slide-in-from-bottom-8 duration-500 ease-out">
      <div class="p-10 pb-8 flex flex-col items-center text-center">
        <!-- Icon Box with glow -->
        <div 
          class="h-24 w-24 rounded-[2rem] flex items-center justify-center mb-8 border relative ring-4 ring-white/10"
          :class="config.iconBgClass"
        >
          <div class="absolute inset-0 blur-2xl opacity-20 bg-current rounded-full" />
          <component 
            :is="config.icon" 
            class="h-12 w-12 relative z-10"
            :class="config.iconClass"
          />
        </div>

        <!-- Error Code -->
        <h1 class="text-6xl font-bold tracking-tighter mb-4 text-foreground/90 font-sans">
          {{ state.code }}
        </h1>

        <!-- Title -->
        <h2 class="text-xl font-bold mb-4 text-foreground">
          {{ title }}
        </h2>

        <!-- Message -->
        <p class="text-sm text-muted-foreground leading-relaxed px-4 opacity-80">
          {{ message }}
        </p>

        <!-- Actions -->
        <div class="w-full flex flex-col gap-3 mt-10">
          <button
            v-if="state.code === 401 || state.code === 419"
            class="w-full inline-flex items-center justify-center px-6 py-4 border border-transparent text-sm font-semibold rounded-2xl text-warning-foreground bg-warning hover:bg-warning/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-warning transition-colors active:scale-[0.98]"
            @click="handleLogin"
          >
            <LogIn class="w-5 h-5 mr-2" />
            {{ t('features.errors.419.login') }}
          </button>
                    
          <button
            class="w-full inline-flex items-center justify-center px-6 py-4 border border-border text-sm font-semibold rounded-2xl text-foreground bg-muted/50 hover:bg-muted focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-[colors,transform] active:scale-95"
            @click="handleRefresh"
          >
            <RefreshCw class="w-5 h-5 mr-2 text-muted-foreground" />
            {{ t('features.errors.419.refresh') }}
          </button>
        </div>
      </div>

      <!-- Footer Details -->
      <div class="px-10 py-6 border-t border-white/10 bg-black/5">
        <div class="flex items-center justify-between text-[10px] text-muted-foreground/60 font-bold uppercase tracking-[0.2em]">
          <span>Error Code: {{ state.code }}</span>
          <span class="font-mono">{{ state.traceId }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, type Component } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/modules/Core/stores/auth';
import { useI18n } from 'vue-i18n';
import { SECURITY_ROUTES } from '@/config/security';
import Fingerprint from 'lucide-vue-next/dist/esm/icons/fingerprint-pattern.js';
import FileQuestion from 'lucide-vue-next/dist/esm/icons/file-question-mark.js';
import ServerCrash from 'lucide-vue-next/dist/esm/icons/server-crash.js';
import Lock from 'lucide-vue-next/dist/esm/icons/lock.js';
import LogIn from 'lucide-vue-next/dist/esm/icons/log-in.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import AlertTriangle from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import { onMounted, reactive } from 'vue';

// Use a local reactive state that mirrors the system error store
// This breaks the static import cycle while maintaining UI responsiveness
const state = reactive({
    isVisible: false,
    code: null,
    title: '',
    message: '',
    description: '',
    reason: null,
    redirect: null,
    traceId: '',
});

let hideErrorFn = () => {};

onMounted(async () => {
    const { useSystemError } = await import('@/composables/useSystemError');
    const { state: systemState, hideError } = useSystemError();
    
    // Sync local state with global store
    hideErrorFn = hideError;
    Object.assign(state, systemState);
});

const hideError = () => hideErrorFn();
const router = useRouter();
const authStore = useAuthStore();
const { t } = useI18n();

const title = computed(() => {
    if (state.title) return state.title;
    return t(`features.errors.${state.code}.title`, 'Something went wrong');
});

const message = computed(() => {
    if (state.message) return state.message;
    
    if (state.code === 419 || state.code === 401) {
        if (state.reason === 'concurrent') return t('features.errors.419.concurrent');
        if (state.reason === 'timeout') return t('features.errors.419.timeout');
    }
    
    return t(`features.errors.${state.code}.message`, 'An unexpected error occurred.');
});

interface ErrorConfig {
    icon: Component;
    iconBgClass: string;
    iconClass: string;
}

const config = computed<ErrorConfig>(() => {
    const code = Number(state.code);
    switch (code) {
        case 401:
        case 419:
            return {
                icon: Fingerprint,
                iconBgClass: 'bg-orange-500/10 border-orange-500/20',
                iconClass: 'text-orange-600 dark:text-orange-500'
            };
        case 403:
            return {
                icon: Lock,
                iconBgClass: 'bg-red-500/10 border-red-500/20',
                iconClass: 'text-red-600 dark:text-red-500'
            };
        case 404:
            return {
                icon: FileQuestion,
                iconBgClass: 'bg-blue-500/10 border-blue-500/20',
                iconClass: 'text-blue-600 dark:text-blue-500'
            };
        case 429:
            return {
                icon: AlertTriangle,
                iconBgClass: 'bg-yellow-500/10 border-yellow-500/20',
                iconClass: 'text-yellow-600 dark:text-yellow-500'
            };
        case 500:
        default:
            return {
                icon: ServerCrash,
                iconBgClass: 'bg-destructive/10 border-destructive/20',
                iconClass: 'text-destructive'
            };
    }
});

const handleLogin = () => {
    const redirect = (state.redirect || router.currentRoute.value.fullPath) as string;
    const target = `${SECURITY_ROUTES.login}?redirect=${encodeURIComponent(redirect !== '/' ? redirect : SECURITY_ROUTES.dashboardBase)}`;
    
    hideError();
    authStore.logout(); // Local clear
    
    // ATOMIC RECOVERY: Hard reload to the login page
    // This wipes the JS heap, kills all intervals, and stops all "request bombing"
    window.location.href = target;
};

const handleRefresh = () => {
    window.location.reload();
};
</script>
