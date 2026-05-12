<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[600px] h-[80vh] flex flex-col p-0">
      <DialogHeader class="p-6 pb-0">
        <DialogTitle>{{ $t('modules.school.academic.labels.manageGroupMembers', { name: group?.name }) }}</DialogTitle>
        <DialogDescription>{{ $t('modules.school.academic.labels.manageGroupMembersDesc') }}</DialogDescription>
      </DialogHeader>
      
      <div class="p-6 space-y-4 flex-1 flex flex-col min-h-0">
        <!-- Add Student -->
        <div class="flex gap-2">
          <div class="flex-1">
            <Select v-model="selectedStudentId">
              <SelectTrigger><SelectValue :placeholder="$t('modules.school.academic.placeholders.selectStudentToAdd')" /></SelectTrigger>
              <SelectContent>
                <SelectItem 
                  v-for="s in availableStudents" 
                  :key="s.id" 
                  :value="String(s.id)"
                >
                  {{ s.full_name }} ({{ s.nisn || 'No NISN' }})
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <Button
            :disabled="!selectedStudentId || adding"
            @click="handleAddMember"
          >
            <LucideIcon
              v-if="adding"
              name="Loader2"
              class="w-4 h-4 mr-2 animate-spin"
            />
            {{ $t('common.actions.add') }}
          </Button>
        </div>

        <!-- Members List -->
        <div class="border rounded-md flex-1 overflow-auto">
          <table class="w-full text-sm">
            <thead class="bg-muted/50 sticky top-0">
              <tr>
                <th class="p-3 text-left font-medium">
                  {{ $t('modules.school.labels.student') }}
                </th>
                <th class="p-3 text-left font-medium">
                  NISN
                </th>
                <th class="p-3 text-right font-medium">
                  {{ $t('common.actions.title') }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr
                v-if="loading"
                class="text-center"
              >
                <td
                  colspan="3"
                  class="p-8"
                >
                  <LucideIcon
                    name="Loader2"
                    class="w-6 h-6 mx-auto animate-spin text-muted-foreground"
                  />
                </td>
              </tr>
              <tr
                v-else-if="members.length === 0"
                class="text-center"
              >
                <td
                  colspan="3"
                  class="p-8 text-muted-foreground"
                >
                  {{ $t('modules.school.academic.labels.noMembers') }}
                </td>
              </tr>
              <tr
                v-for="m in members"
                :key="m.id"
                class="hover:bg-muted/30"
              >
                <td class="p-3">
                  {{ m.full_name }}
                </td>
                <td class="p-3 text-muted-foreground">
                  {{ m.nisn || '-' }}
                </td>
                <td class="p-3 text-right">
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8 text-destructive"
                    @click="handleRemoveMember(m.id)"
                  >
                    <LucideIcon
                      name="UserMinus"
                      class="w-4 h-4"
                    />
                  </Button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription,
  Button, LucideIcon, Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/shared/components/ui';
import { AcademicService } from '@/modules/School/services/AcademicService';
import { StudentService } from '@/modules/School/services/StudentService';
import { parseResponse } from '@/shared/utils/responseParser';
import { useToast } from '@/shared/composables/useToast';

const { t } = useI18n();
const props = defineProps<{
  open: boolean;
  group: any;
}>();

const emit = defineEmits(['update:open']);

const toast = useToast();
const loading = ref(false);
const adding = ref(false);
const members = ref<any[]>([]);
const availableStudents = ref<any[]>([]);
const selectedStudentId = ref('');

const fetchData = async () => {
    if (!props.group?.id) return;
    loading.value = true;
    try {
        const [membersRes, studentsRes] = await Promise.all([
            AcademicService.getStudyGroupMembers(props.group.id),
            StudentService.getStudents({ per_page: 500 })
        ]);
        
        members.value = parseResponse(membersRes).data;
        const res = parseResponse(studentsRes);
        const allStudents = res.data || [];
        
        // Filter out students who are already members
        availableStudents.value = allStudents.filter((s: any) => 
            !members.value.some((m: any) => m.id === s.id)
        );
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        loading.value = false;
    }
}

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        fetchData();
        selectedStudentId.value = '';
    }
});

const handleAddMember = async () => {
    if (!selectedStudentId.value) return;
    adding.value = true;
    try {
        await AcademicService.addStudyGroupMember(props.group.id, selectedStudentId.value);
        toast.success.action(t('modules.school.academic.messages.studentAddSuccess'));
        fetchData();
        selectedStudentId.value = '';
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        adding.value = false;
    }
}

const handleRemoveMember = async (studentId: number) => {
    try {
        await AcademicService.removeStudyGroupMember(props.group.id, studentId);
        toast.success.action(t('modules.school.academic.messages.studentRemoveSuccess'));
        fetchData();
    } catch (e) {
        toast.error.fromResponse(e);
    }
}
</script>
