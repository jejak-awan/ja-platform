<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('modules.school.extensions.labels.editBook') : $t('modules.school.extensions.labels.addBook') }}</DialogTitle>
      </DialogHeader>
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label for="title">{{ $t('modules.school.extensions.labels.bookTitle') }} <span class="text-destructive">*</span></Label>
          <Input
            id="title"
            v-model="form.title"
            required
          />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="isbn">{{ $t('modules.school.extensions.labels.isbn') }}</Label>
            <Input
              id="isbn"
              v-model="form.isbn"
            />
          </div>
          <div class="space-y-2">
            <Label for="author">{{ $t('modules.school.extensions.labels.author') }}</Label>
            <Input
              id="author"
              v-model="form.author"
            />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="publisher">{{ $t('modules.school.extensions.labels.publisher') }}</Label>
            <Input
              id="publisher"
              v-model="form.publisher"
            />
          </div>
          <div class="space-y-2">
            <Label for="year">{{ $t('modules.school.extensions.labels.publishYear') }}</Label>
            <Input
              id="year"
              v-model="form.publish_year"
              type="number"
            />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="qty">{{ $t('modules.school.extensions.labels.quantity') }} <span class="text-destructive">*</span></Label>
            <Input
              id="qty"
              v-model="form.quantity"
              type="number"
              required
              min="0"
            />
          </div>
          <div class="space-y-2">
            <Label for="loc">{{ $t('modules.school.extensions.labels.shelfLocation') }}</Label>
            <Input
              id="loc"
              v-model="form.location"
              :placeholder="$t('academic.placeholders.notesHint')"
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
  id: undefined as number | undefined,
  title: '',
  isbn: '',
  author: '',
  publisher: '',
  publish_year: new Date().getFullYear(),
  quantity: 1,
  location: '',
});

watch(() => props.initialData, (val) => {
  if (val) form.value = { ...val };
  else form.value = { id: undefined, title: '', isbn: '', author: '', publisher: '', publish_year: new Date().getFullYear(), quantity: 1, location: '' };
}, { immediate: true });

const handleSubmit = () => {
  emit('submit', { ...form.value });
};
</script>
