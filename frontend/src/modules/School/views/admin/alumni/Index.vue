<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    Card, CardContent,
    Tabs, TabsList, TabsTrigger, TabsContent,
    Button, Badge, LucideIcon
} from '@/shared/components/ui';
import axios from 'axios';
import dayjs from 'dayjs';
import { useToast } from '@/shared/composables/useToast';

const { t } = useI18n();
const toast = useToast();
const loading = ref(true);
const activeTab = ref('list');

// Data
const alumni = ref<any[]>([]);
const tracerStudies = ref<any[]>([]);

const fetchData = async () => {
    loading.value = true;
    try {
        const [aRes, tRes] = await Promise.all([
            axios.get('/api/v1/admin/extensions/alumni'),
            axios.get('/api/v1/admin/extensions/tracer-studies')
        ]);
        alumni.value = aRes.data.data || [];
        tracerStudies.value = tRes.data.data || [];
    } catch {
        toast.error.default('Failed to fetch alumni data');
    } finally {
        loading.value = false;
    }
};

const getEmploymentBadge = (status: string) => {
    const variants: Record<string, string> = {
        'Employed': 'default',
        'Self-employed': 'info',
        'Unemployed': 'destructive',
        'Studying': 'secondary',
        'Bekerja': 'default',
        'Wirausaha': 'info',
        'Belum Bekerja': 'destructive',
        'Kuliah': 'secondary'
    };
    return (variants[status] || 'default') as any;
};

onMounted(fetchData);

// Stats
const stats = computed(() => {
    return {
        totalAlumni: alumni.value.length,
        totalTracer: tracerStudies.value.length,
        employedRate: tracerStudies.value.length > 0 
            ? Math.round(tracerStudies.value.filter(t => ['Employed', 'Self-employed', 'Bekerja', 'Wirausaha'].includes(t.employment_status)).length / tracerStudies.value.length * 100)
            : 0
    };
});

