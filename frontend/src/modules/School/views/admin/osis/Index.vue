<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    Card, CardHeader, CardTitle, CardContent,
    Tabs, TabsList, TabsTrigger, TabsContent,
    Button, Badge, LucideIcon
} from '@/components/ui';
import axios from 'axios';
import dayjs from 'dayjs';
import { useToast } from '@/composables/useToast';

const { t } = useI18n();
const toast = useToast();
const loading = ref(true);
const activeTab = ref('programs');

// Interfaces
interface OsisProgram {
    id: number;
    name: string;
    description: string;
    status: string;
    planned_date: string;
    estimated_budget: number;
}

interface OsisMember {
    id: number;
    student_id: number;
    position: string;
    period: string;
    student?: {
        name: string;
        nis: string;
    };
}

interface OsisFinance {
    id: number;
    type: 'income' | 'expense';
    amount: number;
    date: string;
    description: string;
}

interface OsisSuggestion {
    id: number;
    subject: string;
    content: string;
    status: string;
    created_at: string;
    response?: string;
}

// Data
const programs = ref<OsisProgram[]>([]);
const members = ref<OsisMember[]>([]);
const finances = ref<OsisFinance[]>([]);
const suggestions = ref<OsisSuggestion[]>([]);

const fetchData = async () => {
    loading.value = true;
    try {
        const [pRes, mRes, fRes, sRes] = await Promise.all([
            axios.get('/api/v1/admin/osis/programs'),
            axios.get('/api/v1/admin/osis/members'),
            axios.get('/api/v1/admin/osis/finances'),
            axios.get('/api/v1/admin/osis/suggestions')
        ]);
        programs.value = pRes.data.data;
        members.value = mRes.data.data;
        finances.value = fRes.data.data;
        suggestions.value = sRes.data.data;
    } catch {
        toast.error.default('Failed to fetch OSIS data');
    } finally {
        loading.value = false;
    }
};

const getStatusBadge = (status: string) => {
    const variants: Record<string, string> = {
        planned: 'secondary',
        in_progress: 'default',
        completed: 'success',
        cancelled: 'destructive',
        pending: 'warning',
        reviewed: 'info',
        actioned: 'success',
        rejected: 'destructive'
    };
    return (variants[status] || 'default') as "default" | "destructive" | "outline" | "secondary" | "success" | "warning" | "info" | null | undefined;
};

onMounted(fetchData);

// Stats
const stats = computed(() => {
    const totalPrograms = programs.value.length;
    const completedPrograms = programs.value.filter(p => p.status === 'completed').length;
    const totalMembers = members.value.length;
    const pendingSuggestions = suggestions.value.filter(s => s.status === 'pending').length;
    
    return { totalPrograms, completedPrograms, totalMembers, pendingSuggestions };
});

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
};

