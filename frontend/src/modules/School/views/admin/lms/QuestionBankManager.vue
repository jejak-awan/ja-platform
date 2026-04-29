<template>
  <div class="space-y-8 p-6 animate-in fade-in duration-700">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-2">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20">
            <LucideIcon
              name="Book"
              class="w-5 h-5 text-primary"
            />
          </div>
          <h1 class="text-3xl font-black tracking-tight text-foreground uppercase">
            {{ $t('features.school.lms.title') }}
          </h1>
        </div>
        <p class="text-muted-foreground text-sm font-medium italic">
          {{ $t('features.school.lms.subtitle') }}
        </p>
      </div>
      <div class="flex gap-2">
        <Button 
          class="rounded-xl shadow-sm px-6"
          @click="handleAdd"
        >
          <LucideIcon
            name="Plus"
            class="w-4 h-4 mr-2"
          />
          {{ activeTab === 'bank' ? $t('features.school.lms.actions.addBank') : $t('features.school.lms.actions.addExam') }}
        </Button>
      </div>
      <ExamQuestionDialog 
        v-model:open="examQuestionsOpen" 
        :exam="selectedExam" 
      />
    </div>

    <Tabs
      v-model="activeTab"
      class="w-full"
    >
      <TabsList class="p-2 bg-muted/50 rounded-2xl inline-flex h-auto gap-2 mb-6">
        <TabsTrigger 
          value="bank" 
          class="rounded-xl px-8 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
        >
          <LucideIcon
            name="Layers"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.lms.tabs.bank') }}
        </TabsTrigger>
        <TabsTrigger 
          value="exam" 
          class="rounded-xl px-8 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
        >
          <LucideIcon
            name="Monitor"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.lms.tabs.exam') }}
        </TabsTrigger>
      </TabsList>

      <div class="mt-4">
        <Card class="border border-border/40 bg-card shadow-none rounded-xl overflow-hidden">
          <CardContent class="p-0">
            <DataTable 
              :table="table"
              :loading="loading"
            />
          </CardContent>
        </Card>
      </div>
    </Tabs>

    <!-- Dialogs -->
    <QuestionBankDialog
      v-model:open="dialogs.bank"
      :is-edit="!!selectedItem"
      :initial-data="selectedItem"
      :loading="saving"
      @submit="handleSave"
    />
    <ExamDialog
      v-model:open="dialogs.exam"
      :is-edit="!!selectedItem"
      :initial-data="selectedItem"
      :loading="saving"
      @submit="handleSave"
    />
    <QuestionListDialog
      v-model:open="dialogs.questions"
      :bank="selectedItem"
    />

    <ConfirmModal ref="confirmModal" />
  </div>
</template>

