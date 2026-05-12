<template>
  <div class="pb-8">
    <div class="px-6 space-y-8">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 animate-in fade-in duration-500">
        <div>
          <div class="flex items-center gap-4 mb-2">
            <div class="w-12 h-12 rounded-xl bg-muted flex items-center justify-center border border-border shadow-none transition-colors">
              <LucideIcon
                name="Building2"
                class="w-6 h-6 text-foreground/70"
              />
            </div>
            <div>
              <h1 class="text-2xl font-bold tracking-tight text-foreground">
                {{ $t('modules.school.title') }}
              </h1>
              <p class="text-muted-foreground text-xs font-medium">
                {{ $t('modules.school.subtitle') }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div
        v-if="loading"
        class="flex flex-col items-center justify-center p-20 gap-4"
      >
        <div class="w-10 h-10 border-2 border-primary/20 border-t-primary rounded-full animate-spin" />
        <p class="text-muted-foreground animate-pulse text-sm">Memuat data lembaga...</p>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="!school"
        class="p-16 border border-dashed rounded-2xl text-center space-y-6 bg-muted/20 border-border animate-in fade-in duration-500"
      >
        <div class="p-5 bg-muted text-foreground/40 rounded-full w-fit mx-auto border border-border shadow-none">
          <LucideIcon
            name="Building"
            class="w-10 h-10"
          />
        </div>
        <div class="space-y-2">
          <h3 class="text-xl font-bold text-foreground tracking-tight">
            {{ $t('modules.school.institution.empty') }}
          </h3>
          <p class="text-muted-foreground text-sm max-w-sm mx-auto leading-relaxed">
            {{ $t('modules.school.institution.emptyDescription') }}
          </p>
        </div>
        <Button
          v-if="isGlobalAdmin"
          as-child
          class="rounded-xl px-8 h-12 font-bold shadow-none text-sm bg-foreground text-background hover:bg-foreground/90"
        >
          <router-link :to="{ name: 'schools.create' }">
            <LucideIcon
              name="Plus"
              class="w-4 h-4 mr-2"
            />
            {{ $t('modules.school.institution.setupNow') }}
          </router-link>
        </Button>
      </div>

      <!-- Main Content -->
      <div
        v-else
        class="space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-500"
      >
        <!-- Institution Header Card -->
        <Card class="overflow-hidden border-border bg-card shadow-none rounded-2xl">
          <div class="p-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 text-left">
            <div class="flex items-center gap-6">
              <div class="w-20 h-20 rounded-xl bg-muted flex items-center justify-center border border-border overflow-hidden">
                <img
                  v-if="school.logo"
                  :src="school.logo"
                  class="w-full h-full object-cover"
                  :alt="school.name"
                />
                <LucideIcon
                  v-else
                  :name="getSchoolIcon(school.type || '')"
                  class="w-10 h-10 text-muted-foreground/30"
                />
              </div>
              <div>
                <h2 class="text-2xl font-bold text-foreground tracking-tight">
                  {{ school.name }}
                </h2>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                  <Badge
                    variant="outline"
                    class="bg-muted/50 text-foreground border-border font-bold text-[10px] px-3 py-0.5 rounded-lg"
                  >
                    {{ $t(`modules.school.institution.status.${school.type?.toLowerCase() || 'undefined'}`) }}
                  </Badge>
                  <Badge
                    variant="outline"
                    class="bg-muted/30 text-muted-foreground border-border/60 font-medium text-[10px] px-3 py-0.5 rounded-lg"
                  >
                    {{ isMultiLevel ? $t('common.labels.multi_level') : $t('modules.school.labels.single_level') }}
                  </Badge>
                  <Badge
                    variant="outline"
                    class="bg-muted/30 text-muted-foreground border-border/60 font-medium text-[10px] px-3 py-0.5 rounded-lg"
                  >
                    {{ school.is_multi_branch ? $t('modules.school.wizard.options.multiLocation') : $t('modules.school.wizard.options.singleLocation') }}
                  </Badge>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <Button
                v-if="!isIdentityEditRestricted"
                as-child
                variant="outline"
                class="rounded-xl h-10 px-6 font-bold text-xs shadow-none bg-background hover:bg-muted transition-all active:scale-95 border-border"
              >
                <router-link
                  :to="{ name: 'schools.edit', params: { id: school.id } }"
                  class="flex items-center"
                >
                  <LucideIcon
                    name="Pencil"
                    class="w-4 h-4 mr-2"
                  />
                  {{ $t('common.actions.editProfile') }}
                </router-link>
              </Button>
              <Button
                v-if="isGlobalAdmin"
                variant="outline"
                class="rounded-xl h-10 px-4 font-bold text-xs text-destructive border-destructive/20 hover:bg-destructive/10 transition-all active:scale-95"
                @click="confirmDelete"
              >
                <LucideIcon
                  name="Trash2"
                  class="w-4 h-4 mr-2"
                />
                {{ $t('common.actions.delete') }}
              </Button>
            </div>
          </div>

          <!-- Navigation Tabs -->
          <Tabs
            v-model="activeTab"
            class="w-full"
          >
            <TabsList class="w-full h-12 bg-muted/20 p-0 border-y border-border rounded-none justify-start px-8 gap-6">
              <TabsTrigger
                value="identity"
                class="h-12 px-2 bg-transparent border-b-2 border-transparent data-[state=active]:border-foreground data-[state=active]:bg-transparent data-[state=active]:text-foreground rounded-none font-bold text-xs transition-all shadow-none"
              >
                <LucideIcon
                  name="LayoutDashboard"
                  class="w-3.5 h-3.5 mr-2"
                />
                <template v-if="!isMultiLevel">
                  {{ $t('modules.school.labels.schoolProfile') }}
                </template>
                <template v-else>
                  {{ unitStore.activeUnitId === 0 ? $t('common.labels.identity') : $t('common.labels.unitProfile') }}
                </template>
              </TabsTrigger>
              <TabsTrigger
                v-if="showLevelsTab && isGlobalAdmin && (unitStore.activeUnitId === 0 || !isMultiLevel)"
                value="levels"
                class="h-12 px-2 bg-transparent border-b-2 border-transparent data-[state=active]:border-foreground data-[state=active]:bg-transparent data-[state=active]:text-foreground rounded-none font-bold text-xs transition-all shadow-none"
              >
                <LucideIcon
                  name="Settings2"
                  class="w-3.5 h-3.5 mr-2"
                />
                {{ isMultiLevel ? $t('modules.school.units.title') : $t('modules.school.labels.unitTechnicalDetail') }}
                <Badge
                  v-if="isMultiLevel"
                  variant="secondary"
                  class="ml-2 rounded-full px-1.5 h-4 text-[10px] bg-foreground/5 text-foreground border-none font-black"
                >
                  {{ levels.length }}
                </Badge>
              </TabsTrigger>
            </TabsList>

            <!-- TAB: IDENTITAS -->
            <TabsContent
              value="identity"
              class="mt-0"
            >
              <IdentityTab
                :form="identityForm"
                :is-restricted="isIdentityEditRestricted"
                :levels="levels"
                @save-school="handleUpdateSchool"
              />
            </TabsContent>

            <!-- TAB: UNIT JENJANG / DETAIL TEKNIS -->
            <TabsContent
              v-if="showLevelsTab && isGlobalAdmin && (unitStore.activeUnitId === 0 || !isMultiLevel)"
              value="levels"
              class="mt-0"
            >
              <UnitTab
                :units="levels"
                :selected-unit-id="selectedUnitId"
                :selected-unit="selectedUnit"
                :school="school"
                :can-add-more="canAddMoreLevels"
                :saving="savingUnit"
                :is-super-admin="isGlobalAdmin"
                :is-delete-restricted="isLevelDeleteRestricted"
                @add="handleAddLevel"
                @edit="handleEditLevel"
                @delete="confirmDeleteLevel"
                @select="lv => selectedUnitId = lv.id!"
                @save-detail="saveLevelData"
                @update-unit="handleLevelUpdate"
                @set-default="handleSetDefaultUnit"
              />
            </TabsContent>
          </Tabs>
        </Card>
      </div>
    </div>

    <!-- Modals -->
    <UnitDialog
      v-model:show="showUnitDialog"
      v-model:form="unitForm"
      :editing-id="editingUnitId"
      :is-multi-branch="school?.is_multi_branch"
      :is-multi-level="school?.is_multi_unit"
      :school-type="school?.type"
      :school-name="school?.name"
      @save="handleSaveLevel"
    />

    <!-- Institution Delete Confirmation -->
    <Dialog v-model:open="showDeleteConfirm">
      <DialogContent class="sm:max-w-[500px] !z-[100050] p-0 overflow-hidden border-none shadow-2xl">
        <DialogHeader class="bg-destructive/5 p-8 text-center border-b border-destructive/10">
          <div class="w-16 h-16 bg-destructive/10 text-destructive rounded-full flex items-center justify-center mx-auto mb-4 ring-8 ring-destructive/5">
            <LucideIcon
              name="AlertTriangle"
              class="w-8 h-8"
            />
          </div>
          <DialogTitle class="text-2xl font-bold text-foreground mb-2">
            Hapus Lembaga?
          </DialogTitle>
          <DialogDescription class="text-muted-foreground leading-relaxed">
            Tindakan ini permanen. Semua data unit jenjang, akademik, dan administratif di bawah lembaga ini akan ikut terhapus.
          </DialogDescription>
        </DialogHeader>

        <div class="p-8 space-y-6 text-left">
          <div class="space-y-3">
            <Label class="text-sm font-bold text-foreground">
              Ketik nama lembaga di bawah untuk konfirmasi:
            </Label>
            <div class="bg-muted/50 p-3 rounded-lg border border-border/50 font-mono text-sm text-center font-bold text-destructive">
              {{ school?.name }}
            </div>
            <Input 
              v-model="deleteConfirmName"
              placeholder="Masukkan nama lembaga"
              class="h-12 rounded-xl border-destructive/20 focus:ring-destructive/20 focus:border-destructive/30"
            />
          </div>

          <DialogFooter class="flex flex-col sm:flex-row gap-3 sm:gap-0">
            <Button variant="ghost" class="rounded-xl h-12 font-bold" @click="showDeleteConfirm = false">Batal</Button>
            <Button 
              class="rounded-xl h-12 font-bold px-10 bg-destructive hover:bg-destructive/90 shadow-lg shadow-destructive/20"
              :disabled="deleteConfirmName !== school?.name || isDeleting"
              @click="executeDelete"
            >
              <LucideIcon v-if="isDeleting" name="Loader2" class="w-4 h-4 mr-2 animate-spin" />
              <LucideIcon v-else name="Trash2" class="w-4 h-4 mr-2" />
              {{ isDeleting ? 'Menghapus...' : 'Hapus Sekarang' }}
            </Button>
          </DialogFooter>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Unit Delete Confirmation -->
    <Dialog v-model:open="showLevelDeleteConfirm">
      <DialogContent class="sm:max-w-[500px] !z-[100050] p-0 overflow-hidden border-none shadow-2xl">
        <DialogHeader class="bg-destructive/5 p-8 text-center border-b border-destructive/10">
          <div class="w-16 h-16 bg-destructive/10 text-destructive rounded-full flex items-center justify-center mx-auto mb-4 ring-8 ring-destructive/5">
            <LucideIcon name="AlertTriangle" class="w-8 h-8" />
          </div>
          <DialogTitle class="text-2xl font-bold text-foreground mb-2">Hapus Unit?</DialogTitle>
          <DialogDescription class="text-muted-foreground leading-relaxed">
            Menghapus unit <strong>{{ unitToDelete?.name }}</strong> akan menghapus seluruh data akademik di unit tersebut.
          </DialogDescription>
        </DialogHeader>

        <div class="p-8 space-y-6 text-left">
          <div class="space-y-3">
            <Label class="text-sm font-bold text-foreground">Ketik nama unit di bawah untuk konfirmasi:</Label>
            <div class="bg-muted/50 p-3 rounded-lg border border-border/50 font-mono text-sm text-center font-bold text-destructive">
              {{ unitToDelete?.name }}
            </div>
            <Input 
              v-model="levelDeleteConfirmName"
              placeholder="Masukkan nama unit"
              class="h-12 rounded-xl border-destructive/20 focus:ring-destructive/20 focus:border-destructive/30"
            />
          </div>

          <DialogFooter class="flex flex-col sm:flex-row gap-3 sm:gap-0">
            <Button variant="ghost" class="rounded-xl h-12 font-bold" @click="showLevelDeleteConfirm = false">Batal</Button>
            <Button 
              class="rounded-xl h-12 font-bold px-10 bg-destructive hover:bg-destructive/90 shadow-lg shadow-destructive/20"
              :disabled="levelDeleteConfirmName !== unitToDelete?.name || isDeletingLevel"
              @click="executeDeleteLevel"
            >
              <LucideIcon v-if="isDeletingLevel" name="Loader2" class="w-4 h-4 mr-2 animate-spin" />
              <LucideIcon v-else name="Trash2" class="w-4 h-4 mr-2" />
              {{ isDeletingLevel ? 'Menghapus...' : 'Hapus Unit' }}
            </Button>
          </DialogFooter>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useSchoolStore } from '../../../stores/school';
