<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent class="max-w-2xl">
      <DialogHeader>
        <DialogTitle>
          {{ fieldGroup ? t('features.developer.custom_fields.groups.modal.title_edit') : t('features.developer.custom_fields.groups.modal.title_create') }}
        </DialogTitle>
      </DialogHeader>

      <form
        class="space-y-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label for="name">
            {{ t('features.developer.custom_fields.groups.modal.name_label') }} <span class="text-destructive">*</span>
          </Label>
          <Input
            id="name"
            v-model="form.name"
            required
            :placeholder="t('features.developer.custom_fields.groups.modal.name_placeholder')"
          />
        </div>

        <div class="space-y-2">
          <Label for="description">
            {{ t('features.developer.custom_fields.groups.modal.description_label') }}
          </Label>
          <Textarea
            id="description"
            v-model="form.description"
            rows="3"
            :placeholder="t('features.developer.custom_fields.groups.modal.description_placeholder')"
          />
        </div>

        <div class="space-y-2">
          <Label for="attachable_type">
            {{ t('features.developer.custom_fields.groups.modal.attach_label') }}
          </Label>
          <Select v-model="form.attachable_type">
            <SelectTrigger id="attachable_type">
              <SelectValue :placeholder="t('features.developer.custom_fields.groups.modal.attach_options.none')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="none">
                {{ t('features.developer.custom_fields.groups.modal.attach_options.none') }}
              </SelectItem>
              <SelectItem value="App\\Models\\Content">
                {{ t('features.developer.custom_fields.groups.modal.attach_options.content') }}
              </SelectItem>
              <SelectItem value="App\\Models\\Category">
                {{ t('features.developer.custom_fields.groups.modal.attach_options.category') }}
              </SelectItem>
              <SelectItem value="App\\Models\\Media">
                {{ t('features.developer.custom_fields.groups.modal.attach_options.media') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <DialogFooter>
          <Button
            variant="outline"
            type="button"
            @click="$emit('close')"
          >
            {{ t('features.developer.custom_fields.groups.modal.cancel') }}
          </Button>
          <Button
            type="submit"
            :disabled="saving || !isValid || (fieldGroup && !isDirty)"
          >
            <Loader2
              v-if="saving"
              class="w-4 h-4 mr-2 animate-spin"
            />
            {{ saving ? t('features.developer.custom_fields.groups.modal.saving') : (fieldGroup ? t('features.developer.custom_fields.groups.modal.update') : t('features.developer.custom_fields.groups.modal.create')) }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    Button,
    Input,
    Label,
    Textarea,
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem
} from '@/components/ui';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

interface FieldGroup {
    id: number | string;
    name: string;
    description: string | null;
    attachable_type: string | null;
}

const { t } = useI18n();

const props = defineProps<{
    fieldGroup?: FieldGroup | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved'): void;
}>();

const saving = ref(false);

interface FieldGroupForm {
    name: string;
    description: string;
    attachable_type: string | 'none';
}

const form = ref<FieldGroupForm>({
    name: '',
    description: '',
    attachable_type: 'none',
});

const initialForm = ref<FieldGroupForm | null>(null);

const isValid = computed(() => {
    return !!form.value.name?.trim();
});

const isDirty = computed(() => {
    if (!initialForm.value) return true;
    return JSON.stringify(form.value) !== JSON.stringify(initialForm.value);
});

const loadFieldGroup = () => {
    if (props.fieldGroup) {
        form.value = {
            name: props.fieldGroup.name || '',
            description: props.fieldGroup.description || '',
            attachable_type: props.fieldGroup.attachable_type || 'none',
        };
    } else {
        form.value = {
            name: '',
            description: '',
            attachable_type: 'none',
        };
    }
    initialForm.value = JSON.parse(JSON.stringify(form.value));
};

const toast = useToast();

const handleSubmit = async () => {
    saving.value = true;
    try {
        const payload = {
            ...form.value,
            attachable_type: form.value.attachable_type === 'none' ? null : form.value.attachable_type
        };
        if (props.fieldGroup) {
            await api.put(`/admin/cms/field-groups/${props.fieldGroup.id}`, payload);
            toast.success.update(t('features.developer.custom_fields.tabs.groups'));
        } else {
            await api.post('/admin/cms/field-groups', form.value);
            toast.success.create(t('features.developer.custom_fields.tabs.groups'));
        }
        emit('saved');
    } catch (error: unknown) {
        logger.error('Failed to save field group:', error);
        toast.error.fromResponse(error);
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadFieldGroup();
});
</script>

