<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('features.school.logistics.sarpras.actions.editLand') : $t('features.school.logistics.sarpras.actions.addLand') }}</DialogTitle>
      </DialogHeader>
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label for="name">{{ $t('features.school.logistics.sarpras.labels.landName') }} <span class="text-destructive">*</span></Label>
          <Input
            id="name"
            v-model="form.name"
            :placeholder="$t('features.school.logistics.sarpras.placeholders.landNameHint')"
            required
          />
        </div>
        <div class="space-y-2">
          <Label for="address">{{ $t('common.labels.address') }}</Label>
          <Textarea
            id="address"
            v-model="form.address"
            rows="3"
          />
        </div>
        <div class="space-y-2">
          <Label for="certificate_number">{{ $t('features.school.logistics.sarpras.labels.certificateNumber') }}</Label>
          <Input
            id="certificate_number"
            v-model="form.certificate_number"
          />
        </div>
        <div class="space-y-2">
          <Label for="area">{{ $t('features.school.logistics.sarpras.labels.area') }} (m2)</Label>
          <Input
            id="area"
            v-model="form.area"
            type="number"
          />
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
  Button, Label, Input, Textarea, LucideIcon
} from '@/components/ui';

const props = defineProps<{
  open: boolean;
  isEdit?: boolean;
  initialData?: any;
  loading?: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

const form = ref({
  name: '',
  address: '',
  certificate_number: '',
  area: 0,
});

watch(() => props.initialData, (val) => {
  if (val) form.value = { ...val };
  else form.value = { name: '', address: '', certificate_number: '', area: 0 };
}, { immediate: true });

const handleSubmit = () => {
  emit('submit', { ...form.value, school_id: 1 });
};
</script>