import { useUnitStore } from '../../../stores/unit';
import type { SchoolUnit } from '@/modules/School/types';
import { useAuthStore } from '@/modules/Core/stores/auth';
import { useToast } from '@/shared/composables/useToast';
import {
  Input, Label, Tabs, TabsList, TabsTrigger, TabsContent,
  Card, Badge, Button, LucideIcon,
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter
} from '@/shared/components/ui';

import IdentityTab from './parts/IdentityTab.vue';
import UnitTab from './parts/UnitTab.vue';
import UnitDialog from './components/dialogs/UnitDialog.vue';

const schoolStore = useSchoolStore();
const unitStore = useUnitStore();
const authStore = useAuthStore();
const toast = useToast();
const { t } = useI18n();

const loading = ref(true);
const isGlobalAdmin = computed(() => authStore.getRoleRank() >= 95);
const isLevelDeleteRestricted = computed(() => {
  if (isGlobalAdmin.value) return false;
  if (school.value?.is_multi_unit) return false;
  return school.value?.type === 'public' || school.value?.type === 'private';
});
const isIdentityEditRestricted = computed(() => {
  if (isGlobalAdmin.value) return false;
  return identityForm.value.type === 'negeri' || identityForm.value.type === 'swasta';
});
const activeTab = ref('identity');
const savingUnit = ref(false);
const showUnitDialog = ref(false);
const editingUnitId = ref<number | null>(null);
const selectedUnitId = ref<number | null>(null);
const showDeleteConfirm = ref(false);
const deleteConfirmName = ref('');
const isDeleting = ref(false);

