<template>
  <div class="p-6 max-w-4xl mx-auto text-left">
    <div class="mb-8 flex justify-between items-end">
      <div>
        <router-link
          :to="{ name: 'students.index' }"
          class="text-sm text-primary hover:underline flex items-center gap-1 mb-2"
        >
          <LucideIcon
            name="ArrowLeft"
            class="w-3 h-3"
          /> {{ $t('common.actions.backTo') }} {{ $t('modules.school.students.labels.studentData') }}
        </router-link>
        <h1 class="text-2xl font-bold text-foreground">
          {{ $t('modules.school.students.actions.add') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ $t('modules.school.academic.messages.fillForm') }}
        </p>
      </div>
    </div>

    <StudentForm
      :loading="saving"
      @submit="handleSubmit"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import api from '@/engine/api/client';
import { LucideIcon } from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import StudentForm from './components/StudentForm.vue';

const { t } = useI18n();
const router = useRouter();
const toast = useToast();
const saving = ref(false);

const handleSubmit = async (formData: any) => {
  saving.value = true;
  try {
    await api.post('/manage/school/students', formData);
    toast.success.action(t('modules.school.academic.messages.saveSuccess'));
    router.push({ name: 'students.index' });
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    saving.value = false;
  }
};
</script>
