<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('common.actions.edit') + ' ' + $t('features.school.academic.labels.group') : $t('common.actions.add') + ' ' + $t('features.school.academic.labels.group') }}</DialogTitle>
      </DialogHeader>
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label for="name">{{ $t('features.school.academic.labels.groupName') }} <span class="text-destructive">*</span></Label>
          <Input
            id="name"
            v-model="form.name"
            :placeholder="$t('features.school.units.form.namePlaceholder')"
            required
          />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>{{ $t('common.labels.institutionStatus') }} <span class="text-destructive">*</span></Label>
            <Select
              v-model="form.school_unit_id"
              required
            >
              <SelectTrigger><SelectValue :placeholder="$t('features.school.academic.placeholders.selectLevel')" /></SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="l in levels"
                  :key="l.id"
                  :value="String(l.id)"
                >
                  {{ l.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label>{{ $t('features.school.academic.tabs.years') }} <span class="text-destructive">*</span></Label>
            <Select
              v-model="form.academic_year_id"
              required
            >
              <SelectTrigger><SelectValue :placeholder="$t('features.school.academic.placeholders.selectYear')" /></SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="y in years"
                  :key="y.id"
                  :value="String(y.id)"
                >
                  {{ y.year }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
        <div class="space-y-2">
          <Label>{{ $t('features.school.academic.labels.homeroomTeacher') }}</Label>
          <Select v-model="form.homeroom_teacher_id">
            <SelectTrigger><SelectValue :placeholder="$t('features.school.academic.placeholders.selectTeacher')" /></SelectTrigger>
            <SelectContent>
              <SelectItem value="none">
                {{ $t('common.labels.none') }}
              </SelectItem>
              <SelectItem
                v-for="s in staff"
                :key="s.id"
                :value="String(s.id)"
              >
                {{ s.full_name }}
              </SelectItem>
            </SelectContent>
          </Select>
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
import { ref, watch, onMounted } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
  Button, Label, Input, LucideIcon, Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/components/ui';
import { InstitutionService } from '@/modules/School/services/InstitutionService';
import { AcademicService } from '@/modules/School/services/AcademicService';
import { HRService } from '@/modules/School/services/HRService';
import { parseResponse } from '@/utils/responseParser';

const props = defineProps<{
  open: boolean;
  isEdit?: boolean;
  initialData?: any;
  loading?: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

const levels = ref<any[]>([]);
const years = ref<any[]>([]);
const staff = ref<any[]>([]);

const form = ref({
  name: '',
  school_unit_id: undefined as string | undefined,
  academic_year_id: undefined as string | undefined,
  homeroom_teacher_id: undefined as string | undefined,
});

watch(() => props.initialData, (val) => {
  if (val) {
      form.value = { 
          name: val.name,
          school_unit_id: val.school_unit_id ? String(val.school_unit_id) : undefined,
          academic_year_id: val.academic_year_id ? String(val.academic_year_id) : undefined,
          homeroom_teacher_id: val.homeroom_teacher_id ? String(val.homeroom_teacher_id) : 'none',
      };
  }
  else {
      form.value = { name: '', school_unit_id: undefined, academic_year_id: undefined, homeroom_teacher_id: 'none' };
  }
}, { immediate: true });

const fetchMetadata = async () => {
    try {
        const [lRes, yRes, sRes] = await Promise.all([
            InstitutionService.getUnits(),
            AcademicService.getAcademicYears(),
            HRService.getStaff({ per_page: 100, ptk_type: 'Guru Mapel' })
        ]);
        levels.value = parseResponse(lRes).data;
        years.value = parseResponse(yRes).data;
        staff.value = parseResponse(sRes).data;
    } catch (e) {
        console.error(e);
    }
}

const handleSubmit = () => {
  const data = { ...form.value };
  if (data.homeroom_teacher_id === 'none') data.homeroom_teacher_id = undefined;
  emit('submit', { ...data, school_id: 1 });
};

onMounted(fetchMetadata);
</script>
