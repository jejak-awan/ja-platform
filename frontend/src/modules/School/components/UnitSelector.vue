<template>
  <div
    v-if="canSelectLevel"
    class="relative inline-block text-left"
  >
    <TooltipProvider>
      <Tooltip :delay-duration="300">
        <Popover v-model:open="showSelector">
          <PopoverTrigger as-child>
            <TooltipTrigger as-child>
              <button
                class="flex items-center justify-center w-9 h-9 rounded-full text-muted-foreground hover:text-foreground hover:bg-primary/5 hover:ring-4 hover:ring-primary/10 hover:scale-110 active:scale-95 transition-all duration-300 group focus:outline-none"
              >
                <LucideIcon
                  :name="getUnitIcon(activeUnit?.level)"
                  class="w-5 h-5 transition-transform duration-300"
                />
              </button>
            </TooltipTrigger>
          </PopoverTrigger>
          
          <PopoverContent align="end" :side-offset="8" class="w-[320px] p-0 overflow-hidden border-none shadow-2xl rounded-[1.25rem] bg-background/95 backdrop-blur-xl ring-1 ring-border/50 z-[60]">
            <!-- Header Section: Tighter and cleaner -->
            <div class="relative p-4 bg-gradient-to-br from-primary/10 via-transparent to-muted/20 border-b border-border/30">
              <div class="flex items-center gap-2.5 mb-3">
                <div class="w-7 h-7 rounded-lg bg-primary flex items-center justify-center shadow-lg shadow-primary/20">
                  <LayoutGrid class="w-3.5 h-3.5 text-primary-foreground" />
                </div>
                <div class="flex flex-col">
                  <h2 class="text-[13px] font-bold tracking-tight text-foreground leading-none">{{ t('common.labels.selectUnitLevel') }}</h2>
                  <p class="text-[9px] text-muted-foreground font-bold tracking-tight mt-1">{{ t('common.labels.workspaceSwitcher') }}</p>
                </div>
              </div>

              <div class="relative group">
                <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground/60 group-focus-within:text-primary transition-colors" />
                <input 
                  v-model="searchQuery"
                  type="text" 
                  class="w-full bg-background/50 border border-border/50 focus:border-primary/40 focus:ring-4 focus:ring-primary/5 rounded-lg py-1.5 pl-8 pr-3 text-[12px] font-medium outline-none transition-all placeholder:text-muted-foreground/40"
                  placeholder="Cari lapis manajemen..."
                />
              </div>
            </div>

            <!-- List Sections: Left aligned, tighter gaps, precise typography -->
            <div class="max-h-[380px] overflow-y-auto p-1.5 space-y-3 bg-background/20">
              <!-- GLOBAL AUTHORITIES SECTION -->
              <div v-if="filteredAuthorities.length > 0">
                <div class="px-2.5 mb-1.5 text-[9px] font-bold text-muted-foreground/50 tracking-wider">Otoritas Global</div>
                <div class="space-y-0.5">
                  <button 
                    v-for="auth in filteredAuthorities" 
                    :key="auth.id + auth.type" 
                    class="w-full flex items-center gap-3 p-2 rounded-lg transition-all duration-200 group relative border border-transparent"
                    :class="auth.id === activeUnit?.id && auth.type === unitStore.activeContextType
                      ? 'bg-primary/10 border-primary/10' 
                      : 'hover:bg-muted/60 hover:border-border/40 text-foreground'"
                    @click="handleSelect(auth.id, auth.type as any)"
                  >
                    <div 
                      class="flex items-center justify-center w-7 h-7 rounded-md transition-all duration-300"
                      :class="auth.id === activeUnit?.id && auth.type === unitStore.activeContextType
                        ? 'bg-primary text-primary-foreground' 
                        : 'bg-muted/80 text-muted-foreground group-hover:bg-primary/10 group-hover:text-primary'"
                    >
                      <LucideIcon
                        :name="auth.icon"
                        class="w-3.5 h-3.5"
                      />
                    </div>
                    
                    <div class="flex flex-col items-start min-w-0 flex-1">
                      <span class="text-[12px] font-bold truncate w-full tracking-tight" :class="auth.id === activeUnit?.id && auth.type === unitStore.activeContextType ? 'text-primary' : 'text-foreground'">
                        {{ auth.name }}
                      </span>
                      <span class="text-[9px] font-bold opacity-60 tracking-tight text-muted-foreground">
                        {{ auth.label }}
                      </span>
                    </div>

                    <div 
                      v-if="auth.id === activeUnit?.id && auth.type === unitStore.activeContextType"
                      class="flex items-center justify-center w-4 h-4 rounded-full text-primary"
                    >
                      <Check class="w-3.5 h-3.5" stroke-width="3" />
                    </div>
                  </button>
                </div>
              </div>

              <!-- SCHOOL UNITS SECTION -->
              <div v-if="filteredLevels.length > 0">
                <div class="px-2.5 mb-1.5 text-[9px] font-bold text-muted-foreground/50 tracking-wider">Unit Operasional</div>
                <div class="space-y-0.5">
                  <button 
                    v-for="lv in filteredLevels" 
                    :key="lv.id" 
                    class="w-full flex items-center gap-3 p-2 rounded-lg transition-all duration-200 group relative border border-transparent"
                    :class="lv.id === activeUnit?.id && unitStore.activeContextType === 'unit'
                      ? 'bg-primary/10 border-primary/10' 
                      : 'hover:bg-muted/60 hover:border-border/40 text-foreground'"
                    @click="lv.id !== undefined && handleSelect(lv.id, 'unit')"
                  >
                    <div 
                      class="flex items-center justify-center w-7 h-7 rounded-md transition-all duration-300"
                      :class="lv.id === activeUnit?.id && unitStore.activeContextType === 'unit'
                        ? 'bg-primary text-primary-foreground' 
                        : 'bg-muted/80 text-muted-foreground group-hover:bg-primary/10 group-hover:text-primary'"
                    >
                      <LucideIcon
                        :name="getUnitIcon(lv.level)"
                        class="w-3.5 h-3.5"
                      />
                    </div>
                    
                    <div class="flex flex-col items-start min-w-0 flex-1">
                      <span class="text-[12px] font-bold truncate w-full tracking-tight" :class="lv.id === activeUnit?.id && String(activeUnit?.id) !== '0' ? 'text-primary' : 'text-foreground'">
                        {{ lv.name }}
                      </span>
                      <span class="text-[9px] font-bold opacity-60 tracking-tight text-muted-foreground">
                        {{ lv.level || 'Unit' }}
                      </span>
                    </div>

                    <div 
                      v-if="lv.id === activeUnit?.id && unitStore.activeContextType === 'unit'"
                      class="flex items-center justify-center w-4 h-4 rounded-full text-primary"
                    >
                      <Check class="w-3.5 h-3.5" stroke-width="3" />
                    </div>
                  </button>
                </div>
              </div>

              <div v-if="filteredLevels.length === 0 && filteredAuthorities.length === 0" class="py-6 text-center space-y-1.5">
                 <div class="w-8 h-8 rounded-full bg-muted/50 mx-auto flex items-center justify-center">
                    <Search class="w-3.5 h-3.5 text-muted-foreground/30" />
                 </div>
                 <p class="text-[10px] font-bold text-muted-foreground italic">Pencarian tidak ditemukan.</p>
              </div>
            </div>

            <div class="p-2.5 border-t border-border/30 bg-muted/5 flex items-center justify-center">
               <div class="text-[8px] font-bold text-muted-foreground/30 tracking-widest">
                  JA-PLATFORM &copy; 2026
               </div>
            </div>
          </PopoverContent>
        </Popover>
        <TooltipContent side="bottom" align="center" class="bg-foreground text-background px-3 py-1.5 rounded-lg shadow-xl border-none z-[70]">
          <p class="text-[9px] font-bold tracking-wider opacity-60 mb-0.5">{{ t('common.labels.activeUnit') }}</p>
          <p class="text-[11px] font-bold">{{ activeUnit?.name }}</p>
        </TooltipContent>
      </Tooltip>
    </TooltipProvider>
  </div>
