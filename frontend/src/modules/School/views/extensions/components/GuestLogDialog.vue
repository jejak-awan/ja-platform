<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('common.labels.edit') || 'Edit' : $t('modules.school.extensions.tabs.guest') }}</DialogTitle>
      </DialogHeader>
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label for="name">{{ $t('common.labels.name') }} <span class="text-destructive">*</span></Label>
          <Input
            id="name"
            v-model="form.name"
            required
          />
        </div>
        <div class="space-y-2">
          <Label for="institution">{{ $t('common.labels.description') }} (Instansi)</Label>
          <Input
            id="institution"
            v-model="form.institution"
          />
        </div>
        <div class="space-y-2">
          <Label for="purpose">{{ $t('common.labels.description') }} <span class="text-destructive">*</span></Label>
          <Input
            id="purpose"
            v-model="form.purpose"
            required
          />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="phone">No. Telepon</Label>
            <Input
              id="phone"
              v-model="form.phone"
            />
          </div>
          <div class="space-y-2">
            <Label for="time">{{ $t('common.labels.date') }} <span class="text-destructive">*</span></Label>
            <Input
              id="time"
              v-model="form.visit_time"
              type="datetime-local"
              required
            />
          </div>
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
            {{ $t('common.labels.save') }}
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
  Button, Label, Input, LucideIcon
} from '@/shared/components/ui';

const props = defineProps<{
  open: boolean;
  isEdit?: boolean;
  initialData?: any;
  loading?: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

const form = ref({
  name: '',
  institution: '',
  purpose: '',
  phone: '',
  visit_time: new Date().toISOString().slice(0, 16),
});

watch(() => props.initialData, (val) => {
  if (val) form.value = { ...val, visit_time: val.visit_time ? new Date(val.visit_time).toISOString().slice(0, 16) : '' };
  else form.value = { name: '', institution: '', purpose: '', phone: '', visit_time: new Date().toISOString().slice(0, 16) };
}, { immediate: true });

const handleSubmit = () => {
  emit('submit', { ...form.value });
};
</script>
