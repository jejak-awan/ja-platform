<template>
  <div class="space-y-8 animate-in zoom-in-95 duration-700">
    <!-- OSIS Header: Clean -->
    <div class="p-8 rounded-xl bg-card border border-border/50 shadow-sm relative overflow-hidden group">
      <div class="relative z-10">
        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-primary/10 rounded-xl text-primary border border-primary/20">
            <LucideIcon
              name="Shield"
              class="w-6 h-6"
            />
          </div>
          <h1 class="text-3xl font-bold tracking-tight text-foreground">
            {{ t('features.school.osis.dashboard.title') }}
          </h1>
        </div>
        <p class="text-muted-foreground mt-2 max-w-xl text-lg font-medium">
          {{ t('features.school.osis.dashboard.subtitle') }}
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <Card
        v-for="stat in osisStats"
        :key="stat.key"
        class="bg-card border-border/40 shadow-none rounded-xl"
      >
        <CardContent class="p-6">
          <div class="flex justify-between items-start">
            <div class="space-y-1">
              <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">
                {{ t('features.school.osis.stats.' + stat.key) }}
              </p>
              <h3 class="text-3xl font-bold text-foreground">
                {{ stat.value }}
              </h3>
            </div>
            <div :class="['p-2 rounded-xl bg-opacity-10', stat.colorClass]">
              <LucideIcon
                :name="stat.icon"
                class="w-5 h-5"
              />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <Card class="lg:col-span-2 bg-card border-border/40 shadow-none rounded-xl">
        <CardHeader class="flex flex-row items-center justify-between">
          <CardTitle>{{ t('features.school.osis.tabs.programs') }}</CardTitle>
          <Button
            variant="outline"
            size="sm"
            class="rounded-xl"
          >
            {{ t('common.actions.new') }}
          </Button>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div
              v-for="program in upcomingPrograms"
              :key="program.id"
              class="p-4 rounded-xl bg-muted/20 border border-border/20 hover:bg-muted/40 transition-colors cursor-pointer flex gap-4 items-center"
            >
              <div class="w-14 h-14 bg-background rounded-xl border border-border/50 flex flex-col items-center justify-center shrink-0">
                <span class="text-[10px] font-bold text-pink-500 uppercase">{{ getMonthName(program.planned_date) }}</span>
                <span class="text-xl font-bold">{{ getDay(program.planned_date) }}</span>
              </div>
              <div class="flex-1">
                <h4 class="font-bold">
                  {{ program.name }}
                </h4>
                <p class="text-xs text-muted-foreground">
                  {{ program.description }}
                </p>
              </div>
              <Badge
                :variant="program.status === 'in_progress' ? 'default' : 'outline'"
                class="rounded-full text-[10px] uppercase font-bold"
              >
                {{ t('common.status.' + program.status) }}
              </Badge>
            </div>
            <div
              v-if="upcomingPrograms.length === 0"
              class="py-10 text-center text-muted-foreground italic"
            >
              {{ t('features.school.osis.programs.emptyTitle') }}
            </div>
          </div>
        </CardContent>
      </Card>

      <div class="space-y-8">
        <Card class="border-border/40 bg-card text-foreground rounded-xl shadow-none overflow-hidden relative group">
          <CardHeader>
            <CardTitle class="text-[11px] font-bold text-primary">
              {{ t('features.school.osis.finances.title') }}
            </CardTitle>
            <CardDescription class="text-muted-foreground italic mt-1 text-[10px]">
              Sisa saldo kas organisasi periode ini.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <h2 class="text-3xl font-bold tracking-tight">
              {{ formatCurrency(stats.budget) }}
            </h2>
            <div class="mt-6 flex gap-2">
              <div class="flex-1 h-1 rounded-full bg-muted overflow-hidden">
                <div
                  class="h-full bg-primary"
                  :style="{ width: stats.programs > 0 ? (stats.completedPrograms / stats.programs * 100) + '%' : '0%' }"
                />
              </div>
            </div>
            <p class="text-[9px] uppercase font-bold mt-2 opacity-60 italic text-muted-foreground">
              {{ stats.programs > 0 ? Math.round(stats.completedPrograms / stats.programs * 100) : 0 }}% {{ t('common.labels.completed') }}
            </p>
          </CardContent>
        </Card>

        <div class="p-6 rounded-3xl bg-pink-500/10 border border-pink-500/20">
          <h4 class="font-bold flex items-center gap-2 mb-4">
            <LucideIcon
              name="MessageSquare"
              class="w-4 h-4 text-pink-500"
            />
            {{ t('features.school.osis.tabs.suggestions') }}
          </h4>
          <div class="space-y-3">
            <div
              v-for="suggestion in recentSuggestions"
              :key="suggestion.id"
              class="text-xs p-3 bg-card border border-border/50 rounded-xl leading-relaxed italic text-muted-foreground"
            >
              "{{ suggestion.content }}"
              <div class="mt-2 text-[8px] font-black text-foreground uppercase opacity-50 not-italic">
                — {{ suggestion.student?.name || t('common.labels.anonymous') }}
              </div>
            </div>
            <div
              v-if="recentSuggestions.length === 0"
              class="text-[10px] text-muted-foreground italic text-center py-4"
            >
              {{ t('features.school.osis.suggestions.empty') }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  Card, CardContent, CardHeader, CardTitle, CardDescription,
  Button, LucideIcon, Badge
} from '@/components/ui';
import axios from 'axios';
import dayjs from 'dayjs';

