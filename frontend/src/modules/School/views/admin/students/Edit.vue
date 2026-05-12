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
          {{ $t('common.actions.edit') }} {{ $t('modules.school.students.labels.studentData') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ $t('modules.school.students.subtitle') }}
        </p>
      </div>
    </div>

    <div
      v-if="fetching"
      class="flex justify-center p-12"
    >
      <LucideIcon
        name="Loader2"
        class="w-8 h-8 animate-spin text-primary"
      />
    </div>
    <StudentForm
      v-else
      :initial-data="student"
      :is-edit="true"
      :loading="saving"
      @submit="handleSubmit"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';
import { StudentService } from '@/modules/School/services/StudentService';
import { LucideIcon } from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import StudentForm from './components/StudentForm.vue';
import { parseResponse } from '@/shared/utils/responseParser';

const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const toast = useToast();
const student = ref<any>(null);
const fetching = ref(true);
const saving = ref(false);

const fetchStudent = async () => {
    fetching.value = true;
    try {
        const response = await StudentService.getStudent(Number(route.params.id));
        student.value = parseResponse(response).data;
    } catch (e) {
        toast.error.fromResponse(e);
        router.push({ name: 'students.index' });
    } finally {
        fetching.value = false;
    }
}

const handleSubmit = async (formData: any) => {
  saving.value = true;
  try {
    await StudentService.updateStudent(Number(route.params.id), formData);
    toast.success.action(t('modules.school.academic.messages.updateSuccess'));
    router.push({ name: 'students.index' });
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
    fetchStudent();
})
</script>
