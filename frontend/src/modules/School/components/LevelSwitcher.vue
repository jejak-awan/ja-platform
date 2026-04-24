<template>
  <div
    v-if="school?.is_multi_level"
    class="relative inline-block text-left"
  >
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button
          variant="outline"
          size="sm"
          class="flex items-center gap-2 px-3 border-primary/20 bg-primary/5 hover:bg-primary/10 text-primary"
        >
          <LucideIcon
            :name="getLevelIcon(activeLevel?.level)"
            class="w-4 h-4"
          />
          <span class="max-w-[120px] truncate font-semibold">
            {{ activeLevel?.name || 'Pilih Jenjang' }}
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
        <DropdownMenuLabel>Pilih Unit / Jenjang</DropdownMenuLabel>
        <DropdownMenuSeparator />
        <DropdownMenuItem 
          v-for="lv in levels" 
          :key="lv.id" 
          class="flex items-center justify-between cursor-pointer"
          :class="{ 'bg-primary/10 text-primary font-bold': lv.id === activeLevel?.id }"
          @click="handleSwitch(lv.id!)"
        >
          <div class="flex items-center gap-2">
            <LucideIcon
              :name="getLevelIcon(lv.level)"
              class="w-4 h-4"
            />
            <span>{{ lv.name }}</span>
          </div>
          <LucideIcon
            v-if="lv.id === activeLevel?.id"
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
import { useLevelStore } from '../stores/level';
import { useSchoolStore } from '../stores/school';
import {
  DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator,
  Button, LucideIcon
} from '@/components/ui';

const levelStore = useLevelStore();
const schoolStore = useSchoolStore();

const school = computed(() => schoolStore.schools.length > 0 ? schoolStore.schools[0] : null);
const levels = computed(() => levelStore.levels);
const activeLevel = computed(() => levelStore.activeLevel);

onMounted(async () => {
  if (schoolStore.schools.length === 0) {
    await schoolStore.fetchSchools();
  }
  if (school.value) {
    await levelStore.fetchLevels(school.value.id!);
  }
});

const getLevelIcon = (type: string | undefined) => {
  switch (type) {
    case 'sd': return 'Baby';
    case 'smp': return 'GraduationCap';
    case 'sma': return 'BookOpen';
    case 'smk': return 'Cpu';
    default: return 'School';
  }
};

const handleSwitch = (id: number) => {
  levelStore.setActiveLevel(id);
  // Reload page or trigger global refetch to apply new header context
  window.location.reload();
};
</script>
