<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center bg-card p-4 rounded-xl border border-border/50 text-left">
      <div class="flex gap-2">
        <Button
          variant="outline"
          size="sm"
          class="h-9"
          @click="dialogs.applications = true"
        >
          <LucideIcon
            name="Users"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.logistics.career.actions.viewApplicants') }}
        </Button>
      </div>
      <Button
        size="sm"
        class="h-9 shadow-lg shadow-primary/20"
        @click="dialogs.vacancy = true"
      >
        <LucideIcon
          name="PlusCircle"
          class="w-4 h-4 mr-2"
        />
        {{ $t('features.school.logistics.career.actions.postVacancy') }}
      </Button>
    </div>

    <!-- Vacancies Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-left">
      <Card
        v-for="v in vacancies"
        :key="v.id"
        class="border-border/50 hover:shadow-md transition-all"
      >
        <CardHeader class="pb-3">
          <div class="flex justify-between items-start">
            <div>
              <CardTitle class="text-base font-bold">
                {{ v.position }}
              </CardTitle>
              <CardDescription class="text-xs font-medium text-primary">
                {{ v.company_name }}
              </CardDescription>
            </div>
            <Badge
              :variant="v.status === 'open' ? 'outline' : 'secondary'"
              class="text-[10px]"
            >
              {{ v.status }}
            </Badge>
          </div>
        </CardHeader>
        <CardContent class="pb-3 text-xs text-muted-foreground line-clamp-3">
          {{ v.description }}
        </CardContent>
        <CardFooter class="pt-3 border-t flex justify-between items-center">
          <div class="flex items-center gap-1 text-[10px]">
            <LucideIcon
              name="Calendar"
              class="w-3 h-3"
            />
            D/L: {{ v.deadline ? new Date(v.deadline).toLocaleDateString() : 'N/A' }}
          </div>
          <div class="text-[10px] font-bold">
            {{ v.applications_count }} {{ $t('features.school.logistics.career.labels.applicants') }}
          </div>
        </CardFooter>
      </Card>
    </div>

    <!-- Dialogs -->
    <VacancyDialog
      v-model:open="dialogs.vacancy"
      @save="fetchVacancies"
    />
    <ApplicationsDialog
      v-model:open="dialogs.applications"
      :vacancies="vacancies"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { LogisticsService } from '@/modules/School/services/LogisticsService';
import {
  Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter,
  Button, LucideIcon, Badge
} from '@/components/ui';
import { useToast } from '@/composables/useToast';
import { parseResponse } from '@/utils/responseParser';

// Components
import VacancyDialog from './components/VacancyDialog.vue';
import ApplicationsDialog from './components/ApplicationsDialog.vue';

const toast = useToast();
const vacancies = ref<any[]>([]);
const dialogs = ref({ vacancy: false, applications: false });

const fetchVacancies = async () => {
   try {
      const response = await LogisticsService.getVacancies();
      vacancies.value = parseResponse(response).data;
   } catch (e) {
      toast.error.fromResponse(e);
   }
}

onMounted(fetchVacancies);
</script>
