<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold text-foreground">
          {{ $t('features.school.academic.title') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ $t('features.school.academic.subtitle') }}
        </p>
      </div>
      <div class="flex gap-2">
        <Button
          v-if="activeTab !== 'timetable'"
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
      <TabsList class="mb-6 border-b bg-transparent p-0 h-auto gap-6">
        <TabsTrigger 
          value="subjects" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all"
        >
          <LucideIcon
            name="BookOpen"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.academic.tabs.subjects') }}
        </TabsTrigger>
        <TabsTrigger 
          value="study-groups" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all"
        >
          <LucideIcon
            name="Users2"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.academic.tabs.studyGroups') }}
        </TabsTrigger>
        <TabsTrigger 
          value="timetable" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all"
        >
          <LucideIcon
            name="Calendar"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.academic.tabs.timetable') }}
        </TabsTrigger>
        <TabsTrigger 
          value="years" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all"
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
        <Card v-else>
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
      class: 'h-8 w-8 text-success',
      onClick: (e: MouseEvent) => {
          e.stopPropagation();
          selectedItem.value = row.original;
          dialogs.value.members = true;
      }
    }, () => h(LucideIcon, { name: 'Users', class: 'w-4 h-4' })) : null,
    h(Button, {
      variant: 'ghost',
      size: 'icon',
      class: 'h-8 w-8 text-primary',
      onClick: (e: MouseEvent) => {
          e.stopPropagation();
          handleEdit(row.original);
      }
    }, () => h(LucideIcon, { name: 'Pencil', class: 'w-4 h-4' })),
    h(Button, {
      variant: 'ghost',
      size: 'icon',
      class: 'h-8 w-8 text-destructive',
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
    cell: info => info.getValue() ? h('span', { class: 'px-2 py-1 rounded-full bg-success/10 text-success text-xs font-medium' }, t('common.labels.active')) : t('common.labels.inactive')
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
