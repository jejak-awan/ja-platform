<template>
  <div class="flex flex-col lg:flex-row min-h-[600px] bg-background">
    <!-- Sidebar Unit List -->
    <div class="w-full lg:w-80 border-r border-border bg-muted/5 flex flex-col">
      <div class="p-6 border-b border-border bg-background/50 backdrop-blur-sm sticky top-0 z-10">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xs font-bold text-foreground/70 tracking-tight flex items-center gap-2">
            {{ $t('modules.school.labels.unitList') }}
            <Badge
              variant="secondary"
              class="rounded-full px-2 h-4 text-[10px] bg-foreground/5 text-foreground border-none font-bold"
            >
              {{ units.length }}
            </Badge>
          </h3>
          <Button
            v-if="canAddMore"
            variant="ghost"
            size="icon"
            class="h-8 w-8 rounded-lg hover:bg-muted transition-colors"
            @click="$emit('add')"
          >
            <LucideIcon
              name="Plus"
              class="w-4 h-4"
            />
          </Button>
        </div>

        <div class="relative group">
          <LucideIcon
            name="Search"
            class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground group-focus-within:text-foreground transition-colors"
          />
          <Input
            :placeholder="$t('common.placeholders.search')"
            class="pl-9 h-9 text-xs rounded-lg border-border bg-background focus-visible:ring-foreground/5"
          />
        </div>
      </div>

      <div class="flex-1 overflow-y-auto p-4 space-y-1.5 custom-scrollbar">
        <template v-if="units.length > 0">
          <button
            v-for="lv in units"
            :key="lv.id"
            class="w-full group relative flex items-center gap-4 p-3 rounded-xl transition-all duration-200 text-left border border-transparent"
            :class="[
              selectedUnitId === lv.id
                ? 'bg-foreground text-background shadow-none border-transparent z-10'
                : 'hover:bg-muted text-muted-foreground hover:text-foreground'
            ]"
            @click="$emit('select', lv)"
          >
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center transition-colors shadow-none"
              :class="[
                selectedUnitId === lv.id
                  ? 'bg-background/20 text-background'
                  : 'bg-background border border-border/50 group-hover:border-foreground/20'
              ]"
            >
              <LucideIcon
                :name="getUnitIcon(lv.level)"
                class="w-5 h-5"
              />
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-0.5">
                <span class="font-bold text-sm truncate">{{ lv.name }}</span>
                <LucideIcon v-if="lv.settings?.is_default" name="Star" class="w-3 h-3 fill-primary text-primary" />
              </div>
              <div class="flex items-center gap-2">
                <Badge
                  variant="outline"
                  class="text-[9px] uppercase font-black px-1.5 h-4 border-none shadow-none"
                  :class="[
                    selectedUnitId === lv.id ? 'bg-background/20 text-background' : 'bg-muted text-muted-foreground'
                  ]"
                >
                  {{ lv.level }}
                </Badge>
                <span
                  v-if="lv.npsn"
                  class="text-[10px] font-mono opacity-60"
                >{{ lv.npsn }}</span>
              </div>
            </div>
            <div
              v-if="selectedUnitId === lv.id"
              class="absolute right-3"
            >
              <LucideIcon
                name="ChevronRight"
                class="w-4 h-4 opacity-50"
              />
            </div>
          </button>
        </template>

        <div
          v-else
          class="flex flex-col items-center justify-center py-12 px-4 text-center"
        >
          <div class="w-12 h-12 bg-muted rounded-2xl flex items-center justify-center mb-3">
            <LucideIcon
              name="School"
              class="w-5 h-5 text-muted-foreground/30"
            />
          </div>
          <p class="text-xs font-bold text-muted-foreground mb-1">{{ $t('common.labels.noUnitTitle') }}</p>
          <p class="text-[10px] text-muted-foreground/40 leading-relaxed">
            {{ $t('common.labels.noUnitDesc') }}
          </p>
        </div>
      </div>
    </div>

    <!-- Unit Detail Content -->
    <div class="flex-1 bg-card flex flex-col min-w-0">
      <template v-if="selectedUnit">
        <div class="p-8 border-b border-border flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 bg-background/50">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-muted flex items-center justify-center border border-border shadow-none">
              <LucideIcon
                :name="getUnitIcon(selectedUnit.level)"
                class="w-6 h-6 text-foreground/60"
              />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-xl font-bold text-foreground tracking-tight">
                  {{ selectedUnit.name }}
                </h3>
                <Badge
                  variant="outline"
                  class="uppercase text-[10px] font-black px-2 py-0 h-5 border-border bg-muted/30"
                >
                  {{ selectedUnit.level }}
                </Badge>
              </div>
              <p class="text-xs text-muted-foreground mt-1 flex items-center gap-1.5 font-medium">
                <LucideIcon
                  name="MapPin"
                  class="w-3.5 h-3.5 opacity-50"
                />
                {{ selectedUnit.settings?.address || $t('modules.school.messages.addressNotSet') }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3 shrink-0">
            <Button
              v-if="selectedUnit && !selectedUnit.settings?.is_default"
              variant="outline"
              size="sm"
              class="rounded-xl h-10 px-4 font-bold text-xs border-primary/20 text-primary hover:bg-primary/5 transition-all"
              @click="$emit('set-default', selectedUnit)"
            >
              <LucideIcon name="Star" class="w-4 h-4 mr-2" />
              {{ $t('modules.school.labels.markAsDefault') }}
            </Button>
            <Badge
              v-else-if="selectedUnit?.settings?.is_default"
              variant="outline"
              class="h-10 px-4 rounded-xl border-primary/20 bg-primary/5 text-primary font-bold text-xs flex items-center gap-2"
            >
              <LucideIcon name="CheckCircle2" class="w-4 h-4" />
              {{ $t('common.labels.isDefaultUnit') }}
            </Badge>

            <Button
              v-if="isDetailDirty"
              variant="outline"
              size="sm"
              class="rounded-xl h-10 px-4 font-bold text-xs border-border hover:bg-muted transition-all active:scale-95 text-muted-foreground hover:text-foreground"
              @click="handleCancel"
            >
              {{ $t('common.actions.cancel') }}
            </Button>
            <Button
              :variant="isDetailDirty ? 'default' : 'outline'"
              size="sm"
              class="rounded-xl h-10 px-6 font-bold text-xs transition-all active:scale-95 shadow-none"
              :class="[
                isDetailDirty 
                  ? 'bg-foreground text-background hover:bg-foreground/90 border-transparent' 
                  : 'bg-background hover:bg-muted border-border text-foreground/80'
              ]"
              :disabled="saving"
              @click="handleMainAction"
            >
              <LucideIcon
                :name="saving ? 'Loader2' : (isDetailDirty ? 'Save' : 'Pencil')"
                class="w-4 h-4 mr-2"
                :class="{ 'animate-spin': saving }"
              />
              <span class="tracking-tight">
                {{ saving ? (isDetailDirty ? $t('common.actions.saving') : $t('common.actions.loading')) : (isDetailDirty ? $t('common.actions.saveChanges') : $t('common.actions.editProfile')) }}
              </span>
            </Button>
            <Button
              v-if="!isDeleteRestricted"
              variant="outline"
              size="sm"
              class="rounded-xl h-10 px-4 font-bold text-xs text-destructive border-destructive/20 hover:bg-destructive/10 transition-all active:scale-95"
              @click="$emit('delete', selectedUnit)"
            >
              <LucideIcon
                name="Trash2"
                class="w-4 h-4 mr-2"
              />
              {{ $t('common.actions.delete') }}
            </Button>
          </div>
        </div>

        <div class="p-6">
          <UnitDetailContainer
            ref="detailTabsRef"
            :key="selectedUnit.id"
            :level="selectedUnit"
            :school="school"
            :saving="saving"
            :is-super-admin="isSuperAdmin"
            @save="$emit('save-detail')"
            @dirty="val => isDetailDirty = val"
            @update:level="val => $emit('update-unit', val)"
          />
        </div>
      </template>

      <div
        v-else
        class="flex-1 flex flex-col items-center justify-center text-center p-12 bg-muted/5"
      >
        <div class="w-20 h-20 bg-primary/5 text-primary/20 rounded-3xl flex items-center justify-center mb-6 ring-8 ring-primary/[0.02]">
          <LucideIcon
            name="ArrowLeft"
            class="w-10 h-10 animate-bounce-x"
          />
        </div>
        <h3 class="text-xl font-bold text-foreground tracking-tight">{{ $t('common.labels.selectUnitTitle') }}</h3>
        <p class="text-muted-foreground text-sm max-w-xs mx-auto mt-2 leading-relaxed">
          {{ $t('common.labels.selectUnitDesc') }}
        </p>
        <Button
          v-if="canAddMore"
          variant="outline"
          class="mt-8 rounded-xl font-bold"
          @click="$emit('add')"
        >
          <LucideIcon
            name="Plus"
            class="w-4 h-4 mr-2"
          />
          {{ $t('modules.school.units.emptyAction') }}
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Badge, Button, LucideIcon, Input } from '@/shared/components/ui';
import UnitDetailContainer from '../components/unit-detail/UnitDetailContainer.vue';
import type { SchoolUnit } from '@/modules/School/types';

