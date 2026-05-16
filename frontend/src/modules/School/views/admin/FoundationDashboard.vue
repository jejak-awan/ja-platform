<template>
  <div class="space-y-6">
    <!-- Clean Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-foreground">
          {{ schoolStore.currentSchool?.name || t('modules.school.navigation.menu.foundationDashboard') }}
        </h1>
        <p class="text-muted-foreground text-sm font-medium">
          Kelola jaringan pendidikan, pantau kinerja unit, dan dorong pertumbuhan strategis.
        </p>
      </div>
      <div class="flex items-center gap-3">
        <Button variant="outline" class="rounded-xl border-border/40 bg-muted/20 hover:bg-muted/40">
          <Download class="w-4 h-4 mr-2" />
          Ekspor Laporan
        </Button>
        <Button class="rounded-xl shadow-lg shadow-primary/20" @click="router.push({ name: 'schools.create' })">
          <Plus class="w-4 h-4 mr-2" />
          Tambah Unit Baru
        </Button>
      </div>
    </div>

    <!-- Aggregate Stats Grid (Dynamic) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <Card v-for="stat in dashboardStats" :key="stat.label" class="border-border/40 bg-card shadow-none rounded-xl">
        <CardContent class="p-6">
          <div class="flex items-start justify-between">
            <div class="space-y-1">
              <p class="text-sm font-medium text-muted-foreground">
                {{ stat.label }}
              </p>
              <p class="text-3xl font-bold text-foreground">
                {{ stat.value }}
              </p>
              <div v-if="stat.trend" class="flex items-center gap-1.5 text-xs text-emerald-500 font-bold mt-1">
                <ArrowUpRight class="w-3 h-3" />
                <span>{{ stat.trend }}</span>
              </div>
            </div>
            <div :class="['p-2.5 rounded-xl bg-primary/10 text-primary', stat.bgColor, stat.textColor]">
              <component :is="stat.icon" class="w-5 h-5" />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Unit Management Section -->
      <Card class="lg:col-span-2 border-border/40 bg-card shadow-none rounded-2xl overflow-hidden">
        <CardHeader class="flex flex-row items-center justify-between pb-4 border-b border-border/30">
          <div class="space-y-1">
            <CardTitle class="text-lg font-bold">Daftar Unit Operasional</CardTitle>
            <CardDescription>Rangkuman kinerja dan pertumbuhan di setiap jenjang</CardDescription>
          </div>
          <Tabs default-value="list">
            <TabsList class="rounded-lg bg-muted/50 p-1 h-9">
              <TabsTrigger value="list" class="rounded-md text-[11px] font-bold px-3">Daftar</TabsTrigger>
              <TabsTrigger value="performance" class="rounded-md text-[11px] font-bold px-3">Kinerja</TabsTrigger>
            </TabsList>
          </Tabs>
        </CardHeader>
        <CardContent class="p-0">
          <div class="divide-y divide-border/30">
            <div v-for="unit in unitStore.levels" :key="unit.id" class="p-5 hover:bg-muted/20 transition-colors flex items-center justify-between group">
              <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-muted/60 flex items-center justify-center font-bold text-primary group-hover:bg-primary group-hover:text-primary-foreground transition-all duration-300">
                  {{ unit.level || 'UN' }}
                </div>
                <div>
                  <h4 class="text-sm font-bold text-foreground">{{ unit.name }}</h4>
                  <div class="flex items-center gap-2 mt-0.5">
                    <Badge variant="outline" class="text-[9px] h-4 font-bold border-primary/20 text-primary">{{ unit.level }}</Badge>
                    <span class="text-[10px] text-muted-foreground font-medium">{{ unit.accreditation || 'Akreditasi -' }}</span>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-6">
                <div class="text-right hidden sm:block">
                  <p class="text-xs font-bold text-foreground">94% Hadir</p>
                  <p class="text-[10px] text-muted-foreground font-medium">Kehadiran Rata-rata</p>
                </div>
                <Button variant="ghost" size="icon" class="rounded-lg hover:bg-primary/10 hover:text-primary transition-colors" @click="handleUnitSelect(unit.id)">
                  <ArrowRight class="w-4 h-4" />
                </Button>
              </div>
            </div>
          </div>
          <div v-if="unitStore.levels.length === 0" class="p-12 text-center text-muted-foreground italic text-sm">
            Belum ada unit yang terdaftar.
          </div>
        </CardContent>
      </Card>

      <!-- Side Info Widgets -->
      <div class="space-y-6">
        <!-- Global Mode Notice -->
        <Card class="border-none bg-primary text-primary-foreground shadow-lg shadow-primary/20 rounded-2xl overflow-hidden p-6 relative group">
          <div class="absolute -right-6 -bottom-6 h-32 w-32 rounded-full bg-white/10 blur-2xl group-hover:scale-125 transition-transform duration-700" />
          <div class="relative z-10 space-y-4">
            <div class="p-2.5 bg-white/20 rounded-xl w-fit shadow-inner">
              <ShieldCheck class="w-5 h-5" />
            </div>
            <div class="space-y-1">
              <h3 class="text-lg font-bold tracking-tight">Konteks Manajemen Pusat</h3>
              <p class="text-xs text-white/80 leading-relaxed font-medium">
                Anda berada di tampilan Yayasan/Pusat. Manajemen operasional siswa & guru dilakukan di tiap Unit.
              </p>
            </div>
            <Button variant="secondary" class="w-full rounded-xl font-bold py-5 bg-white text-primary hover:bg-white/90" @click="router.push({ name: 'schools.index' })">
              Kelola Struktur Unit
              <ArrowRight class="w-4 h-4 ml-2" />
            </Button>
          </div>
        </Card>

        <!-- Node Statistics -->
        <Card class="border-border/40 bg-card shadow-none rounded-2xl p-6 space-y-5">
            <h3 class="text-xs font-bold text-muted-foreground/60 tracking-wider">Metrik Jaringan</h3>
            <div class="space-y-3">
                <div v-for="node in nodes" :key="node.label" class="flex items-center justify-between p-3 rounded-xl border border-transparent hover:border-border/40 hover:bg-muted/30 transition-all cursor-pointer">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-muted/60 flex items-center justify-center">
                            <component :is="node.icon" class="w-4 h-4 text-muted-foreground" />
                        </div>
                        <span class="text-sm font-bold text-foreground/80">{{ node.label }}</span>
                    </div>
                    <Badge variant="secondary" class="bg-muted text-[10px] font-bold h-5">{{ node.count }}</Badge>
                </div>
            </div>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useSchoolStore } from '@/modules/School/stores/school';