const { t } = useI18n();
const loading = ref(true);
const stats = ref({
    programs: 0,
    members: 0,
    suggestions: 0,
    completedPrograms: 0,
    budget: 0
});
const upcomingPrograms = ref<any[]>([]);
const recentSuggestions = ref<any[]>([]);

const fetchData = async () => {
    loading.value = true;
    try {
        const [pRes, mRes, sRes, fRes] = await Promise.all([
            axios.get('/api/v1/admin/osis/programs'),
            axios.get('/api/v1/admin/osis/members'),
            axios.get('/api/v1/admin/osis/suggestions'),
            axios.get('/api/v1/admin/osis/finances')
        ]);
        
        const programs = pRes.data.data || [];
        stats.value.programs = programs.length;
        stats.value.completedPrograms = programs.filter((p: any) => p.status === 'completed').length;
        stats.value.members = (mRes.data.data || []).length;
        stats.value.suggestions = (sRes.data.data || []).filter((s: any) => s.status === 'pending').length;
        
        // Upcoming programs (planned/in_progress)
        upcomingPrograms.value = programs
            .filter((p: any) => p.status !== 'completed' && p.status !== 'cancelled')
            .sort((a: any, b: any) => new Date(a.planned_date).getTime() - new Date(b.planned_date).getTime())
            .slice(0, 3);

        recentSuggestions.value = (sRes.data.data || [])
            .filter((s: any) => s.status === 'pending')
            .slice(0, 2);

        // Budget calc
        const finances = fRes.data.data || [];
        const income = finances.filter((f: any) => f.type === 'income').reduce((acc: number, cur: any) => acc + Number(cur.amount), 0);
        const expense = finances.filter((f: any) => f.type === 'expense').reduce((acc: number, cur: any) => acc + Number(cur.amount), 0);
        stats.value.budget = income - expense;

    } catch (error) {
        console.error('Failed to fetch OSIS dashboard data', error);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchData);

const osisStats = computed(() => [
  { key: 'completed', label: 'Event Terlaksana', value: stats.value.completedPrograms, icon: 'Sparkles', colorClass: 'text-pink-500 bg-pink-500' },
  { key: 'members', label: 'Total Anggota', value: stats.value.members, icon: 'Users', colorClass: 'text-blue-500 bg-blue-500' },
  { key: 'pendingSuggestions', label: 'Saran Baru', value: stats.value.suggestions, icon: 'FilePlus', colorClass: 'text-orange-500 bg-orange-500' },
]);

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
};

const getMonthName = (date: string) => dayjs(date).format('MMM').toUpperCase();
const getDay = (date: string) => dayjs(date).format('DD');
</script>
