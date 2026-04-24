<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[600px] max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('features.school.lms.actions.editQuestion') : $t('features.school.lms.actions.addQuestion') }}</DialogTitle>
      </DialogHeader>
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label for="cnt">{{ $t('features.school.lms.labels.questionContent') }} <span class="text-destructive">*</span></Label>
          <Textarea
            id="cnt"
            v-model="form.content"
            required
            rows="4"
            :placeholder="$t('features.school.lms.placeholders.questionHint')"
          />
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>{{ $t('features.school.lms.labels.questionType') }} <span class="text-destructive">*</span></Label>
            <Select
              v-model="form.type"
              required
            >
              <SelectTrigger><SelectValue :placeholder="$t('features.school.lms.placeholders.selectType')" /></SelectTrigger>
              <SelectContent>
                <SelectItem value="multiple_choice">
                  {{ $t('features.school.lms.labels.types.multiple_choice') }}
                </SelectItem>
                <SelectItem value="essay">
                  {{ $t('features.school.lms.labels.types.essay') }}
                </SelectItem>
                <SelectItem value="true_false">
                  {{ $t('features.school.lms.labels.types.true_false') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label>{{ $t('features.school.lms.labels.complexityLevel') }} <span class="text-destructive">*</span></Label>
            <Select
              v-model="form.level"
              required
            >
              <SelectTrigger><SelectValue :placeholder="$t('features.school.lms.placeholders.selectLevel')" /></SelectTrigger>
              <SelectContent>
                <SelectItem value="easy">
                  {{ $t('features.school.lms.labels.easy') }}
                </SelectItem>
                <SelectItem value="medium">
                  {{ $t('features.school.lms.labels.medium') }}
                </SelectItem>
                <SelectItem value="hard">
                  {{ $t('features.school.lms.labels.hard') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <!-- Options for Multiple Choice -->
        <div
          v-if="form.type === 'multiple_choice'"
          class="space-y-3 p-4 border rounded-md"
        >
          <div class="flex justify-between items-center">
            <Label class="text-sm font-bold">{{ $t('features.school.lms.labels.answerOptions') }}</Label>
          </div>
          <div
            v-for="(_, idx) in form.options"
            :key="idx"
            class="flex gap-2 items-center"
          >
            <span class="w-6 text-xs font-bold">{{ String.fromCharCode(65 + Number(idx)) }}</span>
            <Input
              v-model="form.options[idx]"
              :placeholder="$t('features.school.lms.placeholders.optionHint')"
            />
          </div>
          <div class="space-y-2 pt-2 border-t">
            <Label>{{ $t('features.school.lms.labels.correctAnswer') }} ({{ $t('common.labels.other') }})</Label>
            <Select v-model="form.answer">
              <SelectTrigger><SelectValue :placeholder="$t('features.school.lms.placeholders.selectAnswer')" /></SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="(_, idx) in form.options"
                  :key="idx"
                  :value="String.fromCharCode(65 + Number(idx))"
                >
                  {{ $t('common.labels.other') }} {{ String.fromCharCode(65 + Number(idx)) }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <!-- Options for True/False -->
        <div
          v-else-if="form.type === 'true_false'"
          class="space-y-2"
        >
          <Label>{{ $t('features.school.lms.labels.correctAnswer') }}</Label>
          <Select v-model="form.answer">
            <SelectTrigger><SelectValue :placeholder="$t('features.school.lms.placeholders.selectAnswer')" /></SelectTrigger>
            <SelectContent>
              <SelectItem value="True">
                {{ $t('common.labels.true') }}
              </SelectItem>
              <SelectItem value="False">
                {{ $t('common.labels.false') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Answer for Essay -->
        <div
          v-else
          class="space-y-2"
        >
          <Label for="ans">{{ $t('features.school.lms.labels.essayHint') }} <span class="text-destructive">*</span></Label>
          <Textarea
            id="ans"
            v-model="form.answer"
            required
            rows="3"
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
  Button, Label, Input, Textarea, LucideIcon, Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/components/ui';

const props = defineProps<{
  open: boolean;
  isEdit?: boolean;
  initialData?: any;
  loading?: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

const form = ref<any>({
  content: '',
  type: 'multiple_choice',
  level: 'medium',
  options: ['', '', '', '', ''],
  answer: '',
  media_path: null,
});

watch(() => props.initialData, (val) => {
  if (val) {
    form.value = { ...val };
    if (!form.value.options) form.value.options = ['', '', '', '', ''];
  } else {
    form.value = {
      content: '',
      type: 'multiple_choice',
      level: 'medium',
      options: ['', '', '', '', ''],
      answer: '',
      media_path: null,
    };
  }
}, { immediate: true });

const handleSubmit = () => {
    // Only send options if multiple choice
    const payload = { ...form.value };
    if (payload.type !== 'multiple_choice') {
        payload.options = null;
    }
    emit('submit', payload);
};
</script>
