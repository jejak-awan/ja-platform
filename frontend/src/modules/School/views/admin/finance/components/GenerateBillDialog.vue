<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>{{ $t('features.school.finance.actions.generateBills') }}</DialogTitle>
      </DialogHeader>
      
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label>{{ $t('features.school.finance.tabs.feeTypes') }}</Label>
          <Select
            v-model="form.fee_type_id"
            required
          >
            <SelectTrigger><SelectValue :placeholder="$t('features.school.finance.placeholders.selectFeeType')" /></SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="type in feeTypes"
                :key="type.id"
                :value="String(type.id)"
              >
                {{ type.name }} ({{ formatCurrency(type.amount) }})
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="space-y-2">
          <Label>{{ $t('features.school.academic.tabs.groups') }} (Target)</Label>
          <Select
            v-model="form.study_group_id"
            required
          >
            <SelectTrigger><SelectValue :placeholder="$t('features.school.finance.placeholders.selectStudyGroup')" /></SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="group in studyGroups"
                :key="group.id"
                :value="String(group.id)"
              >
                {{ group.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>{{ $t('features.school.academic.tabs.years') }}</Label>
            <Select
              v-model="form.academic_year_id"
              required
            >
              <SelectTrigger><SelectValue :placeholder="$t('features.school.academic.placeholders.selectYear')" /></SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="year in academicYears"
                  :key="year.id"
                  :value="String(year.id)"
                >
                  {{ year.year }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label>{{ $t('common.labels.period') }} ({{ $t('common.labels.optional') }})</Label>
            <Select v-model="form.month">
              <SelectTrigger><SelectValue :placeholder="$t('common.labels.date')" /></SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="(name, key) in translatedMonths"
                  :key="key"
                  :value="String(Object.keys(translatedMonths).indexOf(key) + 1)"
                >
                  {{ name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <div class="space-y-2">
          <Label>{{ $t('features.school.finance.labels.period') }}</Label>
          <Input
            v-model="form.due_date"
            type="date"
          />
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
            {{ $t('common.actions.generate') }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
  Button, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, LucideIcon, Input
} from '@/components/ui';
import { FinanceService } from '@/modules/School/services/FinanceService';
import { AcademicService } from '@/modules/School/services/AcademicService';
import { parseResponse } from '@/utils/responseParser';
import { useToast } from '@/composables/useToast';
import type { FeeType } from '@/types';

const { t } = useI18n();
const props = defineProps<{
  open: boolean;
}>();

const emit = defineEmits(['update:open', 'save']);
const toast = useToast();
const loading = ref(false);

const feeTypes = ref<FeeType[]>([]);
const studyGroups = ref<any[]>([]);
const academicYears = ref<any[]>([]);

const translatedMonths = computed(() => ({
  january: t('common.months.january'),
  february: t('common.months.february'),
  march: t('common.months.march'),
  april: t('common.months.april'),
  may: t('common.months.may'),
  june: t('common.months.june'),
  july: t('common.months.july'),
  august: t('common.months.august'),
  september: t('common.months.september'),
  october: t('common.months.october'),
  november: t('common.months.november'),
  december: t('common.months.december')
}));

const form = ref({
  school_id: 1,
  school_level_id: 1,
  fee_type_id: undefined as string | undefined,
  study_group_id: undefined as string | undefined,
  academic_year_id: undefined as string | undefined,
  month: undefined as string | undefined,
  due_date: ''
});

const fetchData = async () => {
  try {
    const [fees, groups, years] = await Promise.all([
      FinanceService.getFeeTypes(),
      AcademicService.getStudyGroups(),
      AcademicService.getAcademicYears()
    ]);
    feeTypes.value = (parseResponse(fees).data || []) as FeeType[];
    studyGroups.value = parseResponse(groups).data || [];
    academicYears.value = parseResponse(years).data || [];
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
    await FinanceService.generateBills(form.value);
    toast.success.action(t('features.school.finance.messages.generateBillsSuccess'));
    emit('save');
    emit('update:open', false);
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};

const formatCurrency = (val: any) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(val);
};
</script>