const props = defineProps<{
  units: SchoolUnit[];
  selectedUnitId: string | number | null;
  selectedUnit: SchoolUnit | null;
  school: any;
  canAddMore: boolean;
  saving: boolean;
  isSuperAdmin: boolean;
  isDeleteRestricted: boolean;
}>();

const emit = defineEmits<{
  (e: 'add'): void;
  (e: 'edit', unit: SchoolUnit): void;
  (e: 'delete', unit: SchoolUnit): void;
  (e: 'select', unit: SchoolUnit): void;
  (e: 'save-detail'): void;
  (e: 'update-unit', unit: any): void;
  (e: 'set-default', unit: SchoolUnit): void;
}>();

const isDetailDirty = ref(false);
const detailTabsRef = ref<any>(null);

const handleMainAction = () => {
  if (isDetailDirty.value) {
    emit('save-detail');
  } else {
    emit('edit', props.selectedUnit!);
  }
};

const handleCancel = () => {
  if (detailTabsRef.value) {
    detailTabsRef.value.revertChanges();
    isDetailDirty.value = false;
  }
};

const getUnitIcon = (levelType: string) => {
  switch (levelType?.toLowerCase()) {
    case 'sd': return 'Baby';
    case 'smp': return 'GraduationCap';
    case 'sma': return 'BookOpen';
    case 'smk': return 'Cpu';
    default: return 'School';
  }
};
</script>