const showLevelDeleteConfirm = ref(false);
const levelDeleteConfirmName = ref('');
const isDeletingLevel = ref(false);
const unitToDelete = ref<SchoolUnit | null>(null);

const school = computed(() => schoolStore.schools.length > 0 ? schoolStore.schools[0] : null);
const levels = computed(() => unitStore.levels);
const selectedUnit = computed(() => levels.value.find(l => l.id === selectedUnitId.value) || null);

const showLevelsTab = computed(() => {
  return !!school.value;
});

const isMultiLevel = computed(() => {
  if (!school.value) return false;
  return String(school.value.is_multi_unit) === 'true' || 
         String(school.value.is_multi_unit) === '1';
});

const canAddMoreLevels = computed(() => {
  if (!school.value) return false;
  if (isMultiLevel.value) return true;
  return levels.value.length === 0;
});

const identityForm = ref({
  id: undefined as number | undefined,
  name: '',
  type: '',
  is_multi_unit: false,
  is_multi_branch: false,
  npsn: '',
  nss: '',
  nds: '',
  status_kepemilikan: '',
});

const unitForm = ref<any>({
  level: 'smk',
  name: '',
  npsn: '',
  school_id: 1,
  settings: {
    accreditation: 'A'
  },
});

onMounted(async () => {
  loading.value = true;
  try {
    // Initial data should already be loaded by AdminLayout.
    // We just need to ensure the local form is populated.
    if (school.value) {
      Object.assign(identityForm.value, school.value);
      const firstLevel = levels.value[0];
      if (firstLevel?.id != null) {
        selectedUnitId.value = firstLevel.id;
      }
    } else {
        // Fallback for direct deep links if AdminLayout hasn't finished
        await schoolStore.fetchSchools();
        // Re-read from the store directly to avoid TS narrowing from the outer `if`
        const freshSchool = schoolStore.schools.length > 0 ? schoolStore.schools[0] : null;
        if (freshSchool) {
            Object.assign(identityForm.value, freshSchool);
            await unitStore.fetchUnits(freshSchool.id);
            const firstLevel = levels.value[0];
            if (firstLevel?.id != null) {
                selectedUnitId.value = firstLevel.id;
            }
        }
    }
  } finally {
    loading.value = false;
  }
});

