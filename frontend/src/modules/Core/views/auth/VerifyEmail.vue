<template>
  <div class="min-h-screen flex items-center justify-center bg-linear-to-br from-background via-muted/20 to-background px-4 py-2 sm:px-6 lg:px-8">
    <div class="w-full max-w-lg bg-card rounded-3xl shadow-2xl shadow-primary/5 overflow-hidden border border-border/40 min-h-0 animate-fade-up">
      <!-- Verify Form -->
      <div class="w-full p-6 sm:p-8 flex flex-col justify-center animate-fade">
        <!-- Internal Branding -->
        <div class="flex items-center gap-2 mb-3 group justify-center">
          <div class="bg-primary rounded-lg p-1.5 shadow-lg shadow-primary/20 transition-transform group-hover:scale-110">
            <img
              v-if="cmsStore.siteSettings?.site_logo"
              :src="cmsStore.siteSettings.site_logo"
              :alt="cmsStore.siteSettings.site_name"
              class="h-5 w-auto object-contain invert grayscale brightness-200"
            >
            <LayoutTemplate
              v-else
              class="h-5 w-5 text-primary-foreground"
            />
          </div>
          <span class="text-lg font-black tracking-tight text-foreground">{{ cmsStore.siteSettings?.site_name || 'Janari CMS' }}</span>
        </div>

        <div class="mb-3 text-center md:text-left">
          <h2 class="text-xl font-black tracking-tight text-foreground">
            {{ t('features.auth.verifyEmail.title') }}
          </h2>
          <p class="mt-0.5 text-muted-foreground text-[10px]">
            {{ t('features.auth.verifyEmail.description') }}
          </p>
        </div>

        <div
          v-if="message"
          class="rounded-xl p-2 text-[10px] mb-3 animate-fade"
          :class="messageType === 'error' ? 'bg-destructive/10 text-destructive border border-destructive/20' : 'bg-success/10 text-success border border-success/20'"
        >
          {{ message }}
        </div>

        <div class="space-y-2">
          <div class="text-center md:text-left">
            <div class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-primary/5 text-primary mb-1">
              <Mail class="h-5 w-5" />
            </div>
            <p class="text-muted-foreground text-[10px] leading-relaxed">
              {{ t('features.auth.verifyEmail.resendPrompt') }}
            </p>
          </div>

          <div class="space-y-1.5">
            <Button
              :disabled="loading || resendCooldown > 0"
              class="w-full h-9 auth-button-gradient py-2 px-4 shadow-lg shadow-primary/10"
              @click="handleResend"
            >
              <Loader2
                v-if="loading"
                class="mr-2 h-3 w-3 animate-spin"
              />
              <span
                v-if="loading"
                class="text-xs"
              >{{ t('features.auth.verifyEmail.sending') }}</span>
              <span
                v-else-if="resendCooldown > 0"
                class="text-xs"
              >{{ t('features.auth.verifyEmail.cooldown', { seconds: resendCooldown }) }}</span>
              <span
                v-else
                class="text-xs"
              >{{ t('features.auth.verifyEmail.resendButton') }}</span>
            </Button>

            <div class="text-center">
              <router-link
                :to="{ name: 'login' }"
                class="inline-flex items-center text-sm font-bold text-primary hover:text-primary/80 transition-colors group"
              >
                <ArrowLeft class="mr-2 h-4 w-4 transition-transform group-hover:-translate-x-1" />
                {{ t('features.auth.forgotPassword.backToLogin') }}
              </router-link>
            </div>
          </div>
        </div>

        <div
          v-if="verified"
          class="mt-3 bg-success/10 border border-success/20 rounded-xl p-3 text-center animate-fade-up"
        >
          <CheckCircle class="mx-auto h-8 w-8 text-success mb-2" />
          <p class="text-success font-black text-sm">
            {{ t('features.auth.verifyEmail.success') }}
          </p>
          <p class="text-[10px] text-success/70 mt-0.5">
            {{ t('features.auth.verifyEmail.redirecting') }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useCmsStore } from '@/modules/Cms/stores/cms';
import Mail from 'lucide-vue-next/dist/esm/icons/mail.js';
import CheckCircle from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import LayoutTemplate from 'lucide-vue-next/dist/esm/icons/layout-template.js';
import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js';


// Shadcn Components
import { Button } from '@/components/ui';

const { t } = useI18n();
const cmsStore = useCmsStore();
const router = useRouter();
const route = useRoute();

const message = ref('');
const messageType = ref('');
const loading = ref(false);
const verified = ref(false);
const resendCooldown = ref(0);
let cooldownInterval: ReturnType<typeof setInterval> | null = null;

onMounted(async () => {
    // Check if there's a verification token in the URL
    if (route.query.token && route.query.email) {
        await handleVerify(route.query.token as string, route.query.email as string);
    }
});

onUnmounted(() => {
    if (cooldownInterval) {
        clearInterval(cooldownInterval);
    }
});

const handleVerify = async (token: string, email: string) => {
    loading.value = true;
    message.value = '';
    messageType.value = '';

    try {
        const response = await api.post('/verify-email', {
            token,
            email,
        });
        const payload = response.data as { success?: boolean; message?: string };

        if (payload.success !== false) {
            verified.value = true;
            message.value = t('features.auth.verifyEmail.success');
            messageType.value = 'success';
            
            setTimeout(() => {
                router.push({ name: 'login' });
            }, 2000);
        } else {
            message.value = payload.message || t('features.auth.verifyEmail.failed');
            messageType.value = 'error';
        }
    } catch (error: unknown) {
        if (typeof error === 'object' && error !== null && 'response' in error) {
            const err = error as { response?: { data?: { message?: string } } };
            message.value = err.response?.data?.message || t('features.auth.verifyEmail.failed');
        } else {
            message.value = t('features.auth.verifyEmail.failed');
        }
        messageType.value = 'error';
    } finally {
        loading.value = false;
    }
};

const handleResend = async () => {
    loading.value = true;
    message.value = '';
    messageType.value = '';

    try {
        const email = route.query.email || '';
        const response = await api.post('/resend-verification', {
            email,
        });
        const payload = response.data as { success?: boolean; message?: string };

        if (payload.success !== false) {
            message.value = t('features.auth.verifyEmail.resendSuccess');
            messageType.value = 'success';
            
            // Start cooldown timer (60 seconds)
            resendCooldown.value = 60;
            cooldownInterval = setInterval(() => {
                resendCooldown.value--;
                if (resendCooldown.value <= 0) {
                    if (cooldownInterval) clearInterval(cooldownInterval);
                    cooldownInterval = null;
                }
            }, 1000);
        } else {
            message.value = payload.message || t('features.auth.verifyEmail.failed');
            messageType.value = 'error';
        }
    } catch (error: unknown) {
        if (typeof error === 'object' && error !== null && 'response' in error) {
            const err = error as { response?: { data?: { message?: string } } };
            message.value = err.response?.data?.message || t('features.auth.verifyEmail.failed');
        } else {
            message.value = t('features.auth.verifyEmail.failed');
        }
        messageType.value = 'error';
    } finally {
        loading.value = false;
    }
};
</script>
