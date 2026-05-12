<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    Card, CardHeader, CardTitle, CardContent,
    Tabs, TabsList, TabsTrigger, TabsContent,
    Button, Badge, LucideIcon
} from '@/shared/components/ui';
import axios from 'axios';
import dayjs from 'dayjs';
import { useToast } from '@/shared/composables/useToast';

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
const suggestions = ref<OsisSuggestion[]>([]);

const fetchData = async () => {
    loading.value = true;
    try {
        const [pRes, mRes, sRes] = await Promise.all([
            axios.get('/api/v1/admin/osis/programs'),
            axios.get('/api/v1/admin/osis/members'),
            axios.get('/api/v1/admin/osis/suggestions')
        ]);
        programs.value = pRes.data.data;
        members.value = mRes.data.data;
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
  <div class="space-y-6 p-8 animate-in fade-in duration-700">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-2">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center border border-primary/20 shadow-sm">
            <LucideIcon
              name="Globe"
              class="w-6 h-6 text-primary"
            />
          </div>
          <h1 class="text-3xl font-bold tracking-tight text-foreground">
            {{ t('modules.school.osis.title') }}
          </h1>
        </div>
        <p class="text-sm text-muted-foreground">
          {{ t('modules.school.osis.subtitle') }}
        </p>
      </div>
      <div class="flex gap-2">
        <Button
          variant="outline"
          class="rounded-xl border-border/40 h-11 px-4"
          @click="fetchData"
        >
          <LucideIcon
            name="refresh-cw"
            :class="{ 'animate-spin': loading }"
            class="w-4 h-4"
          />
        </Button>
        <Button class="rounded-xl shadow-sm px-6 h-11">
          <LucideIcon
            name="plus"
            class="w-4 h-4 mr-2"
          />
          {{ t('modules.school.osis.actions.newActivity') }}
        </Button>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div 
        v-for="(stat, idx) in [
          { key: 'workPrograms', value: stats.totalPrograms, color: 'text-primary', icon: 'Package' },
          { key: 'completed', value: stats.completedPrograms, color: 'text-success', icon: 'CircleCheck' },
          { key: 'members', value: stats.totalMembers, color: 'text-info', icon: 'Users' },
          { key: 'pendingSuggestions', value: stats.pendingSuggestions, color: 'text-warning', icon: 'Inbox' }
        ]"
        :key="idx"
        class="group bg-card border border-border/40 rounded-xl p-6 shadow-sm hover:bg-muted/30 transition-all duration-300"
      >
        <div class="flex justify-between items-start mb-4">
          <div :class="`w-10 h-10 rounded-xl flex items-center justify-center border border-border/40 bg-muted/50 ${stat.color}`">
            <LucideIcon
              :name="stat.icon"
              class="w-5 h-5"
            />
          </div>
          <span class="text-[10px] font-semibold text-muted-foreground/60">{{ t('modules.school.osis.stats.' + stat.key) }}</span>
        </div>
        <div class="flex items-baseline gap-2">
          <h3 class="text-2xl font-bold tracking-tight text-foreground">
            {{ stat.value }}
          </h3>
          <span class="text-[10px] font-medium text-muted-foreground">Data</span>
        </div>
      </div>
    </div>

    <Tabs
      v-model="activeTab"
      class="w-full"
    >
      <TabsList class="p-2 bg-muted/50 rounded-2xl inline-flex h-auto gap-2 mb-6">
        <TabsTrigger
          value="programs"
          class="rounded-xl px-8 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
        >
          {{ t('modules.school.osis.tabs.programs') }}
        </TabsTrigger>
        <TabsTrigger
          value="members"
          class="rounded-xl px-8 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
        >
          {{ t('modules.school.osis.tabs.members') }}
        </TabsTrigger>
        <TabsTrigger
          value="suggestions"
          class="rounded-xl px-8 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
        >
          {{ t('modules.school.osis.tabs.suggestions') }}
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
            class="group transition-all bg-card border border-border/40 rounded-xl overflow-hidden shadow-sm hover:bg-muted/30"
          >
            <CardHeader class="pb-2">
              <div class="flex items-center justify-between mb-2">
                <Badge 
                  :variant="getStatusBadge(program.status)"
                  class="rounded-lg text-[9px] font-black uppercase tracking-widest px-2 py-0.5 border border-current bg-transparent"
                >
                  {{ t('common.status.' + program.status) }}
                </Badge>
                <span class="text-[9px] font-black text-muted-foreground/60 uppercase tracking-widest">{{ dayjs(program.planned_date).format('DD MMM YYYY') }}</span>
              </div>
              <CardTitle class="text-lg font-bold tracking-tight text-foreground group-hover:text-primary transition-colors">
                {{ program.name }}
              </CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-sm font-medium text-muted-foreground line-clamp-3 mb-4 leading-relaxed">
                {{ program.description }}
              </p>
              <div class="flex items-center justify-between pt-4 border-t border-border/40">
                <div class="flex flex-col">
                  <span class="text-[9px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1">{{ t('modules.school.osis.programs.estimatedBudget') }}</span>
                  <span class="font-black text-sm text-foreground">{{ formatCurrency(program.estimated_budget) }}</span>
                </div>
                <Button
                  size="icon"
                  variant="ghost"
                  class="h-8 w-8 rounded-lg text-muted-foreground hover:bg-primary/10 hover:text-primary"
                >
                  <LucideIcon
                    name="ExternalLink"
                    class="w-4 h-4"
                  />
                </Button>
              </div>
            </CardContent>
          </Card>
          <!-- Empty State / Add Card -->
          <div
            v-if="programs.length === 0"
            class="col-span-full py-20 flex flex-col items-center justify-center bg-muted/20 rounded-xl border-2 border-dashed border-border/40"
          >
            <LucideIcon
              name="Package"
              class="w-12 h-12 text-muted-foreground/20 mb-4"
            />
            <h3 class="text-xl font-bold text-foreground">
              {{ t('modules.school.osis.programs.emptyTitle') }}
            </h3>
            <p class="text-muted-foreground text-sm font-medium">
              {{ t('modules.school.osis.programs.emptyDesc') }}
            </p>
          </div>
        </div>
      </TabsContent>

      <TabsContent value="members">
        <Card class="border border-border/40 bg-card shadow-sm rounded-xl overflow-hidden">
          <CardContent class="p-0">
            <div class="overflow-x-auto">
              <table class="w-full text-left">
                <thead>
                  <tr class="text-[11px] font-semibold text-muted-foreground/70 border-b border-border/40 bg-muted/20">
                    <th class="p-6">
                      {{ t('modules.school.osis.members.name') }}
                    </th>
                    <th class="p-6">
                      {{ t('modules.school.osis.members.position') }}
                    </th>
                    <th class="p-6">
                      {{ t('modules.school.osis.members.period') }}
                    </th>
                    <th class="p-6 text-right">
                      {{ t('common.actions.title') }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="member in members"
                    :key="member.id"
                    class="border-b border-border/40 last:border-0 hover:bg-muted/30 transition-colors"
                  >
                    <td class="p-6">
                      <div class="flex items-center gap-3 text-left">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center font-black text-primary border border-primary/20">
                          {{ member.student?.name?.charAt(0) }}
                        </div>
                        <div>
                          <p class="font-bold text-foreground">
                            {{ member.student?.name }}
                          </p>
                          <p class="text-[10px] text-muted-foreground font-medium">
                            {{ member.student?.nis }}
                          </p>
                        </div>
                      </div>
                    </td>
                    <td class="p-6">
                      <Badge
                        variant="outline"
                        class="rounded-lg text-[9px] font-black uppercase tracking-widest px-2 py-0.5 border border-border/40 bg-transparent text-foreground"
                      >
                        {{ member.position }}
                      </Badge>
                    </td>
                    <td class="p-6 text-xs font-bold text-muted-foreground">
                      {{ member.period }}
                    </td>
                    <td class="p-6 text-right">
                      <Button
                        variant="ghost"
                        size="sm"
                        class="rounded-xl h-8 text-destructive hover:bg-destructive/10 hover:text-destructive font-bold"
                      >
                        {{ t('modules.school.osis.members.remove') }}
                      </Button>
                    </td>
                  </tr>
                </tbody>
              </table>
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
            class="bg-card border border-border/40 rounded-xl overflow-hidden shadow-sm hover:bg-muted/30 transition-all text-left"
          >
            <CardContent class="p-6">
              <div class="flex flex-col md:flex-row items-start justify-between gap-6">
                <div class="flex-1">
                  <div class="flex items-center gap-3 mb-3">
                    <Badge 
                      :variant="getStatusBadge(suggestion.status)"
                      class="rounded-lg text-[9px] font-black uppercase tracking-widest px-2 py-0.5 border border-current bg-transparent"
                    >
                      {{ t('common.status.' + suggestion.status) }}
                    </Badge>
                    <span class="text-[9px] font-black text-muted-foreground/60 uppercase tracking-widest">{{ dayjs(suggestion.created_at).format('DD MMM YYYY HH:mm') }}</span>
                  </div>
                  <h3 class="text-lg font-bold tracking-tight text-foreground mb-1">
                    {{ suggestion.subject }}
                  </h3>
                  <p class="text-sm font-medium text-muted-foreground leading-relaxed">
                    {{ suggestion.content }}
                  </p>
                                    
                  <div
                    v-if="suggestion.response"
                    class="mt-6 p-4 bg-primary/5 rounded-xl border border-primary/20 relative overflow-hidden"
                  >
                    <p class="text-[9px] uppercase font-black tracking-widest text-primary mb-2 relative z-10">
                      {{ t('modules.school.osis.suggestions.officialResponse') }}
                    </p>
                    <p class="text-sm font-bold italic text-foreground/80 relative z-10 leading-relaxed">
                      "{{ suggestion.response }}"
                    </p>
                    <LucideIcon name="MessageSquare" class="absolute -right-2 -bottom-2 w-12 h-12 text-primary opacity-[0.05]" />
                  </div>
                </div>
                <div class="flex flex-row md:flex-col gap-2 shrink-0 w-full md:w-auto">
                  <Button
                    size="sm"
                    class="rounded-xl flex-1 md:flex-none h-9 font-bold px-6"
                  >
                    {{ t('modules.school.osis.suggestions.respond') }}
                  </Button>
                  <Button
                    size="sm"
                    variant="ghost"
                    class="rounded-xl flex-1 md:flex-none h-9 font-bold px-6 text-muted-foreground hover:bg-muted/50"
                  >
                    {{ t('modules.school.osis.suggestions.archive') }}
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
          <div
            v-if="suggestions.length === 0"
            class="py-20 flex flex-col items-center justify-center bg-muted/20 rounded-xl border-2 border-dashed border-border/40"
          >
            <LucideIcon
              name="MessageSquareX"
              class="w-12 h-12 text-muted-foreground/20 mb-4"
            />
            <p class="text-muted-foreground text-sm font-medium italic">
              {{ t('modules.school.osis.suggestions.empty') }}
            </p>
          </div>
        </div>
      </TabsContent>
    </Tabs>
  </div>
</template>

<style scoped>
</style>