<script setup lang="ts">
import { ref, watch, h, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { LmsService } from '@/modules/School/services/LmsService';
import {
  Tabs, TabsList, TabsTrigger, Card, CardContent, Button, LucideIcon, DataTable, ConfirmModal
} from '@/components/ui';
import { 
  useVueTable, 
  getCoreRowModel, 
  createColumnHelper,
} from '@tanstack/vue-table';
import { useToast } from '@/composables/useToast';
import { parseResponse } from '@/utils/responseParser';

// Components
import QuestionBankDialog from './components/QuestionBankDialog.vue';
import ExamDialog from './components/ExamDialog.vue';
import QuestionListDialog from './components/QuestionListDialog.vue';
import ExamQuestionDialog from './components/ExamQuestionDialog.vue';

const { t } = useI18n();
const toast = useToast();
const confirmModal = ref<any>(null);
const activeTab = ref('bank');
const loading = ref(false);
const saving = ref(false);
const rows = ref<any[]>([]);
const selectedItem = ref<any>(null);
const examQuestionsOpen = ref(false);
const selectedExam = ref<any>(null);

const dialogs = ref({
  bank: false,
  exam: false,
  questions: false,
});

const columnHelper = createColumnHelper<any>();

const actionColumn = columnHelper.display({
  id: 'actions',
  header: t('common.labels.actions'),
  cell: ({ row }) => h('div', { class: 'flex gap-2' }, [
    activeTab.value === 'bank' ? h(Button, {
      variant: 'ghost',
      size: 'icon',
      title: t('features.school.lms.actions.manageQuestions'),
      class: 'h-8 w-8 text-muted-foreground hover:bg-success/10 hover:text-success rounded-lg',
      onClick: (e: MouseEvent) => {
          e.stopPropagation();
          handleManageQuestions(row.original);
      }
    }, () => h(LucideIcon, { name: 'ListChecks', class: 'w-4 h-4' })) : null,
    activeTab.value === 'exam' ? h(Button, {
      variant: 'ghost',
      size: 'icon',
      title: t('features.school.lms.actions.manageQuestions'),
      class: 'h-8 w-8 text-primary',
      onClick: (e: MouseEvent) => {
          e.stopPropagation();
          handleManageExamQuestions(row.original);
      }
    }, () => h(LucideIcon, { name: 'ListChecks', class: 'w-4 h-4' })) : null,
    h(Button, {
      variant: 'ghost',
      size: 'icon',
      title: t('common.actions.edit'),
      class: 'h-8 w-8 text-muted-foreground hover:bg-primary/10 hover:text-primary rounded-lg',
      onClick: (e: MouseEvent) => {
          e.stopPropagation();
          handleEdit(row.original);
      }
    }, () => h(LucideIcon, { name: 'Pencil', class: 'w-4 h-4' })),
    h(Button, {
      variant: 'ghost',
      size: 'icon',
      title: t('common.actions.delete'),
      class: 'h-8 w-8 text-muted-foreground hover:bg-destructive/10 hover:text-destructive rounded-lg',
      onClick: (e: MouseEvent) => {
          e.stopPropagation();
          handleDelete(row.original);
      }
    }, () => h(LucideIcon, { name: 'Trash2', class: 'w-4 h-4' }))
  ]),
});
const bankColumns = [
  columnHelper.accessor('name', { header: t('features.school.lms.labels.bankName') }),
  columnHelper.accessor('subject.name', { header: t('features.school.academic.labels.subject') }),
  columnHelper.accessor('questions_count', { header: t('features.school.lms.labels.questionCount'), cell: info => info.getValue() || 0 }),
  actionColumn
];

const examColumns = [
  columnHelper.accessor('title', { header: t('features.school.lms.labels.examName') }),
  columnHelper.accessor('subject.name', { header: t('features.school.academic.labels.subject') }),
  columnHelper.accessor('start_time', { 
    header: t('features.school.lms.labels.startTime'), 
    cell: info => new Date(info.getValue()).toLocaleString(t('common.language') === 'id' ? 'id-ID' : 'en-US')
  }),
  columnHelper.accessor('is_active', { 
    header: t('common.labels.status'),
    cell: info => info.getValue() ? h('span', { class: 'px-2 py-0.5 rounded-lg bg-success/10 text-success border border-success/20 text-[10px] font-black uppercase tracking-widest' }, t('features.school.lms.labels.active')) : h('span', { class: 'px-2 py-0.5 rounded-lg bg-muted text-muted-foreground text-[10px] font-black uppercase tracking-widest' }, t('features.school.lms.labels.draft'))
  }),
  actionColumn
];

const table = useVueTable({
  get data() { return rows.value },
  get columns() { return activeTab.value === 'bank' ? bankColumns : examColumns },
  getCoreRowModel: getCoreRowModel(),
});

const fetchData = async () => {
  loading.value = true;
  try {
    const response = activeTab.value === 'bank' ? await LmsService.getQuestionBanks() : await LmsService.getExams();
    const { data } = parseResponse(response);
    rows.value = data;
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};

watch(activeTab, () => {
  rows.value = [];
  fetchData();
});

const handleAdd = () => {
    selectedItem.value = null;
    openDialog();
}

const handleManageQuestions = (bank: any) => {
    selectedItem.value = bank; // Use selectedItem for QuestionListDialog
    dialogs.value.questions = true;
}

const handleManageExamQuestions = (exam: any) => {
    selectedExam.value = exam;
    examQuestionsOpen.value = true;
}

const handleEdit = (item: any) => {
    selectedItem.value = item;
    openDialog();
}

const openDialog = () => {
    if (activeTab.value === 'bank') dialogs.value.bank = true;
    else if (activeTab.value === 'exam') dialogs.value.exam = true;
}

const handleSave = async (formData: any) => {
    saving.value = true;
    try {
        if (activeTab.value === 'bank') {
            if (selectedItem.value) {
                await LmsService.updateQuestionBank(selectedItem.value.id, formData);
                toast.success.action(t('features.school.lms.messages.updateSuccess') || 'Data success updated');
            } else {
                await LmsService.storeQuestionBank(formData);
                toast.success.action(t('features.school.lms.messages.addSuccess') || 'Data success added');
            }
        } else {
            if (selectedItem.value) {
                await LmsService.updateExam(selectedItem.value.id, formData);
                toast.success.action(t('features.school.lms.messages.updateSuccess') || 'Data success updated');
            } else {
                await LmsService.storeExam(formData);
                toast.success.action(t('features.school.lms.messages.addSuccess') || 'Data success added');
            }
        }
        
        dialogs.value.bank = false;
        dialogs.value.exam = false;
        fetchData();
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        saving.value = false;
    }
}

const handleDelete = async (item: any) => {
    const confirmed = await confirmModal.value.confirm({
        title: t('common.actions.delete'),
        message: `${t('common.actions.delete')} "${item.name || item.title}"?`,
        variant: 'destructive'
    });

    if (confirmed) {
        try {
            if (activeTab.value === 'bank') {
                await LmsService.deleteQuestionBank(item.id);
            } else {
                await LmsService.deleteExam(item.id);
            }
            toast.success.action(t('features.school.lms.messages.deleteSuccess') || 'Data deleted');
            fetchData();
        } catch (e) {
            toast.error.fromResponse(e);
        }
    }
}

onMounted(() => {
  fetchData();
});
</script>
