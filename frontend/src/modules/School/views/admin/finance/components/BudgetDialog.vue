<template>
  <Dialog v-model:open="open">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ $t('features.school.finance.actions.addBudget') }} (RAPBS)</DialogTitle>
        <DialogDescription>
          {{ $t('features.school.finance.messages.budgetDescription') }}
        </DialogDescription>
      </DialogHeader>

      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label>{{ $t('features.school.academic.tabs.years') }}</Label>
          <Select
            v-model="form.academic_year_id"
            required
          >
            <SelectTrigger>
              <SelectValue :placeholder="$t('features.school.academic.placeholders.selectYear')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="year in academicYears"
                :key="year.id"
                :value="year.id.toString()"
              >
                {{ year.year }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="space-y-2">
          <Label>{{ $t('features.school.finance.labels.category') }}</Label>
          <Select
            v-model="form.category"
            required
          >
            <SelectTrigger>
              <SelectValue :placeholder="$t('features.school.finance.placeholders.selectCategory')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="Gaji & Tunjangan">
                {{ $t('features.school.finance.categories.salary') }}
              </SelectItem>
              <SelectItem value="Sarana & Prasarana">
                {{ $t('features.school.finance.categories.sarpras') }}
              </SelectItem>
              <SelectItem value="Operasional Pendidikan">
                {{ $t('features.school.finance.categories.ops') }}
              </SelectItem>
              <SelectItem value="Kesiswaan">
                {{ $t('features.school.finance.categories.students') }}
              </SelectItem>
              <SelectItem value="Pengembangan SDM">
                {{ $t('features.school.finance.categories.hrd') }}
              </SelectItem>
              <SelectItem value="Lainnya">
                {{ $t('features.school.finance.categories.others') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="space-y-2">
          <Label>{{ $t('features.school.finance.labels.plannedAmount') }}</Label>
          <Input 
            v-model="form.planned_amount" 
            type="number" 
            placeholder="0" 
            required 
          />
        </div>

        <div class="space-y-2">
          <Label>{{ $t('features.school.finance.labels.notes') }}</Label>
          <Textarea
            v-model="form.notes"
            :placeholder="$t('features.school.finance.placeholders.notesHint')"
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
            {{ $t('common.actions.save') }} {{ $t('features.school.finance.tabs.budgeting') }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Button, Input, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
  Textarea, LucideIcon
} from '@/components/ui';
import { AcademicService } from '@/modules/School/services/AcademicService';
import { parseResponse } from '@/utils/responseParser';

defineProps<{
  loading?: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);
const open = ref(false);

const form = ref({
  academic_year_id: '',
  category: '',
  planned_amount: '',
  notes: ''
});

const academicYears = ref<any[]>([]);

const fetchAcademicYears = async () => {
  try {
    const response = await AcademicService.getAcademicYears();
    academicYears.value = parseResponse(response).data || [];
    if (academicYears.value.length > 0) {
        const active = academicYears.value.find(y => y.is_active);
        if (active) form.value.academic_year_id = active.id.toString();
    }
  } catch (e) {
    console.error(e);
  }
};

const handleSubmit = () => {
  emit('submit', { ...form.value });
};

onMounted(() => {
  fetchAcademicYears();
});
</script>