watch(() => school.value, (newSchool) => {
  if (newSchool) {
    Object.assign(identityForm.value, newSchool);
  }
}, { deep: true });

watch(() => identityForm.value.type, (newType) => {
  if (newType === 'public' || newType === 'negeri') {
    identityForm.value.status_kepemilikan = 'government';
  } else if (newType === 'private' || newType === 'yayasan' || newType === 'swasta') {
    identityForm.value.status_kepemilikan = 'foundation';
  }
}, { immediate: true });

const getSchoolIcon = (type: string) => {
  switch (type?.toLowerCase()) {
    case 'public':
    case 'negeri': return 'ShieldCheck';
    case 'yayasan': return 'Component';
    default: return 'Building';
  }
};

const handleAddLevel = () => {
  editingUnitId.value = null;
  unitForm.value = {
    level: 'smk',
    name: '',
    npsn: '',
    school_id: school.value?.id || 1,
    settings: {
      use_primary_address: true,
      accreditation: 'A'
    },
  };
  showUnitDialog.value = true;
};

const handleEditLevel = (lv: SchoolUnit) => {
  editingUnitId.value = lv.id!;
  unitForm.value = {
    ...lv,
    settings: { ...lv.settings }
  };
  showUnitDialog.value = true;
};

const closeUnitDialog = () => {
  showUnitDialog.value = false;
  editingUnitId.value = null;
  unitForm.value.name = '';
  unitForm.value.npsn = '';
};