</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black tracking-tight text-foreground/90">
          {{ t('features.school.osis.title') }}
        </h1>
        <p class="text-muted-foreground">
          {{ t('features.school.osis.subtitle') }}
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
          {{ t('features.school.osis.actions.reload') }}
        </Button>
        <Button class="rounded-xl shadow-lg shadow-primary/20">
          <LucideIcon
            name="plus"
            class="w-4 h-4 mr-2"
          />
          {{ t('features.school.osis.actions.newActivity') }}
        </Button>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <Card
        v-for="(stat, idx) in [
          { key: 'workPrograms', value: stats.totalPrograms, color: 'text-primary', icon: 'package' },
          { key: 'completed', value: stats.completedPrograms, color: 'text-green-500', icon: 'check-circle' },
          { key: 'members', value: stats.totalMembers, color: 'text-blue-500', icon: 'users' },
          { key: 'pendingSuggestions', value: stats.pendingSuggestions, color: 'text-orange-500', icon: 'inbox' }
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
              {{ t('features.school.osis.stats.' + stat.key) }}
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
          value="programs"
          class="rounded-xl px-8"
        >
          {{ t('features.school.osis.tabs.programs') }}
        </TabsTrigger>
        <TabsTrigger
          value="members"
          class="rounded-xl px-8"
        >
          {{ t('features.school.osis.tabs.members') }}
        </TabsTrigger>
        <TabsTrigger
          value="finances"
          class="rounded-xl px-8"
        >
          {{ t('features.school.osis.tabs.finances') }}
        </TabsTrigger>
        <TabsTrigger
          value="suggestions"
          class="rounded-xl px-8"
        >
          {{ t('features.school.osis.tabs.suggestions') }}
          <Badge
            v-if="stats.pendingSuggestions"
            variant="destructive"
            class="ml-2 scale-75"
          >
            {{ stats.pendingSuggestions }}
          </Badge>
        </TabsTrigger>
      </TabsList>

      <!-- Programs Tab -->
      <TabsContent value="programs">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <Card
            v-for="program in programs"
            :key="program.id"
            class="group transition-all hover:shadow-2xl hover:shadow-primary/5 border-none bg-white/60 dark:bg-slate-900/60 backdrop-blur-md rounded-[2.5rem] overflow-hidden"
          >
            <CardHeader class="pb-2">
              <div class="flex items-center justify-between mb-2">
                <Badge :variant="getStatusBadge(program.status)">
                  {{ t('common.status.' + program.status) }}
                </Badge>
                <span class="text-[10px] font-medium text-muted-foreground">{{ dayjs(program.planned_date).format('DD MMM YYYY') }}</span>
              </div>
              <CardTitle class="text-lg font-bold group-hover:text-primary transition-colors">
                {{ program.name }}
              </CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-sm text-muted-foreground line-clamp-3 mb-4">
                {{ program.description }}
              </p>
              <div class="flex items-center justify-between pt-4 border-t border-dashed">
                <div class="flex flex-col">
                  <span class="text-[8px] uppercase font-bold text-muted-foreground tracking-widest">{{ t('features.school.osis.programs.estimatedBudget') }}</span>
                  <span class="font-bold text-sm">{{ formatCurrency(program.estimated_budget) }}</span>
                </div>
                <Button
                  size="icon"
                  variant="ghost"
                  class="rounded-full"
                >
                  <LucideIcon
                    name="external-link"
                    class="w-4 h-4"
                  />
                </Button>
              </div>
            </CardContent>
          </Card>
          <!-- Empty State / Add Card -->
          <div
            v-if="programs.length === 0"
            class="col-span-full py-20 flex flex-col items-center justify-center bg-accent/10 rounded-[3rem] border-2 border-dashed"
          >
            <LucideIcon
              name="package-open"
              class="w-16 h-16 text-muted-foreground/20 mb-4"
            />
            <h3 class="text-xl font-bold">
              {{ t('features.school.osis.programs.emptyTitle') }}
            </h3>
            <p class="text-muted-foreground">
              {{ t('features.school.osis.programs.emptyDesc') }}
            </p>
          </div>
        </div>
      </TabsContent>

      <!-- Members Tab -->
      <TabsContent value="members">
        <Card class="border-none bg-white/60 dark:bg-slate-900/60 backdrop-blur-md rounded-[2.5rem]">
          <CardContent class="p-0">
            <table class="w-full text-left">
              <thead>
                <tr class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/40 border-b">
                  <th class="p-6">
                    {{ t('features.school.osis.members.name') }}
                  </th>
                  <th class="p-6">
                    {{ t('features.school.osis.members.position') }}
                  </th>
                  <th class="p-6">
                    {{ t('features.school.osis.members.period') }}
                  </th>
                  <th class="p-6 text-right">
                    {{ t('common.labels.actions') }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="member in members"
                  :key="member.id"
                  class="border-b last:border-0 hover:bg-accent/5 transition-colors"
                >
                  <td class="p-6">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center font-bold text-primary">
                        {{ member.student?.name?.charAt(0) }}
                      </div>
                      <div>
                        <p class="font-bold text-foreground">
                          {{ member.student?.name }}
                        </p>
                        <p class="text-[10px] text-muted-foreground uppercase tracking-wider">
                          {{ member.student?.nis }}
                        </p>
                      </div>
                    </div>
                  </td>
                  <td class="p-6">
                    <Badge
                      variant="outline"
                      class="font-bold rounded-lg"
                    >
                      {{ member.position }}
                    </Badge>
                  </td>
                  <td class="p-6 text-sm font-medium">
                    {{ member.period }}
                  </td>
                  <td class="p-6 text-right">
                    <Button
                      variant="ghost"
                      size="sm"
                      class="rounded-xl"
                    >
                      {{ t('features.school.osis.members.remove') }}
                    </Button>
                  </td>
                </tr>
              </tbody>
            </table>
          </CardContent>
        </Card>
      </TabsContent>

      <!-- Finance Tab -->
      <TabsContent value="finances">
        <Card class="border-none bg-white/60 dark:bg-slate-900/60 backdrop-blur-md rounded-[2.5rem]">
          <CardContent class="p-6">
            <div class="flex items-center justify-between mb-8">
              <h2 class="text-xl font-black">
                {{ t('features.school.osis.finances.title') }}
              </h2>
              <div class="flex gap-4">
                <div class="px-4 py-2 bg-green-500/10 rounded-2xl flex flex-col">
                  <span class="text-[8px] uppercase font-bold text-green-500 tracking-widest">{{ t('features.school.osis.finances.income') }}</span>
                  <span class="font-black text-green-600">IDR 0</span>
                </div>
                <div class="px-4 py-2 bg-red-500/10 rounded-2xl flex flex-col">
                  <span class="text-[8px] uppercase font-bold text-red-500 tracking-widest">{{ t('features.school.osis.finances.expense') }}</span>
                  <span class="font-black text-red-600">IDR 0</span>
                </div>
              </div>
            </div>
            <div class="py-20 flex flex-col items-center justify-center">
              <LucideIcon
                name="wallet"
                class="w-16 h-16 text-muted-foreground/20 mb-4"
              />
              <p class="text-muted-foreground italic">
                {{ t('features.school.osis.finances.empty') }}
              </p>
            </div>
          </CardContent>
        </Card>
      </TabsContent>

      <!-- Suggestions Tab -->
      <TabsContent value="suggestions">
        <div class="space-y-4">
          <Card
            v-for="suggestion in suggestions"
            :key="suggestion.id"
            class="border-none bg-white/60 dark:bg-slate-900/60 backdrop-blur-md rounded-[2rem] overflow-hidden"
          >
            <CardContent class="p-6">
              <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                  <div class="flex items-center gap-3 mb-2">
                    <Badge :variant="getStatusBadge(suggestion.status)">
                      {{ t('common.status.' + suggestion.status) }}
                    </Badge>
                    <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">{{ dayjs(suggestion.created_at).format('DD MMM YYYY HH:mm') }}</span>
                  </div>
                  <h3 class="text-lg font-bold mb-1">
                    {{ suggestion.subject }}
                  </h3>
                  <p class="text-sm text-muted-foreground">
                    {{ suggestion.content }}
                  </p>
                                    
                  <div
                    v-if="suggestion.response"
                    class="mt-4 p-4 bg-primary/5 rounded-2xl border border-primary/10"
                  >
                    <p class="text-[10px] uppercase font-black tracking-widest text-primary mb-1">
                      {{ t('features.school.osis.suggestions.officialResponse') }}
                    </p>
                    <p class="text-sm italic text-foreground/80">
                      {{ suggestion.response }}
                    </p>
                  </div>
                </div>
                <div class="flex flex-col gap-2 shrink-0">
                  <Button
                    size="sm"
                    class="rounded-xl"
                  >
                    {{ t('features.school.osis.suggestions.respond') }}
                  </Button>
                  <Button
                    size="sm"
                    variant="ghost"
                    class="rounded-xl"
                  >
                    {{ t('features.school.osis.suggestions.archive') }}
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
          <div
            v-if="suggestions.length === 0"
            class="py-20 flex flex-col items-center justify-center bg-accent/5 rounded-[3rem]"
          >
            <LucideIcon
              name="message-square-off"
              class="w-16 h-16 text-muted-foreground/20 mb-4"
            />
            <p class="text-muted-foreground italic">
              {{ t('features.school.osis.suggestions.empty') }}
            </p>
          </div>
        </div>
      </TabsContent>
    </Tabs>
  </div>
</template>

<style scoped>
.glass-effect {
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(12px);
}
</style>
