<template>
  <div
    v-if="canSwitchLevel"
    class="relative inline-block text-left"
  >
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button
          variant="outline"
          size="sm"
          class="flex items-center gap-2 px-3 border-primary/20 bg-primary/5 hover:bg-primary/10 text-primary transition-all active:scale-95"
        >
          <LucideIcon
            :name="getUnitIcon(activeUnit?.level)"
            class="w-4 h-4"
          />
          <span class="max-w-[120px] truncate font-semibold">
            {{ activeUnit?.name || $t('common.labels.selectLevel') }}
          </span>
          <LucideIcon
            name="ChevronDown"
            class="w-3 h-3 opacity-50"
          />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent
        align="end"
        class="w-56"
      >
        <DropdownMenuLabel>{{ $t('common.labels.selectUnitLevel') }}</DropdownMenuLabel>
        <DropdownMenuSeparator />
        <DropdownMenuItem 
          v-for="lv in displayLevels" 
          :key="lv.id" 
          class="flex items-center justify-between cursor-pointer"
          :class="{ 'bg-primary/10 text-primary font-bold': lv.id === activeUnit?.id }"
          @click="lv.id && handleSwitch(lv.id)"
        >
          <div class="flex items-center gap-2">
            <LucideIcon
              :name="getUnitIcon(lv.level)"
              class="w-4 h-4"
            />
            <span>{{ lv.name }}</span>
          </div>
          <LucideIcon
            v-if="lv.id === activeUnit?.id"
            name="Check"
            class="w-4 h-4"
          />
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </div>
</template>

<script setup lang="ts">
import { onMounted, computed } from 'vue';
import { useUnitStore } from '../stores/unit';
import { useSchoolStore } from '../stores/school';
import { useAuthStore } from '../../Core/stores/auth';
import {
  DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator,
  Button, LucideIcon
} from '@/components/ui';

const unitStore = useUnitStore();
const schoolStore = useSchoolStore();
const authStore = useAuthStore();

const school = computed(() => schoolStore.schools.length > 0 ? schoolStore.schools[0] : null);
const activeUnit = computed(() => unitStore.activeUnit);

const isFoundationAdmin = computed(() => authStore.getRoleRank() >= 95);

const displayLevels = computed(() => {
  if (isFoundationAdmin.value) {
    return unitStore.levels;
  }
  return authStore.user?.levels || [];
});

const canSwitchLevel = computed(() => {
  // 1. School must be multi-level
  if (!school.value?.is_multi_unit) return false;
  
  // 2. Allowed if Foundation Admin OR User is assigned to multiple levels
  return isFoundationAdmin.value || displayLevels.value.length > 1;
});

onMounted(async () => {
  if (schoolStore.schools.length === 0) {
    await schoolStore.fetchSchools();
  }
  if (school.value) {
    await unitStore.fetchUnits(school.value.id!);
  }
  
  // If user is restricted and current level is not in their assigned list,
  // we should potentially redirect them to their first assigned level.
  if (!isFoundationAdmin.value && authStore.user?.levels?.length) {
    const isAssigned = authStore.user.levels.some(l => l.id === unitStore.activeUnitId);
    if (!isAssigned && unitStore.activeUnitId) {
      const firstLevelId = authStore.user.levels[0]?.id;
      if (firstLevelId) handleSwitch(firstLevelId);
    }
  }
});

const getUnitIcon = (type: string | undefined) => {
  switch (type?.toLowerCase()) {
    case 'sd': return 'Baby';
    case 'smp': return 'GraduationCap';
    case 'sma': return 'BookOpen';
    case 'smk': return 'Cpu';
    default: return 'School';
  }
};

const handleSwitch = (id: number) => {
  unitStore.setActiveLevel(id);
  // Reload page to re-initialize all stores with the new level context
  window.location.reload();
};
</script>