</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black tracking-tight text-foreground/90">
          {{ t('modules.school.extensions.labels.alumniDatabase') }}
        </h1>
        <p class="text-muted-foreground">
          {{ t('modules.school.extensions.subtitle') }}
        </p>
      </div>
      <div class="flex gap-2">
        <Button
          variant="outline"
          @click="fetchData"
        >
          <LucideIcon
            name="refresh-cw"
            :class="{ 'animate-spin': loading }"
            class="w-4 h-4 mr-2"
          />
          {{ t('modules.school.osis.actions.reload') }}
        </Button>
        <Button class="rounded-xl shadow-lg shadow-primary/20 bg-indigo-600 hover:bg-indigo-700">
          <LucideIcon
            name="user-plus"
            class="w-4 h-4 mr-2"
          />
          {{ t('modules.school.extensions.labels.addAlumni') }}
        </Button>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <Card
        v-for="(stat, idx) in [
          { key: 'totalAlumni', value: stats.totalAlumni, color: 'text-indigo-600', icon: 'graduation-cap' },
          { key: 'tracerResponses', value: stats.totalTracer, color: 'text-emerald-600', icon: 'clipboard-list' },
          { key: 'employmentRate', value: stats.employedRate + '%', color: 'text-amber-600', icon: 'trending-up' }
        ]"
        :key="idx"
        class="border-none bg-white/50 dark:bg-slate-900/50 backdrop-blur-xl"
      >
        <CardContent class="p-6 flex items-center gap-4">
          <div :class="['p-3 rounded-2xl bg-white dark:bg-slate-800 shadow-sm', stat.color]">
            <LucideIcon
              :name="stat.icon"
              class="w-6 h-6"
            />
          </div>
          <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">
              {{ t('modules.school.extensions.labels.' + stat.key) }}
            </p>
            <p class="text-2xl font-black text-foreground">
              {{ stat.value }}
            </p>
          </div>
        </CardContent>
      </Card>
    </div>

    <Tabs
      v-model="activeTab"
      class="w-full"
    >
      <TabsList class="bg-muted/50 p-1 rounded-2xl mb-6">
        <TabsTrigger
          value="list"
          class="rounded-xl px-8"
        >
          {{ t('modules.school.extensions.labels.alumniList') }}
        </TabsTrigger>
        <TabsTrigger
          value="tracer"
          class="rounded-xl px-8"
        >
          {{ t('modules.school.extensions.labels.tracerStudy') }}
        </TabsTrigger>
        <TabsTrigger
          value="analytics"
          class="rounded-xl px-8"
        >
          {{ t('modules.school.extensions.labels.analytics') }}
        </TabsTrigger>
      </TabsList>

      <!-- Alumni List -->
      <TabsContent value="list">
        <Card class="border-none bg-white/60 dark:bg-slate-900/60 backdrop-blur-md rounded-[2.5rem] overflow-hidden">
          <CardContent class="p-0">
            <table class="w-full text-left">
              <thead>
                <tr class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/40 border-b">
                  <th class="p-6">
                    {{ t('modules.school.extensions.labels.graduateName') }}
                  </th>
                  <th class="p-6">
                    {{ t('modules.school.extensions.labels.gradYear') }}
                  </th>
                  <th class="p-6">
                    {{ t('modules.school.extensions.labels.currentActivity') }}
                  </th>
                  <th class="p-6 text-right">
                    {{ t('common.actions.title') }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="alumnus in alumni"
                  :key="alumnus.id"
                  class="border-b last:border-0 hover:bg-accent/5 transition-colors group"
                >
                  <td class="p-6">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-indigo-500/10 flex items-center justify-center font-bold text-indigo-600">
                        {{ alumnus.student?.name?.charAt(0) }}
                      </div>
                      <div>
                        <p class="font-bold text-foreground group-hover:text-indigo-600 transition-colors">
                          {{ alumnus.student?.name }}
                        </p>
                        <p class="text-[10px] text-muted-foreground uppercase tracking-wider">
                          {{ alumnus.student?.nis }}
                        </p>
                      </div>
                    </div>
                  </td>
                  <td class="p-6">
                    <Badge
                      variant="outline"
                      class="font-bold"
                    >
                      {{ alumnus.graduation_year }}
                    </Badge>
                  </td>
                  <td class="p-6 text-sm font-medium">
                    {{ alumnus.current_activity }}
                    <p
                      v-if="alumnus.institution_name"
                      class="text-[10px] text-muted-foreground italic"
                    >
                      @ {{ alumnus.institution_name }}
                    </p>
                  </td>
                  <td class="p-6 text-right">
                    <Button
                      variant="ghost"
                      size="icon"
                      class="rounded-full"
                    >
                      <LucideIcon
                        name="eye"
                        class="w-4 h-4"
                      />
                    </Button>
                  </td>
                </tr>
              </tbody>
            </table>
            <div
              v-if="alumni.length === 0"
              class="py-20 flex flex-col items-center justify-center border-t"
            >
              <LucideIcon
                name="users"
                class="w-16 h-16 text-muted-foreground/20 mb-4"
              />
              <p class="text-muted-foreground italic">
                {{ t('modules.school.extensions.labels.noAlumniFound') }}
              </p>
            </div>
          </CardContent>
        </Card>
      </TabsContent>

      <!-- Tracer Study -->
      <TabsContent value="tracer">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <Card
            v-for="tracer in tracerStudies"
            :key="tracer.id"
            class="border-none bg-white/60 dark:bg-slate-900/60 backdrop-blur-md rounded-[2rem] p-6"
          >
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-600">
                  <LucideIcon
                    name="briefcase"
                    class="w-6 h-6"
                  />
                </div>
                <div>
                  <h3 class="font-bold">
                    {{ tracer.alumni?.student?.name }}
                  </h3>
                  <Badge
                    :variant="getEmploymentBadge(tracer.employment_status)"
                    class="text-[10px] rounded-lg"
                  >
                    {{ tracer.employment_status }}
                  </Badge>
                </div>
              </div>
              <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">{{ dayjs(tracer.created_at).format('MMM YYYY') }}</span>
            </div>
                        
            <div class="space-y-3 pt-4 border-t border-dashed">
              <div
                v-if="tracer.company_name"
                class="flex items-center justify-between text-sm"
              >
                <span class="text-muted-foreground">{{ t('modules.school.extensions.labels.employer') }}:</span>
                <span class="font-bold">{{ tracer.company_name }}</span>
              </div>
              <div
                v-if="tracer.university_name"
                class="flex items-center justify-between text-sm"
              >
                <span class="text-muted-foreground">{{ t('modules.school.extensions.labels.university') }}:</span>
                <span class="font-bold">{{ tracer.university_name }}</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-muted-foreground">{{ t('modules.school.extensions.labels.majorRelevance') }}:</span>
                <LucideIcon
                  :name="tracer.is_relevant_to_major ? 'check-circle' : 'x-circle'" 
                  :class="tracer.is_relevant_to_major ? 'text-emerald-500' : 'text-rose-500'"
                  class="w-4 h-4"
                />
              </div>
            </div>
          </Card>
          <div
            v-if="tracerStudies.length === 0"
            class="col-span-full py-20 flex flex-col items-center justify-center bg-accent/5 rounded-[3rem] border-2 border-dashed"
          >
            <LucideIcon
              name="clipboard-check"
              class="w-16 h-16 text-muted-foreground/20 mb-4"
            />
            <h3 class="text-xl font-bold">
              {{ t('modules.school.extensions.labels.noTracerData') }}
            </h3>
            <p class="text-muted-foreground">
              {{ t('modules.school.extensions.labels.startCollecting') }}
            </p>
          </div>
        </div>
      </TabsContent>

      <!-- Analytics Placeholder -->
      <TabsContent value="analytics">
        <Card class="border-none bg-white/60 dark:bg-slate-900/60 backdrop-blur-md rounded-[2.5rem] py-20 text-center">
          <LucideIcon
            name="pie-chart"
            class="w-20 h-20 text-muted-foreground/20 mx-auto mb-6"
          />
          <h2 class="text-2xl font-black">
            {{ t('modules.school.extensions.labels.analyticsComingSoon') }}
          </h2>
          <p class="text-muted-foreground max-w-sm mx-auto mt-2">
            {{ t('modules.school.extensions.labels.analyticsDesc') }}
          </p>
        </Card>
      </TabsContent>
    </Tabs>
  </div>
</template>
