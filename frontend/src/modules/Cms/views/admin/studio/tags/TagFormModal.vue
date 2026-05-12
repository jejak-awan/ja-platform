<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('modules.cms.tags.form.editTitle') : $t('modules.cms.tags.form.createTitle') }}</DialogTitle>
        <DialogDescription>
          {{ isEdit ? $t('modules.cms.tags.form.editDescription') : $t('modules.cms.tags.form.createDescription') }}
        </DialogDescription>
      </DialogHeader>

      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <!-- Name -->
        <div class="space-y-2">
          <Label>
            {{ $t('modules.cms.tags.form.name') }} <span class="text-destructive">*</span>
          </Label>
          <Input
            v-model="form.name"
            required
            :class="errors.name ? 'border-destructive focus-visible:ring-destructive' : ''"
            :placeholder="$t('modules.cms.tags.form.namePlaceholder')"
            @input="generateSlug"
          />
          <p
            v-if="errors.name"
            class="text-sm text-destructive"
          >
            {{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}
          </p>
        </div>

        <!-- Slug -->
        <div class="space-y-2">
          <Label>
            {{ $t('modules.cms.tags.form.slug') }} <span class="text-destructive">*</span>
          </Label>
          <Input
            v-model="form.slug"
            required
            :class="errors.slug ? 'border-destructive focus-visible:ring-destructive' : ''"
            :placeholder="$t('modules.cms.tags.form.slugPlaceholder')"
          />
          <p class="text-xs text-muted-foreground">
            {{ $t('modules.cms.tags.form.slugHelp') }}
          </p>
          <p
            v-if="errors.slug"
            class="text-sm text-destructive"
          >
            {{ Array.isArray(errors.slug) ? errors.slug[0] : errors.slug }}
          </p>
        </div>

        <!-- Description -->
        <div class="space-y-2">
          <Label>
            {{ $t('modules.cms.tags.form.description') }}
          </Label>
          <Textarea
            v-model="form.description"
            rows="3"
            :placeholder="$t('modules.cms.tags.form.descriptionPlaceholder')"
          />
        </div>

        <DialogFooter>
          <Button
            type="button"
            variant="outline"
            @click="$emit('update:open', false)"
          >
            {{ $t('common.actions.cancel') }}
          </Button>
          <Button
            type="submit"
            :disabled="saving || !isValid"
          >
            <Loader2
              v-if="saving"
              class="w-4 h-4 mr-2 animate-spin"
            />
            {{ saving ? (isEdit ? $t('common.messages.loading.updating') : $t('common.messages.loading.creating')) : (isEdit ? $t('common.actions.update') : $t('common.actions.create')) }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import api from '@/core/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { tagSchema } from '@/shared/schemas';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import type { Tag } from '@/modules/Cms/types/cms';

// Shadcn UI
import {
    Button,
    Input,
    Label,
    Textarea,
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle
} from '@/shared/components/ui';

interface TagForm {
    name: string;
    slug: string;
    description: string;
}

const props = defineProps<{
    open: boolean;
    tag?: Tag | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'success': [];
}>();

const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(tagSchema);

const saving = ref(false);
const isEdit = computed(() => !!props.tag);

const form = ref<TagForm>({
    name: '',
    slug: '',
    description: '',
});

// Initialize form when opening
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        clearErrors();
        if (props.tag) {
            form.value = {
                name: props.tag.name || '',
                slug: props.tag.slug || '',
                description: props.tag.description || '',
            };
        } else {
            // Reset
            form.value = {
                name: '',
                slug: '',
                description: '',
            };
        }
    }
});

const isValid = computed(() => {
    return !!form.value.name?.trim();
});

const generateSlug = () => {
    if (!isEdit.value || !form.value.slug || form.value.slug === slugify(form.value.name)) {
         form.value.slug = slugify(form.value.name);
    }
};

const slugify = (text: string) => {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w-\s]+/g, '')
        .replace(/-+/, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
};

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;

    saving.value = true;
    clearErrors();
    try {
        if (isEdit.value) {
            await api.put(`/admin/cms/tags/${props.tag?.id}`, form.value);
            toast.success.update('Tag');
        } else {
            await api.post('/admin/cms/tags', form.value);
            toast.success.create('Tag');
        }
        
        emit('success');
        emit('update:open', false);
    } catch (error: unknown) {
        if (error && typeof error === 'object' && 'response' in error) {
            const err = error as { response: { status: number, data: { errors: Record<string, string[]> } } };
            if (err.response?.status === 422) {
                setErrors(err.response.data.errors || {});
            }
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        saving.value = false;
    }
};
</script>
