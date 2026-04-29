<template>
  <div class="space-y-8 p-6 animate-in fade-in duration-700">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-2">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20">
            <LucideIcon
              name="GraduationCap"
              class="w-5 h-5 text-primary"
            />
          </div>
          <h1 class="text-3xl font-black tracking-tight text-foreground uppercase">
            {{ $t('features.school.academic.title') }}
          </h1>
        </div>
        <p class="text-muted-foreground text-sm font-medium italic">
          {{ $t('features.school.academic.subtitle') }}
        </p>
      </div>
      <div class="flex gap-2">
        <Button
          v-if="activeTab !== 'timetable'"
          class="rounded-xl shadow-sm px-6"
          @click="handleAdd"
        >
          <LucideIcon
            name="Plus"
            class="w-4 h-4 mr-2"
          />
          {{ $t('common.actions.add') }}
        </Button>
      </div>
    </div>

    <Tabs
      v-model="activeTab"
      class="w-full"
    >
      <TabsList class="p-2 bg-muted/50 rounded-2xl inline-flex h-auto gap-2 mb-6">
        <TabsTrigger 
          value="subjects" 
          class="rounded-xl px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
        >
          <LucideIcon
            name="BookOpen"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.academic.tabs.subjects') }}
        </TabsTrigger>
        <TabsTrigger 
          value="study-groups" 
          class="rounded-xl px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
        >
          <LucideIcon
            name="Users2"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.academic.tabs.studyGroups') }}
        </TabsTrigger>
        <TabsTrigger 
          value="timetable" 
          class="rounded-xl px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
        >
          <LucideIcon
            name="Calendar"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.academic.tabs.timetable') }}
        </TabsTrigger>
        <TabsTrigger 
          value="years" 
          class="rounded-xl px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
        >
          <LucideIcon
            name="CalendarDays"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.academic.tabs.years') }}
        </TabsTrigger>
      </TabsList>

      <div class="mt-4">
        <template v-if="activeTab === 'timetable'">
          <Timetable />
        </template>
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
    <YearDialog 
      v-model:open="dialogs.year" 
      :is-edit="!!selectedItem" 
      :initial-data="selectedItem" 
      :loading="saving"
      @submit="handleSave"
    />
    <SubjectDialog 
      v-model:open="dialogs.subject" 
      :is-edit="!!selectedItem" 
      :initial-data="selectedItem" 
      :loading="saving"
      @submit="handleSave"
    />
    <StudyGroupDialog 
      v-model:open="dialogs.studyGroup" 
      :is-edit="!!selectedItem" 
      :initial-data="selectedItem" 
      :loading="saving"
      @submit="handleSave"
    />
    <StudyGroupMemberDialog
      v-model:open="dialogs.members"
      :group="selectedItem"
    />

    <ConfirmModal ref="confirmModal" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, h, toRefs } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  Tabs, TabsList, TabsTrigger, Card, CardContent, Button, LucideIcon, DataTable, ConfirmModal
} from '@/components/ui';
import { 
  useVueTable, 
  getCoreRowModel, 
  createColumnHelper,
} from '@tanstack/vue-table';
import { useToast } from '@/composables/useToast';
import { useAcademicStore } from '@/modules/School/stores/academic';

// Components
import YearDialog from './components/YearDialog.vue';
import SubjectDialog from './components/SubjectDialog.vue';
import StudyGroupDialog from './components/StudyGroupDialog.vue';
import StudyGroupMemberDialog from './components/StudyGroupMemberDialog.vue';
import Timetable from './Timetable.vue';

const { t } = useI18n();
const toast = useToast();
const confirmModal = ref<any>(null);
const activeTab = ref('subjects');
const saving = ref(false);
const academicStore = useAcademicStore();
const { years, subjects, studyGroups, loading } = toRefs(academicStore);

const rows = computed(() => {
  switch (activeTab.value) {
    case 'subjects': return subjects.value;
    case 'study-groups': return studyGroups.value;
    case 'years': return years.value;
    default: return [];
  }
});

const selectedItem = ref<any>(null);

const dialogs = ref({
  year: false,
  subject: false,
  studyGroup: false,
  members: false,
});

const columnHelper = createColumnHelper<any>();

