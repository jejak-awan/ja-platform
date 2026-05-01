<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="sm:max-w-[600px]">
      <DialogHeader>
        <DialogTitle>{{ $t('admission.actions.manualRegister') }}</DialogTitle>
      </DialogHeader>

      <form @submit.prevent="save" class="space-y-6 py-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Tahun Ajaran -->
          <div class="space-y-2 col-span-2 md:col-span-1">
            <Label>{{ $t('admission.labels.academicYear') }} <span class="text-destructive">*</span></Label>
            <Select v-model="form.academic_year_id" required>
              <SelectTrigger class="h-11 rounded-xl">
                <SelectValue :placeholder="$t('admission.placeholders.selectYear')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="year in academicYears" :key="year.id" :value="String(year.id)">
                  {{ year.year }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Jenjang -->
          <div class="space-y-2 col-span-2 md:col-span-1">
            <Label>{{ $t('admission.labels.level') }} <span class="text-destructive">*</span></Label>
            <Select v-model="form.school_level_id" required>
              <SelectTrigger class="h-11 rounded-xl">
                <SelectValue :placeholder="$t('admission.placeholders.selectLevel')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="level in schoolLevels" :key="level.id" :value="String(level.id)">
                  {{ level.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Nama Lengkap -->
          <div class="space-y-2 col-span-2">
            <Label>{{ $t('admission.labels.full_name') }} <span class="text-destructive">*</span></Label>
            <Input
              v-model="form.full_name"
              :placeholder="$t('admission.placeholders.full_name')"
              required
              class="rounded-xl h-11"
            />
          </div>

          <!-- NISN -->
          <div class="space-y-2">
            <Label>{{ $t('admission.labels.nisn') }}</Label>
            <Input
              v-model="form.nisn"
              :placeholder="$t('admission.placeholders.nisnHint')"
              class="rounded-xl h-11"
            />
          </div>

          <!-- Jenis Kelamin -->
          <div class="space-y-2">
            <Label>{{ $t('common.labels.gender') }} <span class="text-destructive">*</span></Label>
            <Select v-model="form.gender" required>
              <SelectTrigger class="h-11 rounded-xl">
                <SelectValue :placeholder="$t('admission.placeholders.selectGender')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="male">{{ $t('common.genders.male') }}</SelectItem>
                <SelectItem value="female">{{ $t('common.genders.female') }}</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <DialogFooter class="gap-2 sm:gap-0">
          <Button
            type="button"
            variant="ghost"
            class="rounded-xl font-bold h-11"
            :disabled="loading"
            @click="$emit('update:open', false)"
          >
            {{ $t('common.actions.cancel') }}
          </Button>
          <Button
            type="submit"
            :disabled="loading"
            class="rounded-xl font-bold h-11 px-8 shadow-lg shadow-primary/20 transition-all active:scale-95"
          >
            <LoaderCircle
              v-if="loading"
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
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
  Button, Label, Input, Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/components/ui';
import { AdmissionService } from '@/modules/School/services/AdmissionService';
import { AcademicService } from '@/modules/School/services/AcademicService';
import { InstitutionService } from '@/modules/School/services/InstitutionService';
import { parseResponse } from '@/utils/responseParser';
import { useToast } from '@/composables/useToast';

const props = defineProps<{ open: boolean }>();
const emits = defineEmits(['update:open', 'save']);

const { t } = useI18n();
const toast = useToast();
const loading = ref(false);
const academicYears = ref<any[]>([]);
const schoolLevels = ref<any[]>([]);

const form = ref({
  full_name: '',
  gender: '',
  nisn: '',
  academic_year_id: '',
  school_id: 1,
  school_level_id: ''
});

const fetchData = async () => {
  try {
    const [yearsRes, levelsRes] = await Promise.all([
      AcademicService.getAcademicYears(),
      InstitutionService.getLevels()
    ]);
    academicYears.value = parseResponse(yearsRes).data;
    schoolLevels.value = parseResponse(levelsRes).data;

    // Auto select first level if available
    if (schoolLevels.value.length > 0) {
      form.value.school_level_id = String(schoolLevels.value[0].id);
    }
  } catch (e) {
    console.error('Failed to fetch dialog data', e);
  }
};

watch(() => props.open, (val) => {
  if (val) fetchData();
});

onMounted(() => {
  if (props.open) fetchData();
});

const save = async () => {
  loading.value = true;
  try {
    await AdmissionService.storeEnrollment(form.value);
    toast.success.action(t('common.messages.saveSuccess'));
    emits('save');
    emits('update:open', false);
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};
</script>
