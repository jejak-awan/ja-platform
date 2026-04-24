<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('common.actions.edit') + ' ' + $t('features.school.academic.tabs.years') : $t('common.actions.add') + ' ' + $t('features.school.academic.tabs.years') }}</DialogTitle>
      </DialogHeader>
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label for="year">{{ $t('features.school.academic.tabs.years') }} <span class="text-destructive">*</span></Label>
          <Input
            id="year"
            v-model="form.year"
            placeholder="Contoh: 2024/2025"
            required
          />
        </div>
        <div class="flex items-center space-x-2">
          <Switch
            id="active"
            :checked="form.is_active"
            @update:checked="v => form.is_active = v"
          />
          <Label for="active">{{ $t('features.school.academic.labels.setActiveYear') }}</Label>
        </div>
        <DialogFooter>
          <Button
            type="submit"
            :disabled="loading"
          >
            <LucideIcon
              v-if="loading"
              name="Loader2"
              class="w-4 h-4 mr-2 animate-spin"
            />
            {{ $t('common.actions.save') }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
  Button, Label, Input, Switch, LucideIcon
} from '@/components/ui';

const props = defineProps<{
  open: boolean;
  isEdit?: boolean;
  initialData?: any;
  loading?: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

const form = ref({
  year: '',
  is_active: false,
});

watch(() => props.initialData, (val) => {
  if (val) form.value = { ...val };
  else form.value = { year: '', is_active: false };
}, { immediate: true });

const handleSubmit = () => {
  emit('submit', { ...form.value, school_id: 1 });
};
</script>