const handleSaveLevel = async () => {
  if (!unitForm.value.name) {
    toast.error.action(t('modules.school.units.errors.nameRequired'));
    return;
  }
  try {
    const schoolId = school.value?.id ?? 1;
    if (editingUnitId.value) {
      await unitStore.updateUnit(editingUnitId.value, { ...unitForm.value, school_id: schoolId } as any);
      toast.success.save();
    } else {
      unitForm.value.school_id = schoolId;
      await unitStore.createUnit(unitForm.value as any);
      toast.success.save();
    }
    await unitStore.fetchUnits(schoolId);
    closeUnitDialog();
    if (levels.value[0]?.id != null) selectedUnitId.value = levels.value[0].id;
  } catch (e: any) {
    toast.error.fromResponse(e);
  }
};

const handleLevelUpdate = (updatedLevel: any) => {
  const index = unitStore.levels.findIndex(l => l.id === updatedLevel.id);
  if (index !== -1) unitStore.levels[index] = updatedLevel;
};

const handleSetDefaultUnit = async (unit: SchoolUnit) => {
  try {
    savingUnit.value = true;
    
    // 1. Prepare updates for all levels to clear is_default
    // and set is_default for the chosen one
    const updatePromises = levels.value.map(l => {
      const isTarget = l.id === unit.id;
      const currentIsDefault = !!l.settings?.is_default;
      
      // Only update if state changes
      if (isTarget !== currentIsDefault) {
        const updatedSettings = { 
          ...(l.settings || {}), 
          is_default: isTarget 
        };
        return unitStore.updateUnit(l.id!, { settings: updatedSettings });
      }
      return null;
    }).filter(p => p !== null);

    if (updatePromises.length > 0) {
      await Promise.all(updatePromises);
      toast.success.save();
    }
  } catch (e: any) {
    toast.error.fromResponse(e);
  } finally {
    savingUnit.value = false;
  }
};

const saveLevelData = async () => {
  if (!selectedUnitId.value || !selectedUnit.value) return;
  savingUnit.value = true;
  try {
    await unitStore.updateUnit(selectedUnitId.value, { ...selectedUnit.value, school_id: school.value?.id });
    toast.success.action(t('modules.school.units.updateSuccess'));
  } catch (e: any) {
    toast.error.fromResponse(e);
  } finally {
    savingUnit.value = false;
  }
};

const handleUpdateSchool = async (updatedForm: any) => {
  try {
    await schoolStore.updateSchool(updatedForm.id, updatedForm);
    toast.success.save();
    Object.assign(identityForm.value, updatedForm);
  } catch (e: any) {
    toast.error.fromResponse(e);
  }
};

const confirmDelete = () => {
  if (school.value?.id) {
    deleteConfirmName.value = '';
    showDeleteConfirm.value = true;
  }
};

const executeDelete = async () => {
  if (!school.value?.id || deleteConfirmName.value !== school.value.name) return;
  isDeleting.value = true;
  try {
    await schoolStore.deleteSchool(school.value.id);
    toast.success.action(t('modules.school.institution.deleteSuccess'));
    showDeleteConfirm.value = false;
    await schoolStore.fetchSchools();
  } catch (e: any) {
    toast.error.fromResponse(e);
  } finally {
    isDeleting.value = false;
  }
};

const confirmDeleteLevel = (lv: SchoolUnit) => {
  unitToDelete.value = lv;
  levelDeleteConfirmName.value = '';
  showLevelDeleteConfirm.value = true;
};

const executeDeleteLevel = async () => {
  if (!unitToDelete.value || levelDeleteConfirmName.value !== unitToDelete.value.name) return;
  isDeletingLevel.value = true;
  try {
    const deletedId = unitToDelete.value.id!;
    await unitStore.deleteUnit(deletedId);
    toast.success.delete();
    await unitStore.fetchUnits(school.value?.id);
    if (selectedUnitId.value === deletedId) selectedUnitId.value = levels.value[0]?.id ?? null;
    showLevelDeleteConfirm.value = false;
  } catch (e: any) {
    toast.error.fromResponse(e);
  } finally {
    isDeletingLevel.value = false;
    unitToDelete.value = null;
  }
};
</script>
