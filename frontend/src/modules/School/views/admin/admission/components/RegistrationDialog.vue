<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[600px]">
      <DialogHeader>
        <DialogTitle>{{ $t('features.school.admission.actions.manualRegister') }}</DialogTitle>
      </DialogHeader>
      
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2 col-span-2">
            <Label>{{ $t('features.school.admission.labels.full_name') }} <span class="text-destructive">*</span></Label>
            <Input
              v-model="form.full_name"
              required
              :placeholder="$t('features.school.admission.placeholders.fullNameHint')"
            />
          </div>
          <div class="space-y-2">
            <Label>{{ $t('features.school.admission.labels.nisn') }}</Label>
            <Input
              v-model="form.nisn"
              :placeholder="$t('features.school.admission.placeholders.nisnHint')"
            />
          </div>
          <div class="space-y-2">
            <Label>{{ $t('common.labels.gender') }}</Label>
            <Select v-model="form.gender">
              <SelectTrigger><SelectValue :placeholder="$t('features.school.admission.placeholders.selectGender')" /></SelectTrigger>
              <SelectContent>
                <SelectItem value="Laki-laki">
                  {{ $t('common.genders.male') }}
                </SelectItem>
                <SelectItem value="Perempuan">
                  {{ $t('common.genders.female') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label>{{ $t('common.labels.placeOfBirth') }}</Label>
            <Input v-model="form.place_of_birth" />
          </div>
          <div class="space-y-2">
            <Label>{{ $t('common.labels.dateOfBirth') }}</Label>
            <Input
              v-model="form.date_of_birth"
              type="date"
            />
          </div>
          <div class="space-y-2">
            <Label>{{ $t('common.labels.phone') }}</Label>
            <Input
              v-model="form.phone"
              placeholder="08..."
            />
          </div>
          <div class="space-y-2">
            <Label>{{ $t('common.labels.email') }}</Label>
            <Input
              v-model="form.email"
              type="email"
              :placeholder="$t('features.school.admission.placeholders.emailHint')"
            />
          </div>
          <div class="space-y-2 col-span-2">
            <Label>{{ $t('features.school.admission.labels.previousSchool') }}</Label>
            <Input
              v-model="form.previous_school"
              :placeholder="$t('features.school.admission.placeholders.previousSchoolHint')"
            />
          </div>
          <div class="space-y-2 col-span-2">
            <Label>{{ $t('features.school.admission.labels.targetAcademicYear') }}</Label>
            <Select
              v-model="form.academic_year_id"
              required
            >
              <SelectTrigger><SelectValue :placeholder="$t('features.school.admission.placeholders.selectYear')" /></SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="year in academicYears"
                  :key="year.id"
                  :value="String(year.id)"
                >
                  {{ (year as any).year }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <DialogFooter>
          <Button
            type="button"
            variant="outline"
            @click="$emit('update:open', false)"
          >
            {{ $t('common.actions.cancel') }}
          </Button>
          <Button
            type="submit"
            :disabled="loading"
          >
            <LucideIcon
              v-if="loading"
              name="Loader2"
              class="w-4 h-4 mr-2 animate-spin"
            />
            {{ $t('common.actions.register') }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
  Button, Label, Input, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, LucideIcon
} from '@/components/ui';
import { AdmissionService } from '@/modules/School/services/AdmissionService';
import { AcademicService } from '@/modules/School/services/AcademicService';
import { parseResponse } from '@/utils/responseParser';
import { useToast } from '@/composables/useToast';

const { t } = useI18n();
const props = defineProps<{ open: boolean }>();
const emit = defineEmits(['update:open', 'save']);
const toast = useToast();
const loading = ref(false);
const academicYears = ref<any[]>([]);

const form = ref({
  school_id: 1,
  school_level_id: 1,
  academic_year_id: undefined as string | undefined,
  full_name: '',
  gender: '',
  place_of_birth: '',
  date_of_birth: '',
  nisn: '',
  phone: '',
  email: '',
  previous_school: ''
});

const fetchData = async () => {
  try {
    const response = await AcademicService.getAcademicYears();
    academicYears.value = parseResponse(response).data || [];
    if (academicYears.value.length > 0) {
      form.value.academic_year_id = String((academicYears.value[0] as any).id);
    }
  } catch (e) {
    console.error(e);
  }
};

watch(() => props.open, (val) => {
  if (val) fetchData();
});

const handleSubmit = async () => {
  loading.value = true;
  try {
    await AdmissionService.storeEnrollment(form.value);
    toast.success.action(t('features.school.academic.messages.addSuccess'));
    emit('save');
    emit('update:open', false);
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};
</script>
