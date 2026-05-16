<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent class="sm:max-w-[600px]">
      <DialogHeader>
        <DialogTitle>
          {{ redirect ? $t('features.redirects.modals.redirect.titleEdit') : $t('features.redirects.modals.redirect.titleCreate') }}
        </DialogTitle>
      </DialogHeader>

      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label>
            {{ $t('features.redirects.modals.redirect.from') }} <span class="text-destructive">*</span>
          </Label>
          <Input
            v-model="form.from_url"
            required
            :placeholder="$t('features.redirects.modals.redirect.fromPlaceholder')"
          />
          <span
            v-if="errors.from_url"
            class="text-xs text-destructive"
          >{{ errors.from_url[0] }}</span>
          <p class="text-xs text-muted-foreground">
            {{ $t('features.redirects.modals.redirect.fromHint') }}
          </p>
        </div>

        <div class="space-y-2">
          <Label>
            {{ $t('features.redirects.modals.redirect.to') }} <span class="text-destructive">*</span>
          </Label>
          <Input
            v-model="form.to_url"
            required
            :placeholder="$t('features.redirects.modals.redirect.toPlaceholder')"
          />
          <span
            v-if="errors.to_url"
            class="text-xs text-destructive"
          >{{ errors.to_url[0] }}</span>
          <p class="text-xs text-muted-foreground">
            {{ $t('features.redirects.modals.redirect.toHint') }}
          </p>
        </div>

        <div class="space-y-2">
          <Label>
            {{ $t('features.redirects.modals.redirect.code') }} <span class="text-destructive">*</span>
          </Label>
          <Select v-model="form.status_code">
            <SelectTrigger>
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="301">
                {{ $t('features.redirects.modals.redirect.codes.p301') }}
              </SelectItem>
              <SelectItem value="302">
                {{ $t('features.redirects.modals.redirect.codes.t302') }}
              </SelectItem>
              <SelectItem value="307">
                {{ $t('features.redirects.modals.redirect.codes.t307') }}
              </SelectItem>
              <SelectItem value="308">
                {{ $t('features.redirects.modals.redirect.codes.p308') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="flex items-center space-x-2 pt-2">
          <Checkbox
            id="is_active"
            v-model:checked="form.is_active"
          />
          <Label
            for="is_active"
            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
          >
            {{ $t('features.redirects.modals.redirect.active') }}
          </Label>
        </div>
      </form>

      <DialogFooter>
        <Button
          variant="outline"
          @click="$emit('close')"
        >
          {{ $t('common.actions.cancel') }}
        </Button>
        <Button
          :disabled="isSubmitting || !isValid || (redirect && !isDirty)"
          @click="handleSubmit"
        >
          <Loader2
            v-if="isSubmitting"
            class="w-4 h-4 mr-2 animate-spin"
          />
          {{ isSubmitting ? $t('features.redirects.modals.redirect.saving') : (redirect ? $t('common.actions.update') : $t('common.actions.create')) }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    Button,
    Input,
    Label,
    Checkbox,
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem
} from '@/shared/components/ui';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { redirectSchema } from '@/shared/schemas/common';

interface Redirect {
    id: number | string;
    from_url: string;
    to_url: string;
    status_code: number;
    is_active: boolean;
}

const props = defineProps<{
    redirect?: Redirect | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved'): void;
}>();

const { t } = useI18n();
const toast = useToast();
const isSubmitting = ref(false);

const { errors, validateWithZod, setErrors } = useFormValidation(redirectSchema);

interface RedirectForm {
    from_url: string;
    to_url: string;
    status_code: string;
    is_active: boolean;
}

const form = ref<RedirectForm>({
    from_url: '',
    to_url: '',
    status_code: '301',
    is_active: true,
});

const initialForm = ref<RedirectForm | null>(null);

const isDirty = computed(() => {
    if (!props.redirect || !initialForm.value) return true; // Always true for create or if init not set
    return JSON.stringify(form.value) !== JSON.stringify(initialForm.value);
});

const isValid = computed(() => {
    return !!form.value.from_url?.trim() && !!form.value.to_url?.trim() && !!form.value.status_code;
});

const loadRedirect = () => {
    if (props.redirect) {
        form.value = {
            from_url: props.redirect.from_url || '',
            to_url: props.redirect.to_url || '',
            status_code: String(props.redirect.status_code) || '301',
            is_active: props.redirect.is_active !== undefined ? props.redirect.is_active : true,
        };
        initialForm.value = JSON.parse(JSON.stringify(form.value));
    }
};

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;

    isSubmitting.value = true;
    try {
        const payload = {
            ...form.value,
            status_code: Number(form.value.status_code)
        };
        if (props.redirect) {
            await api.put(`/manage/cms/redirects/${props.redirect.id}`, payload);
            toast.success.update(t('features.redirects.title'));
        } else {
            await api.post('/manage/cms/redirects', payload);
            toast.success.create(t('features.redirects.title'));
        }
        emit('saved');
    } catch (error: unknown) {
        const err = error as { response?: { status?: number; data?: { errors?: Record<string, string[]> } } };
        if (err.response?.status === 422) {
            setErrors(((err.response?.data?.errors) || {}) as Record<string, string[]>);
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        isSubmitting.value = false;
    }
};

onMounted(() => {
    loadRedirect();
});
</script>

