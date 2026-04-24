<template>
  <div class="min-h-screen flex items-center justify-center bg-linear-to-br from-background via-muted/20 to-background px-4 py-2 sm:px-6 lg:px-8">
    <div class="w-full max-w-lg bg-card rounded-3xl shadow-2xl shadow-primary/5 overflow-hidden border border-border/40 min-h-0 animate-fade-up">
      <!-- Forgot Password Form -->
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
            Forgot password?
          </h2>
          <p class="mt-0.5 text-muted-foreground text-[10px]">
            {{ t('features.auth.forgotPassword.subtitle') || 'Enter your email and we\'ll send you a reset link' }}
          </p>
        </div>

        <form
          class="space-y-2"
          @submit.prevent="handleSubmit"
        >
          <div class="space-y-1">
            <Label
              for="email"
              class="text-[10px] uppercase tracking-wider font-bold ml-1 text-muted-foreground/80"
            >{{ t('common.labels.email') }}</Label>
            <Input
              id="email"
              v-model="form.email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="auth-input h-9 text-sm"
              :class="errors.email ? 'border-destructive/50 ring-destructive/20 focus:border-destructive' : ''"
              :placeholder="t('features.auth.login.emailPlaceholder')"
            />
            <p
              v-if="errors.email"
              class="text-[10px] text-destructive font-medium ml-1"
            >
              {{ errors.email[0] }}
            </p>
          </div>

          <!-- Captcha -->
          <div class="rounded-xl overflow-hidden border border-border/40 bg-muted/5">
            <CaptchaWrapper 
              ref="captchaRef"
              action="forgot-password"
              @verified="onCaptchaVerified"
            />
          </div>

          <div
            v-if="message"
            class="rounded-xl p-2 text-[10px] border animate-fade"
            :class="messageType === 'error' ? 'bg-destructive/10 text-destructive border-destructive/20' : 'bg-success/10 text-success border-success/20'"
          >
            {{ message }}
          </div>

          <Button
            type="submit"
            class="w-full h-9 auth-button-gradient mt-1"
            :disabled="loading || !isValid || (captchaEnabled && !captchaVerified)"
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
              v-else
              class="text-xs"
            >{{ t('features.auth.forgotPassword.submit') }}</span>
          </Button>

          <div class="text-center text-[10px] text-muted-foreground mt-3">
            <router-link
              :to="{ name: 'login' }"
              class="inline-flex items-center font-bold text-primary hover:text-primary/80 transition-all group"
            >
              <ArrowLeft class="mr-2 h-3 w-3 transition-transform group-hover:-translate-x-1" />
              {{ t('features.auth.forgotPassword.backToLogin') }}
            </router-link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/modules/Core/stores/auth';
import { useCmsStore } from '@/modules/Cms/stores/cms';
import { useFormValidation } from '@/composables/useFormValidation';
import { forgotPasswordSchema } from '@/schemas/auth';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js';
import LayoutTemplate from 'lucide-vue-next/dist/esm/icons/layout-template.js';
import CaptchaWrapper, { type CaptchaPayload } from '@/modules/Core/components/captcha/CaptchaWrapper.vue';


// Shadcn Components
import {
    Button,
    Input,
    Label
} from '@/components/ui';

const { t } = useI18n();
const cmsStore = useCmsStore();
const authStore = useAuthStore();
const { errors, validateWithZod, clearErrors } = useFormValidation(forgotPasswordSchema);

interface CaptchaWrapperInstance {
    enabled: boolean;
    method: string;
}

const captchaRef = ref<CaptchaWrapperInstance | null>(null);
const captchaVerified = ref(false);
const captchaToken = ref('');
const captchaAnswer = ref('');
const captchaEnabled = computed(() => captchaRef.value?.enabled || false);

const onCaptchaVerified = (payload: CaptchaPayload) => {
    captchaToken.value = payload.token;
    captchaAnswer.value = payload.answer;
    captchaVerified.value = true;
};

const form = reactive({
    email: '',
});

const isValid = computed(() => {
    return !!form.email;
});

const message = ref('');
const messageType = ref('');
const loading = ref(false);

const handleSubmit = async () => {
    // Client-side validation first
    if (!validateWithZod(form)) {
        return;
    }

    loading.value = true;
    clearErrors();
    message.value = '';

    const payload: { email: string; captcha_token?: string; captcha_answer?: string } = {
        email: form.email,
    };

    if (captchaEnabled.value) {
        payload.captcha_token = captchaToken.value;
        payload.captcha_answer = captchaAnswer.value;
    }

    const result = await authStore.forgotPassword(payload);

    if (result.success) {
        message.value = result.message || '';
        messageType.value = 'success';
    } else {
        message.value = result.message || '';
        messageType.value = 'error';

        // Reset captcha on failure
        if (captchaRef.value?.method === 'slider' || captchaRef.value?.method === 'math' || captchaRef.value?.method === 'image') {
            captchaVerified.value = false;
            captchaToken.value = '';
            captchaAnswer.value = '';
        }
    }

    loading.value = false;
};
</script>