</template>

<script setup lang="ts">
import { onMounted, computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { 
  Search, Check, LayoutGrid 
} from 'lucide-vue-next';
import {
  Popover, PopoverTrigger, PopoverContent,
  Tooltip, TooltipTrigger, TooltipContent, TooltipProvider,
  LucideIcon
} from '@/shared/components/ui';
import { useUnitStore } from '../stores/unit';
import { useSchoolStore } from '../stores/school';
import { useAuthStore } from '@/modules/System/stores/auth';

const { t } = useI18n();
const router = useRouter();
const unitStore = useUnitStore();
const schoolStore = useSchoolStore();
const authStore = useAuthStore();

const showSelector = ref(false);
const searchQuery = ref('');

const school = computed(() => schoolStore.currentSchool);
const isSuperAdmin = computed(() => authStore.getRoleRank() >= 100);
const isFoundationAdmin = computed(() => authStore.getRoleRank() >= 95 && authStore.getRoleRank() < 100);

const authorities = computed(() => {
  const list = [];
  const schoolType = school.value?.type || 'private';
  
  if (isSuperAdmin.value) {
    list.push({
      id: "0",
      name: t('common.labels.systemAdmin'),
      label: t('common.labels.systemAdminDesc'),
      icon: 'ShieldCheck',
      type: 'system'
    });
  }

  if (isSuperAdmin.value || schoolType === 'public') {
    list.push({
      id: "0",
      name: t('common.labels.authorityAdmin'),
      label: t('common.labels.authorityAdminDesc'),
      icon: 'Building2',
      type: 'authority'
    });
  }

  if (isSuperAdmin.value || (schoolType === 'private' && isFoundationAdmin.value)) {
    list.push({
      id: "0",
      name: t('common.labels.foundationAdmin'),
      label: t('common.labels.foundationAdminDesc'),
      icon: 'Library',
      type: 'foundation'
    });
  }

  return list;
});

const filteredAuthorities = computed(() => {
  if (!searchQuery.value) return authorities.value;
  const q = searchQuery.value.toLowerCase();
  return authorities.value.filter(a => 
    a.name.toLowerCase().includes(q) || 
    a.label.toLowerCase().includes(q)
  );
});

const displayLevels = computed(() => {
  return isFoundationAdmin.value || isSuperAdmin.value 
    ? [...unitStore.levels] 
    : [...(authStore.user?.levels || [])];
});

const filteredLevels = computed(() => {
  if (!searchQuery.value) return displayLevels.value;
  const q = searchQuery.value.toLowerCase();
  return displayLevels.value.filter(lv => 
    lv.name.toLowerCase().includes(q) || 
    (lv.level && lv.level.toLowerCase().includes(q))
  );
});

const activeUnit = computed(() => {
  const rawId = unitStore.activeUnitId;
  const contextType = unitStore.activeContextType;

  if (String(rawId) === '0') {
    let name = t('common.labels.systemAdmin');
    if (contextType === 'foundation') name = t('common.labels.foundationAdmin');
    if (contextType === 'authority') name = t('common.labels.authorityAdmin');
    
    return {
      id: "0",
      name,
      level: contextType
    };
  }
  return unitStore.activeUnit;
});

const canSelectLevel = computed(() => {
  if (isSuperAdmin.value) return true;
  if (!school.value?.is_multi_unit) return false;
  return displayLevels.value.length > 1 || isFoundationAdmin.value;
});

onMounted(async () => {
  if (!schoolStore.currentSchool) {
    await schoolStore.fetchSchool();
  }
  // Rely on AdminLayout for fetchUnits
});

const getUnitIcon = (type: string | undefined) => {
  switch (type?.toLowerCase()) {
    case 'global': return 'ShieldCheck';
    case 'sd': return 'Baby';
    case 'smp': return 'GraduationCap';
    case 'sma': return 'BookOpen';
    case 'smk': return 'Cpu';
    default: return 'School';
  }
};

const resolveContextPath = (id: string, type: 'system' | 'foundation' | 'authority' | 'unit') => {
  if (id === '0') {
    if (type === 'system') return '/dash';
    return '/dash/school';
  }
  return '/dash/school-dashboard';
};

const handleSelect = async (id: string, type: 'system' | 'foundation' | 'authority' | 'unit') => {
  if (unitStore.switchingContext) return;
  if (unitStore.activeUnitId === id && unitStore.activeContextType === type) {
    showSelector.value = false;
    return;
  }

  showSelector.value = false;
  await unitStore.setActiveLevel(id, type, true);

  // Use SPA navigation instead of hard reload.
  const path = resolveContextPath(id, type);
  if (path === '/dash') {
    router.push({ name: 'dashboard' });
  } else if (path === '/dash/school') {
    router.push({ name: 'schools.index' });
  } else {
    router.push({ name: 'schools.dashboard' });
  }
};
</script>
