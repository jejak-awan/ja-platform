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
          /> {{ $t('common.actions.backTo') }} {{ $t('features.school.students.labels.studentData') }}
        </router-link>
        <h1 class="text-2xl font-bold text-foreground">
          {{ $t('features.school.students.actions.add') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ $t('features.school.academic.messages.fillForm') }}
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
import api from '@/services/api';
import { LucideIcon } from '@/components/ui';
import { useToast } from '@/composables/useToast';
import StudentForm from './components/StudentForm.vue';

const { t } = useI18n();
const router = useRouter();
const toast = useToast();
const saving = ref(false);

const handleSubmit = async (formData: any) => {
  saving.value = true;
  try {
    await api.post('/admin/students', formData);
    toast.success.action(t('features.school.academic.messages.saveSuccess'));
    router.push({ name: 'students.index' });
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    saving.value = false;
  }
};
</script>