const actionColumn = columnHelper.display({
  id: 'actions',
  header: t('common.labels.status'), // Or actions if you prefer, but labels.json has status
  cell: ({ row }) => h('div', { class: 'flex gap-2' }, [
      activeTab.value === 'study-groups' ? h(Button, {
      variant: 'ghost',
      size: 'icon',
      title: t('features.school.academic.actions.manageMembers'),
      class: 'h-8 w-8 text-muted-foreground hover:bg-success/10 hover:text-success rounded-lg',
      onClick: (e: MouseEvent) => {
          e.stopPropagation();
          selectedItem.value = row.original;
          dialogs.value.members = true;
      }
    }, () => h(LucideIcon, { name: 'Users', class: 'w-4 h-4' })) : null,
    h(Button, {
      variant: 'ghost',
      size: 'icon',
      class: 'h-8 w-8 text-muted-foreground hover:bg-primary/10 hover:text-primary rounded-lg',
      onClick: (e: MouseEvent) => {
          e.stopPropagation();
          handleEdit(row.original);
      }
    }, () => h(LucideIcon, { name: 'Pencil', class: 'w-4 h-4' })),
    h(Button, {
      variant: 'ghost',
      size: 'icon',
      class: 'h-8 w-8 text-muted-foreground hover:bg-destructive/10 hover:text-destructive rounded-lg',
      onClick: (e: MouseEvent) => {
          e.stopPropagation();
          handleDelete(row.original);
      }
    }, () => h(LucideIcon, { name: 'Trash2', class: 'w-4 h-4' }))
  ]),
});

const subjectColumns = [
  columnHelper.accessor('code', { header: t('features.school.academic.labels.subjectCode') }),
  columnHelper.accessor('name', { header: t('features.school.academic.labels.subjectName') }),
  columnHelper.accessor('group', { header: t('features.school.academic.labels.group') }),
  columnHelper.accessor('kkm', { header: t('features.school.academic.labels.kkm') }),
  actionColumn
];

const studyGroupColumns = [
  columnHelper.accessor('name', { header: t('features.school.academic.labels.groupName') }),
  columnHelper.accessor('level.name', { header: t('common.labels.single_level') }), // Using single_level for Jenjang or add level
  columnHelper.accessor('homeroom_teacher.full_name', { header: t('features.school.academic.labels.homeroomTeacher'), cell: info => info.getValue() || '-' }),
  actionColumn
];

const yearColumns = [
  columnHelper.accessor('year', { header: t('features.school.academic.tabs.years') }),
  columnHelper.accessor('is_active', { 
    header: t('common.labels.status'),
    cell: info => info.getValue() ? h('span', { class: 'px-2 py-0.5 rounded-lg bg-success/10 text-success border border-success/20 text-[10px] font-black uppercase tracking-widest' }, t('common.labels.active')) : h('span', { class: 'px-2 py-0.5 rounded-lg bg-muted text-muted-foreground text-[10px] font-black uppercase tracking-widest' }, t('common.labels.inactive'))
  }),
  actionColumn
];

const currentColumns = computed(() => {
  switch (activeTab.value) {
    case 'subjects': return subjectColumns;
    case 'study-groups': return studyGroupColumns;
    case 'years': return yearColumns;
    default: return subjectColumns;
  }
});

const table = useVueTable({
  get data() { return rows.value },
  get columns() { return currentColumns.value },
  getCoreRowModel: getCoreRowModel(),
});

const fetchData = async () => {
    switch (activeTab.value) {
        case 'subjects': await academicStore.fetchSubjects(); break;
        case 'study-groups': await academicStore.fetchStudyGroups(); break;
        case 'years': await academicStore.fetchAcademicYears(); break;
    }
};

watch(activeTab, () => {
  fetchData();
});

const handleAdd = () => {
    selectedItem.value = null;
    openDialog();
}

const handleEdit = (item: any) => {
    selectedItem.value = item;
    openDialog();
}

const openDialog = () => {
    if (activeTab.value === 'years') dialogs.value.year = true;
    else if (activeTab.value === 'subjects') dialogs.value.subject = true;
    else if (activeTab.value === 'study-groups') dialogs.value.studyGroup = true;
}

const handleSave = async (formData: any) => {
    saving.value = true;
    try {
        const id = selectedItem.value ? selectedItem.value.id : null;
        if (activeTab.value === 'years') await academicStore.saveAcademicYear(formData, id);
        else if (activeTab.value === 'subjects') await academicStore.saveSubject(formData, id);
        else if (activeTab.value === 'study-groups') await academicStore.saveStudyGroup(formData, id);
        
        toast.success.action(id ? t('features.school.academic.messages.updateSuccess') : t('features.school.academic.messages.addSuccess'));
        
        // Close all dialogs
        Object.keys(dialogs.value).forEach(k => (dialogs.value as any)[k] = false);
    } catch (e: any) {
        toast.error.fromResponse(e);
    } finally {
        saving.value = false;
    }
}

const handleDelete = async (item: any) => {
    const confirmed = await confirmModal.value.confirm({
        title: t('common.actions.delete'),
        message: t('features.school.academic.messages.deleteConfirm', { name: item.name || item.year }),
        variant: 'destructive'
    });

    if (confirmed) {
        try {
            if (activeTab.value === 'years') await academicStore.deleteAcademicYear(item.id);
            else if (activeTab.value === 'subjects') await academicStore.deleteSubject(item.id);
            else if (activeTab.value === 'study-groups') await academicStore.deleteStudyGroup(item.id);
            toast.success.action(t('features.school.academic.messages.deleteSuccess'));
        } catch (e: any) {
            toast.error.fromResponse(e);
        }
    }
}

onMounted(() => {
  fetchData();
});
</script>
