<template>
  <div class="min-h-screen flex items-center justify-center bg-linear-to-br from-background via-muted/20 to-background px-4 py-2 sm:px-6 lg:px-8">
    <div class="w-full max-w-lg bg-card rounded-3xl shadow-2xl shadow-primary/5 overflow-hidden border border-border/40 min-h-0 animate-fade-up">
      <!-- Reset Password Form -->
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
            Reset password
          </h2>
          <p class="mt-0.5 text-muted-foreground text-[10px]">
            {{ t('features.auth.resetPassword.subtitle') || 'Enter your new password below' }}
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
              required
              class="auth-input h-9 text-sm"
              :class="errors.email ? 'border-destructive/50 ring-destructive/20 focus:border-destructive' : ''"
              :placeholder="t('features.auth.login.emailPlaceholder')"
            />
          </div>
                    
          <div class="space-y-1">
            <Label
              for="token"
              class="text-[10px] uppercase tracking-wider font-bold ml-1 text-muted-foreground/80"
            >{{ t('features.auth.resetPassword.tokenLabel') }}</Label>
            <Input
              id="token"
              v-model="form.token"
              name="token"
              type="text"
              required
              class="auth-input font-mono text-[10px] h-9"
              placeholder="Reset Token"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <div class="space-y-1">
              <Label
                for="password"
                class="text-[10px] uppercase tracking-wider font-bold ml-1 text-muted-foreground/80"
              >{{ t('common.labels.password') }}</Label>
              <div class="relative">
                <Input
                  id="password"
                  v-model="form.password"
                  name="password"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="new-password"
                  required
                  class="auth-input h-9 text-sm pr-10"
                  :class="errors.password ? 'border-destructive/50 ring-destructive/20 focus:border-destructive' : ''"
                  :placeholder="t('features.auth.login.passwordPlaceholder')"
                />
                <button
                  type="button"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                  @click="showPassword = !showPassword"
                >
                  <Eye
                    v-if="!showPassword"
                    class="h-4 w-4"
                  />
                  <EyeOff
                    v-else
                    class="h-4 w-4"
                  />
                </button>
              </div>
            </div>
            <div class="space-y-1">
              <Label
                for="password_confirmation"
                class="text-[10px] uppercase tracking-wider font-bold ml-1 text-muted-foreground/80"
              >{{ t('common.labels.confirmPassword') }}</Label>
              <div class="relative">
                <Input
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  name="password_confirmation"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  autocomplete="new-password"
                  required
                  class="auth-input h-9 text-sm pr-10"
                  :placeholder="t('common.labels.confirmPassword')"
                />
                <button
                  type="button"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                  @click="showConfirmPassword = !showConfirmPassword"
                >
                  <Eye
                    v-if="!showConfirmPassword"
                    class="h-4 w-4"
                  />
                  <EyeOff
                    v-else
                    class="h-4 w-4"
                  />
                </button>
              </div>
            </div>
            <p
              v-if="errors.password"
              class="col-span-full text-[10px] text-destructive font-medium ml-1"
            >
              {{ errors.password[0] }}
            </p>
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
            :disabled="loading || !isValid"
          >
            <Loader2
              v-if="loading"
              class="mr-2 h-3 w-3 animate-spin"
            />
            <span
              v-if="loading"
              class="text-xs"
            >{{ t('common.messages.loading.processing') }}</span>
            <span
              v-else
              class="text-xs"
            >{{ t('features.auth.resetPassword.submit') }}</span>
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
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/modules/Core/stores/auth';
import { useCmsStore } from '@/modules/Cms/stores/cms';
import { useFormValidation } from '@/composables/useFormValidation';
import { resetPasswordSchema } from '@/schemas/auth';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js';
import LayoutTemplate from 'lucide-vue-next/dist/esm/icons/layout-template.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import EyeOff from 'lucide-vue-next/dist/esm/icons/eye-off.js';


// Shadcn Components
import {
    Button,
    Input,
    Label
} from '@/components/ui';

const router = useRouter();
const route = useRoute();
const { t } = useI18n();
const cmsStore = useCmsStore();
const authStore = useAuthStore();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(resetPasswordSchema);

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const form = reactive({
    email: '',
    token: '',
    password: '',
    password_confirmation: '',
});

const isValid = computed(() => {
    return !!form.email && 
           !!form.token && 
           !!form.password && 
           !!form.password_confirmation &&
           form.password === form.password_confirmation;
});

const message = ref('');
const messageType = ref('');
const loading = ref(false);

onMounted(() => {
    if (route.query.token) {
        form.token = route.query.token as string;
    }
    if (route.query.email) {
        form.email = route.query.email as string;
    }
});

const handleSubmit = async () => {
    // Client-side validation first
    if (!validateWithZod(form)) {
        return;
    }

    loading.value = true;
    clearErrors();
    message.value = '';

    const result = await authStore.resetPassword(form);

    if (result.success) {
        message.value = result.message || '';
        messageType.value = 'success';
        setTimeout(() => {
            router.push({ name: 'login' });
        }, 2000);
    } else {
        message.value = result.message || '';
        messageType.value = 'error';
        setErrors(result.errors || {});
    }

    loading.value = false;
};
</script>

