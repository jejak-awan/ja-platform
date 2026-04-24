<template>
  <Card v-if="!isCompleted">
    <CardHeader>
      <div class="flex items-center justify-between">
        <div>
          <CardTitle>{{ $t('features.school.dashboard.setup.title') }}</CardTitle>
          <CardDescription>{{ $t('features.school.dashboard.setup.subtitle') }}</CardDescription>
        </div>
        <div class="text-right">
          <span class="text-2xl font-bold text-primary">{{ Math.round(progress) }}%</span>
          <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">
            {{ $t('features.school.dashboard.setup.completed') }}
          </p>
        </div>
      </div>
      <div class="w-full bg-muted h-2 rounded-full mt-4 overflow-hidden">
        <div 
          class="bg-primary h-full transition-all duration-500 ease-out" 
          :style="{ width: `${progress}%` }"
        />
      </div>
    </CardHeader>
    <CardContent>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div 
          v-for="(step, key) in steps" 
          :key="key"
          class="flex items-center p-3 rounded-lg border bg-card transition-colors hover:bg-muted/30"
          :class="{ 'opacity-60 grayscale-[0.5]': step.done }"
        >
          <div 
            class="w-8 h-8 rounded-full flex items-center justify-center mr-3 shrink-0"
            :class="step.done ? 'bg-success/10 text-success' : 'bg-primary/10 text-primary'"
          >
            <LucideIcon
              :name="step.done ? 'CheckCircle2' : 'Circle'"
              class="w-5 h-5"
            />
          </div>
          <div class="min-w-0">
            <p class="text-sm font-semibold truncate">
              {{ step.label }}
            </p>
            <p class="text-xs text-muted-foreground truncate">
              {{ step.desc }}
            </p>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, LucideIcon } from '@/components/ui';
import api from '@/services/api';

const { t } = useI18n();


const rawStatus = ref<Record<string, boolean>>({});
const progress = ref(0);
const isCompleted = ref(true);

const labels: Record<string, { label: string, desc: string }> = {
  school_profile: { label: t('features.school.dashboard.setup.steps.school_profile.label'), desc: t('features.school.dashboard.setup.steps.school_profile.desc') },
  school_levels: { label: t('features.school.dashboard.setup.steps.school_levels.label'), desc: t('features.school.dashboard.setup.steps.school_levels.desc') },
  academic_year: { label: t('features.school.dashboard.setup.steps.academic_year.label'), desc: t('features.school.dashboard.setup.steps.academic_year.desc') },
  semesters: { label: t('features.school.dashboard.setup.steps.semesters.label'), desc: t('features.school.dashboard.setup.steps.semesters.desc') },
  departments: { label: t('features.school.dashboard.setup.steps.departments.label'), desc: t('features.school.dashboard.setup.steps.departments.desc') },
  study_groups: { label: t('features.school.dashboard.setup.steps.study_groups.label'), desc: t('features.school.dashboard.setup.steps.study_groups.desc') },
};

const steps = computed(() => {
  return Object.keys(labels).map(key => ({
    key,
    ...labels[key],
    done: rawStatus.value[key] || false
  }));
});

const fetchStatus = async () => {
    try {
        const response = await api.get('/admin/school/setup-status');
        const payload = response.data as {
            success?: boolean;
            steps?: Record<string, boolean>;
            progress?: number;
            is_completed?: boolean;
        };

        if (payload.success === false) return;
        rawStatus.value = payload.steps || {};
        progress.value = payload.progress || 0;
        isCompleted.value = payload.is_completed || false;
    } catch (e) {
        console.error(e);
    }
}

onMounted(fetchStatus);
</script>