import { useUnitStore } from '@/modules/School/stores/unit';
import InstitutionService from '@/modules/School/services/InstitutionService';
import { 
  Plus, Download, Users, Briefcase, GraduationCap, School, 
  ArrowUpRight, ArrowRight, ShieldCheck, LayoutGrid, Network, Settings
} from 'lucide-vue-next';
import { 
  Button, Card, CardContent, CardHeader, CardTitle, CardDescription, 
  Badge, Tabs, TabsList, TabsTrigger 
} from '@/shared/components/ui';

const { t } = useI18n();
const router = useRouter();
const schoolStore = useSchoolStore();
const unitStore = useUnitStore();

const loading = ref(false);
const statsData = ref<any>(null);

const dashboardStats = computed(() => [
  { label: 'Total Unit', value: String(unitStore.levels.length), icon: School, trend: 'Stabil', bgColor: 'bg-blue-500/10', textColor: 'text-blue-500' },
  { label: 'Total Siswa', value: statsData.value?.students_count || '0', icon: Users, trend: '+12%', bgColor: 'bg-emerald-500/10', textColor: 'text-emerald-500' },
  { label: 'Total Pegawai', value: statsData.value?.staff_count || '0', icon: Briefcase, trend: '+4%', bgColor: 'bg-amber-500/10', textColor: 'text-amber-500' },
  { label: 'Rata-rata Hadir', value: '94.2%', icon: GraduationCap, trend: '+0.8%', bgColor: 'bg-purple-500/10', textColor: 'text-purple-500' },
]);

const nodes = computed(() => [
    { label: 'Unit Pendidikan', count: unitStore.levels.length, icon: LayoutGrid },
    { label: 'Node Organisasi', count: 12, icon: Network },
    { label: 'Kebijakan Sistem', count: 3, icon: Settings },
]);

const handleUnitSelect = (id: string) => {
  unitStore.setActiveLevel(id, 'unit', true);
  router.push({ name: 'schools.dashboard' });
};

onMounted(async () => {
  loading.value = true;
  try {
    if (!schoolStore.currentSchool) await schoolStore.fetchSchool();
    
    // fetchUnits is handled by AdminLayout, but we can call it here if we need to 
    // ensure it's loaded, though the store lock handles concurrency.
    // However, to reduce churn, we rely on the parent's initialization.

    // Fetch aggregate stats if endpoint exists
    const response = await InstitutionService.getStats();
    statsData.value = response.data?.data || response.data;
  } catch (e) {
    if (String(e).toLowerCase().includes('aborted')) return;
    console.error('[FoundationDashboard] Error fetching data:', e);
  } finally {
    loading.value = false;
  }
});
</script>
