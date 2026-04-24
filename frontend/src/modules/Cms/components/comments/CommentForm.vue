<template>
  <Card class="comment-form">
    <CardHeader>
      <CardTitle>{{ $t('features.comments.leaveComment') }}</CardTitle>
    </CardHeader>
    <CardContent>
      <form
        class="space-y-4"
        @submit.prevent="handleSubmit"
      >
        <div
          v-if="!authStore.isAuthenticated"
          class="grid grid-cols-1 md:grid-cols-2 gap-4"
        >
          <div class="space-y-2">
            <Label for="name">{{ $t('features.comments.name') }}</Label>
            <Input
              id="name"
              v-model="form.name"
              type="text"
              required
            />
          </div>
          <div class="space-y-2">
            <Label for="email">{{ $t('features.comments.email') }}</Label>
            <Input
              id="email"
              v-model="form.email"
              type="email"
              required
            />
          </div>
        </div>

        <div
          v-if="!authStore.isAuthenticated"
          class="space-y-2"
        >
          <Label>{{ $t('features.settings.labels.enable_captcha') }}</Label>
          <CaptchaWrapper 
            action="comment" 
            @verified="handleCaptchaVerified" 
          />
        </div>

        <div class="space-y-2">
          <Label for="body">{{ $t('features.comments.comment') }}</Label>
          <Textarea
            id="body"
            v-model="form.body"
            rows="4"
            required
          />
        </div>

        <Button
          type="submit"
          :disabled="loading"
          class="w-full sm:w-auto"
        >
          <Loader2
            v-if="loading"
            class="w-4 h-4 mr-2 animate-spin"
          />
          {{ loading ? $t('features.comments.submitting') : $t('features.comments.submit') }}
        </Button>
      </form>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useAuthStore } from '@/modules/Core/stores/auth';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import CaptchaWrapper from '@/modules/Core/components/captcha/CaptchaWrapper.vue';
import {
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    Input,
    Label,
    Textarea,
    Button
} from '@/components/ui';
import { useToast } from '@/composables/useToast';
import { useFormValidation } from '@/composables/useFormValidation';
import { commentSchema } from '@/schemas';

interface CommentForm {
    name: string;
    email: string;
    body: string;
    parent_id: number | string | null;
    captcha_token: string;
    captcha_input: string;
}

const props = defineProps<{
    contentId: number | string;
    parentId?: number | string | null;
}>();

const emit = defineEmits<{
    (e: 'submitted'): void;
}>();

useI18n();
const authStore = useAuthStore();
const toast = useToast();
const { validateWithZod, setErrors, clearErrors } = useFormValidation(commentSchema);

const form = ref<CommentForm>({
    name: '',
    email: '',
    body: '',
    parent_id: props.parentId ?? null,
    captcha_token: '',
    captcha_input: '',
});

const handleCaptchaVerified = (payload: { token: string; answer?: string; position?: string; code?: string }) => {
    form.value.captcha_token = payload.token;
    form.value.captcha_input = payload.answer || payload.position || payload.code || '';
};

const loading = ref(false);

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;

    loading.value = true;
    clearErrors();
    try {
        await api.post(`/ja/contents/${props.contentId}/comments`, form.value);
        
        // Reset form
        form.value = {
            name: '',
            email: '',
            body: '',
            parent_id: props.parentId ?? null,
            captcha_token: '',
            captcha_input: '',
        };

        emit('submitted');
        toast.success.create('Comment');
    } catch (error: unknown) {
        const err = error as import('axios').AxiosError<{ errors?: Record<string, string[]> }>;
        if (err.response?.status === 422) {
            setErrors(err.response.data?.errors || {});
        } else {
            toast.error.fromResponse(err);
        }
    } finally {
        loading.value = false;
    }
};
</script>


