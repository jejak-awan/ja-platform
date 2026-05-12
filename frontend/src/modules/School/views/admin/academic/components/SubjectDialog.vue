<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('common.actions.edit') + ' ' + $t('modules.school.academic.labels.subject') : $t('common.actions.add') + ' ' + $t('modules.school.academic.labels.subject') }}</DialogTitle>
      </DialogHeader>
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label for="code">{{ $t('modules.school.academic.labels.subjectCode') }} <span class="text-destructive">*</span></Label>
          <Input
            id="code"
            v-model="form.code"
            placeholder="Contoh: IPA-01"
            required
          />
        </div>
        <div class="space-y-2">
          <Label for="name">{{ $t('modules.school.academic.labels.subjectName') }} <span class="text-destructive">*</span></Label>
          <Input
            id="name"
            v-model="form.name"
            required
          />
        </div>
        <div class="space-y-2">
          <Label for="group">{{ $t('modules.school.academic.labels.group') }} ({{ $t('common.labels.optional') }})</Label>
          <Select v-model="form.group">
            <SelectTrigger><SelectValue :placeholder="$t('modules.school.academic.placeholders.selectSubjectGroup')" /></SelectTrigger>
            <SelectContent>
              <SelectItem value="A">
                {{ $t('modules.school.academic.labels.groupA') }}
              </SelectItem>
              <SelectItem value="B">
                {{ $t('modules.school.academic.labels.groupB') }}
              </SelectItem>
              <SelectItem value="C">
                {{ $t('modules.school.academic.labels.groupC') }}
              </SelectItem>
              <SelectItem value="Muatan Lokal">
                {{ $t('modules.school.academic.labels.localContent') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="kkm">{{ $t('modules.school.academic.labels.kkm') }}</Label>
          <Input
            id="kkm"
            v-model="form.kkm"
            type="number"
            min="0"
            max="100"
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
  Button, Label, Input, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, LucideIcon
} from '@/shared/components/ui';

const props = defineProps<{
  open: boolean;
  isEdit?: boolean;
  initialData?: any;
  loading?: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

const form = ref({
  code: '',
  name: '',
  group: '',
  kkm: 70,
});

watch(() => props.initialData, (val) => {
  if (val) form.value = { ...val };
  else form.value = { code: '', name: '', group: '', kkm: 70 };
}, { immediate: true });

const handleSubmit = () => {
  emit('submit', { ...form.value, school_id: 1 });
};
</script>
