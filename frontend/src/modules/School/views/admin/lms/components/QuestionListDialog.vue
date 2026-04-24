<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[800px] h-[85vh] flex flex-col p-0">
      <DialogHeader class="p-6 pb-0 flex flex-row items-center justify-between space-y-0">
        <div>
          <DialogTitle>{{ $t('features.school.lms.actions.manageQuestions') }}: {{ bank?.name }}</DialogTitle>
          <DialogDescription>{{ $t('features.school.lms.subtitle') }}</DialogDescription>
        </div>
        <Button
          size="sm"
          @click="handleAddQuestion"
        >
          <LucideIcon
            name="Plus"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.lms.actions.addQuestion') }}
        </Button>
      </DialogHeader>

      <div class="p-6 flex-1 flex flex-col min-h-0 space-y-4">
        <div class="border rounded-md flex-1 overflow-auto">
          <table class="w-full text-sm">
            <thead class="bg-muted/50 sticky top-0">
              <tr>
                <th class="p-3 text-left font-medium w-12">
                  {{ $t('common.labels.no') }}
                </th>
                <th class="p-3 text-left font-medium">
                  {{ $t('features.school.lms.labels.questionContent') }}
                </th>
                <th class="p-3 text-left font-medium w-32">
                  {{ $t('common.labels.type') }}
                </th>
                <th class="p-3 text-left font-medium w-24">
                  {{ $t('common.labels.level') }}
                </th>
                <th class="p-3 text-right font-medium w-24">
                  {{ $t('common.labels.actions') }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y text-sm">
              <tr
                v-if="loading"
                class="text-center"
              >
                <td
                  colspan="5"
                  class="p-12 text-muted-foreground"
                >
                  <LucideIcon
                    name="Loader2"
                    class="w-6 h-6 mx-auto animate-spin text-primary"
                  />
                </td>
              </tr>
              <tr
                v-else-if="questions.length === 0"
                class="text-center"
              >
                <td
                  colspan="5"
                  class="p-12 text-muted-foreground"
                >
                  {{ $t('common.messages.noDataFound') }}
                </td>
              </tr>
              <tr
                v-for="(q, idx) in questions"
                :key="q.id"
                class="hover:bg-muted/30"
              >
                <td class="p-3">
                  {{ idx + 1 }}
                </td>
                <td class="p-3">
                  <div class="line-clamp-2">
                    {{ q.content }}
                  </div>
                </td>
                <td class="p-3 capitalize">
                  {{ $t(`features.school.lms.labels.types.${q.type}`) }}
                </td>
                <td class="p-3 capitalize">
                  <span :class="getLevelClass(q.level)">{{ $t(`features.school.lms.labels.${q.level}`) }}</span>
                </td>
                <td class="p-3 text-right">
                  <div class="flex justify-end gap-1">
                    <Button
                      variant="ghost"
                      size="icon"
                      :title="$t('common.actions.edit')"
                      class="h-8 w-8 text-primary"
                      @click="handleEditQuestion(q)"
                    >
                      <LucideIcon
                        name="Pencil"
                        class="w-4 h-4"
                      />
                    </Button>
                    <Button
                      variant="ghost"
                      size="icon"
                      :title="$t('common.actions.delete')"
                      class="h-8 w-8 text-destructive"
                      @click="handleDeleteQuestion(q.id)"
                    >
                      <LucideIcon
                        name="Trash2"
                        class="w-4 h-4"
                      />
                    </Button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </DialogContent>

    <QuestionFormDialog 
      v-model:open="formOpen" 
      :is-edit="!!selectedQuestion" 
      :initial-data="selectedQuestion" 
      :loading="saving"
      @submit="handleSaveQuestion"
    />
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription,
  Button, LucideIcon
} from '@/components/ui';
import QuestionFormDialog from './QuestionFormDialog.vue';
import { LmsService } from '@/modules/School/services/LmsService';
import { parseResponse } from '@/utils/responseParser';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps<{
  open: boolean;
  bank: any;
}>();

defineEmits(['update:open']);

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(false);
const saving = ref(false);
const questions = ref<any[]>([]);
const formOpen = ref(false);
const selectedQuestion = ref<any>(null);

const fetchData = async () => {
    if (!props.bank?.id) return;
    loading.value = true;
    try {
        const response = await LmsService.getQuestions(props.bank.id);
        questions.value = parseResponse(response).data;
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        loading.value = false;
    }
}

watch(() => props.open, (isOpen) => {
    if (isOpen) fetchData();
});

const getLevelClass = (level: string) => {
  switch (level) {
    case 'easy': return 'text-success font-medium';
    case 'medium': return 'text-warning font-medium';
    case 'hard': return 'text-destructive font-medium';
    default: return '';
  }
}

const handleAddQuestion = () => {
    selectedQuestion.value = null;
    formOpen.value = true;
}

const handleEditQuestion = (q: any) => {
    selectedQuestion.value = q;
    formOpen.value = true;
}

const handleSaveQuestion = async (formData: any) => {
    saving.value = true;
    try {
        if (selectedQuestion.value) {
            await LmsService.updateQuestion(selectedQuestion.value.id, formData);
            toast.success.action(t('features.school.lms.messages.updateSuccess') || 'Success updated');
        } else {
            await LmsService.storeQuestion(props.bank.id, formData);
            toast.success.action(t('features.school.lms.messages.addSuccess') || 'Success added');
        }
        formOpen.value = false;
        fetchData();
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        saving.value = false;
    }
}

const handleDeleteQuestion = async (id: number) => {
    if (await confirm({ title: t('common.actions.delete'), description: `${t('common.actions.delete')}?`, variant: 'destructive' })) {
        try {
            await LmsService.deleteQuestion(id);
            toast.success.action(t('features.school.lms.messages.deleteSuccess') || 'Success deleted');
            fetchData();
        } catch (e) {
            toast.error.fromResponse(e);
        }
    }
}
</script>
