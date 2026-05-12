<template>
  <div
    v-if="enrollment"
    class="space-y-6"
  >
    <div class="flex justify-between items-center">
      <div class="flex items-center gap-4">
        <Button
          variant="ghost"
          size="icon"
          @click="router.back()"
        >
          <LucideIcon
            name="ArrowLeft"
            class="w-4 h-4"
          />
        </Button>
        <div>
          <h1 class="text-2xl font-bold text-foreground">
            {{ $t('modules.school.admission.labels.applicantDetail') }}
          </h1>
          <p class="text-sm font-mono text-primary">
            {{ enrollment.registration_number }}
          </p>
        </div>
      </div>
      <div class="flex gap-2">
        <Button
          v-if="enrollment.status !== 'admitted'"
          variant="outline"
          class="text-destructive"
          @click="handleUpdateStatus('rejected')"
        >
          {{ $t('common.actions.reject') }}
        </Button>
        <Button
          v-if="enrollment.status === 'verified'"
          variant="default"
          @click="handleAdmit"
        >
          <LucideIcon
            name="UserCheck"
            class="w-4 h-4 mr-2"
          />
          {{ $t('modules.school.admission.actions.admitAsStudent') }}
        </Button>
        <Button
          v-if="['applied', 'exam'].includes(enrollment.status)"
          variant="secondary"
          @click="handleUpdateStatus('verified')"
        >
          {{ $t('modules.school.admission.actions.verificationComplete') }}
        </Button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Info Utama -->
      <Card class="lg:col-span-2">
        <CardHeader>
          <CardTitle>{{ $t('modules.school.admission.labels.personalData') }}</CardTitle>
        </CardHeader>
        <CardContent class="grid grid-cols-2 gap-y-4 gap-x-8">
          <div class="space-y-1">
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide">
              {{ $t('modules.school.admission.labels.full_name') }}
            </p>
            <p class="font-medium">
              {{ enrollment.full_name }}
            </p>
          </div>
          <div class="space-y-1">
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide">
              {{ $t('modules.school.admission.labels.nisn') }}
            </p>
            <p class="font-medium">
              {{ enrollment.nisn || '-' }}
            </p>
          </div>
          <div class="space-y-1">
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide">
              {{ $t('common.labels.placeDateOfBirth') }}
            </p>
            <p class="font-medium">
              {{ enrollment.place_of_birth }}, {{ formatDate(enrollment.date_of_birth) }}
            </p>
          </div>
          <div class="space-y-1">
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide">
              {{ $t('common.labels.gender') }}
            </p>
            <p class="font-medium">
              {{ enrollment.gender === 'Laki-laki' ? $t('common.genders.male') : $t('common.genders.female') }}
            </p>
          </div>
          <div class="space-y-1">
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide">
              {{ $t('common.labels.contact') }}
            </p>
            <p class="font-medium">
              {{ enrollment.phone }} / {{ enrollment.email || '-' }}
            </p>
          </div>
          <div class="space-y-1">
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide">
              {{ $t('modules.school.admission.labels.previousSchool') }}
            </p>
            <p class="font-medium">
              {{ enrollment.previous_school }}
            </p>
          </div>
        </CardContent>
      </Card>

      <!-- Status & Timeline -->
      <Card>
        <CardHeader>
          <CardTitle>{{ $t('modules.school.admission.labels.registrationStatus') }}</CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="flex items-center gap-3 p-3 rounded-lg border bg-muted/20">
            <div :class="`w-3 h-3 rounded-full ${getStatusColor(enrollment.status)}`" />
            <span class="font-bold capitalize">{{ getStatusLabel(enrollment.status) }}</span>
          </div>
           
          <div class="space-y-3 pt-4">
            <p class="text-sm font-bold">
              {{ $t('modules.school.admission.labels.supportingDocuments') }}:
            </p>
            <div
              v-if="enrollment.documents.length === 0"
              class="text-sm text-muted-foreground italic"
            >
              {{ $t('modules.school.admission.placeholders.noDocuments') }}
            </div>
            <div
              v-for="doc in enrollment.documents"
              :key="doc.id"
              class="flex justify-between items-center p-2 border rounded text-sm"
            >
              <span>{{ doc.name }}</span>
              <span :class="`text-xs px-2 py-0.5 rounded-full ${doc.status === 'verified' ? 'bg-success/10 text-success' : 'bg-muted text-muted-foreground'}`">
                {{ doc.status === 'verified' ? $t('modules.school.admission.labels.verified') : doc.status }}
              </span>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import {
  Card, CardHeader, CardTitle, CardContent, Button, LucideIcon
} from '@/shared/components/ui';
import { AdmissionService } from '@/modules/School/services/AdmissionService';
import { parseResponse } from '@/shared/utils/responseParser';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const toast = useToast();
const { confirm } = useConfirm();
const enrollment = ref<any>(null);
const loading = ref(false);

const fetchEnrollment = async () => {
  loading.value = true;
  try {
    const response = await AdmissionService.getEnrollment(String(route.params.id));
    const res = parseResponse(response) as any;
    enrollment.value = res.data;
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};

const handleUpdateStatus = async (status: string) => {
  try {
    await AdmissionService.updateEnrollmentStatus(enrollment.value.id, status);
    toast.success.action(t('modules.school.academic.messages.updateSuccess'));
    fetchEnrollment();
  } catch (e) {
    toast.error.fromResponse(e);
  }
};

const handleAdmit = async () => {
  if (await confirm({ 
    title: t('modules.school.admission.actions.admitAsStudent'), 
    description: t('modules.school.admission.messages.admitConfirmation'), 
    variant: 'success' 
  })) {
    try {
      await AdmissionService.admitEnrollment(enrollment.value.id);
      toast.success.action(t('modules.school.academic.messages.addSuccess'));
      fetchEnrollment();
    } catch (e) {
      toast.error.fromResponse(e);
    }
  }
};

const formatDate = (date: any) => date ? new Date(date).toLocaleDateString(t('common.language') === 'id' ? 'id-ID' : 'en-US', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    applied: t('modules.school.admission.labels.new'),
    verified: t('modules.school.admission.labels.verified'),
    exam: t('modules.school.admission.labels.selection'),
    admitted: t('modules.school.admission.labels.admitted'),
    rejected: t('modules.school.admission.labels.rejected'),
    draft: 'Draft'
  };
  return labels[status] || status;
};

const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    applied: 'bg-blue-500',
    verified: 'bg-green-500',
    exam: 'bg-yellow-500',
    admitted: 'bg-purple-500',
    rejected: 'bg-red-500',
    draft: 'bg-gray-500'
  };
  return colors[status] || 'bg-gray-500';
};

onMounted(fetchEnrollment);
</script>
