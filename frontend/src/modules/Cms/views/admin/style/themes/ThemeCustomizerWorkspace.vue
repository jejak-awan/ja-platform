<template>
  <div class="h-[calc(100vh-theme(spacing.16))] flex flex-col bg-background text-foreground select-none">
    <!-- Header -->
    <header class="flex items-center justify-between border-b border-border px-6 py-3 bg-card shrink-0 shadow-sm z-20">
      <div class="flex items-center gap-4">
        <Button
          variant="ghost"
          size="icon"
          :title="t('features.theme_customizer.actions.back_tooltip')"
          @click="handleBack"
        >
          <ArrowLeft class="w-5 h-5" />
        </Button>
        <div>
          <h1 class="text-lg font-bold tracking-tight">
            {{ t('features.theme_customizer.title') }}
          </h1>
          <p class="text-[10px] text-muted-foreground font-bold tracking-wider">
            {{ theme?.name || t('common.labels.loading') }}
          </p>
        </div>
      </div>
            
      <div class="flex items-center gap-4">
        <!-- History Controls -->
        <div class="flex items-center border rounded-md bg-background overflow-hidden p-0.5 shadow-sm">
          <button 
            :disabled="!canUndo" 
            class="p-1.5 hover:bg-muted text-muted-foreground hover:text-foreground disabled:opacity-20 transition-colors"
            :title="t('features.theme_customizer.actions.undo')"
            @click="undo"
          >
            <Undo2 class="w-4 h-4" />
          </button>
          <div class="w-px h-4 bg-border mx-0.5" />
          <button 
            :disabled="!canRedo" 
            class="p-1.5 hover:bg-muted text-muted-foreground hover:text-foreground disabled:opacity-20 transition-colors"
            :title="t('features.theme_customizer.actions.redo')"
            @click="redo"
          >
            <Redo2 class="w-4 h-4" />
          </button>
        </div>

        <div class="h-6 w-px bg-border" />

        <div class="hidden lg:flex items-center gap-1 rounded-md border bg-background p-1">
          <button
            class="px-2.5 py-1.5 text-xs font-semibold rounded transition-colors"
            :class="workspaceMode === 'design' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground hover:bg-muted'"
            @click="workspaceMode = 'design'"
          >
            Design
          </button>
          <button
            class="px-2.5 py-1.5 text-xs font-semibold rounded transition-colors"
            :class="workspaceMode === 'bindings' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground hover:bg-muted'"
            @click="workspaceMode = 'bindings'"
          >
            Bindings
          </button>
          <button
            class="px-2.5 py-1.5 text-xs font-semibold rounded transition-colors"
            :class="workspaceMode === 'advanced' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground hover:bg-muted'"
            @click="workspaceMode = 'advanced'"
          >
            Advanced
          </button>
        </div>

        <Button
          variant="outline"
          size="sm"
          class="hidden xl:inline-flex"
          @click="showPreview = true"
        >
          Show Preview
        </Button>

        <div class="flex items-center gap-2">
          <span
            v-if="isDirty"
            class="flex items-center gap-1.5 px-2 py-1 rounded-full bg-amber-500/10 text-amber-500 text-[10px] font-bold animate-pulse"
          >
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500" />
            {{ t('features.theme_customizer.status.unsaved') }}
          </span>
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button
                variant="outline"
                size="sm"
                :disabled="!isDirty"
              >
                <RotateCcw class="w-3.5 h-3.5 mr-1.5" />
                {{ t('features.theme_customizer.actions.revert') }}
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent
              align="end"
              class="w-56"
            >
              <DropdownMenuItem @click="resetToInitial">
                <History class="w-4 h-4 mr-2" />
                {{ t('features.theme_customizer.revert.session_start') }}
              </DropdownMenuItem>
              <DropdownMenuItem
                class="text-destructive"
                @click="resetToDefaults"
              >
                <Zap class="w-4 h-4 mr-2" />
                {{ t('features.theme_customizer.revert.theme_defaults') }}
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
          <Button
            size="sm"
            :disabled="saving || !isDirty"
            class="shadow-lg shadow-primary/20 bg-primary hover:bg-primary/90"
            @click="saveAll"
          >
            <Save
              v-if="!saving"
              class="w-3.5 h-3.5 mr-1.5"
            />
            <Loader2
              v-else
              class="w-3.5 h-3.5 mr-1.5 animate-spin"
            />
            {{ saving ? t('features.theme_customizer.status.saving') : t('features.theme_customizer.actions.publish') }}
          </Button>
        </div>
      </div>
    </header>

    <!-- Main Workspace -->
    <div
      v-if="loading"
      class="flex-1 flex items-center justify-center bg-muted/5"
    >
      <div class="flex flex-col items-center gap-4">
        <div class="relative w-12 h-12">
          <div class="absolute inset-0 rounded-full border-4 border-primary/20" />
          <div class="absolute inset-0 rounded-full border-4 border-primary border-t-transparent animate-spin" />
        </div>
        <span class="text-sm font-medium animate-pulse text-muted-foreground">{{ t('features.theme_customizer.status.initializing') }}</span>
      </div>
    </div>

    <div
      v-else
      class="flex-1 flex overflow-hidden"
    >
      <!-- Sidebar -->
      <CustomizerSidebar
        :groups="filteredGroups"
        :flat-items="flatNavItems"
        :active-item-id="activeItemId"
        :collapsed-groups="collapsedGroups"
        :search-query="searchQuery"
        :sidebar-collapsed="sidebarCollapsed"
        @select-item="selectItem"
        @toggle-group="toggleGroup"
        @update:search-query="searchQuery = $event"
        @update:sidebar-collapsed="sidebarCollapsed = $event"
      />

      <!-- Main Editor Area -->
      <main class="flex-1 overflow-y-auto relative bg-muted/5 custom-scrollbar">
        <div
          v-if="selectedItem"
          class="max-w-4xl mx-auto p-10 space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-300"
        >
          <!-- Section Header -->
          <div class="flex items-start justify-between">
            <div class="space-y-1">
              <div class="flex items-center gap-3">
                <div class="p-2.5 bg-primary/10 rounded-xl text-primary">
                  <component
                    :is="selectedItem.icon"
                    class="w-7 h-7"
                  />
                </div>
                <div>
                  <h2 class="text-3xl font-black tracking-tight text-foreground">
                    {{ selectedItem.label }}
                  </h2>
                  <p class="text-muted-foreground text-sm font-medium">
                    {{ selectedItem.description }}
                  </p>
                </div>
              </div>
            </div>
            <div class="flex flex-col items-end gap-2">
              <div class="flex items-center gap-1.5 px-3 py-1 bg-background border rounded-full text-[10px] font-bold text-muted-foreground shadow-sm">
                <Layout class="w-3 h-3" />
                {{ activeGroupLabel }}
              </div>
            </div>
          </div>

          <!-- ════════ COMBINED EDITOR ════════ -->
          <div class="space-y-8 pb-20">
            <div
              v-if="!isItemCompatibleWithMode(selectedItem)"
              class="rounded-xl border border-dashed border-border bg-card/60 p-5 text-sm text-muted-foreground"
            >
              {{ modeHintText }}
            </div>

            <!-- Type 1: Static CSS Editor (Direct textarea) -->
            <div
              v-if="selectedItem.id === 'styling-css' && workspaceMode === 'advanced'"
              class="relative"
            >
              <div class="absolute top-4 right-4 z-10 flex items-center gap-2">
                <span class="text-[10px] px-2 py-0.5 rounded bg-muted font-mono text-muted-foreground">{{ t('features.theme_customizer.editor.css.label') }}</span>
              </div>
              <div class="bg-card border-border border-2 rounded-2xl overflow-hidden shadow-2xl shadow-primary/5">
                <textarea
                  v-model="customCss"
                  rows="24"
                  class="w-full p-6 bg-background text-sm font-mono leading-relaxed focus:outline-none resize-none min-h-[500px] border-0 custom-scrollbar selection:bg-primary/20"
                  :placeholder="t('features.theme_customizer.editor.css.placeholder')"
                  spellcheck="false"
                />
              </div>
            </div>

            <!-- Type 2: Manifest-Driven Settings (Cards) -->
            <section
              v-if="selectedItem.manifestSections?.length && workspaceMode === 'design'"
              class="space-y-4"
            >
              <h4 class="text-[11px] font-bold tracking-wider text-muted-foreground/80 flex items-center gap-2 px-1">
                <Settings2 class="w-3 h-3" />
                {{ t('features.theme_customizer.editor.sections.visual') }}
              </h4>
              <div class="bg-card border-border border-2 rounded-2xl p-8 shadow-2xl shadow-primary/5 space-y-8">
                <div
                  v-for="section in selectedItem.manifestSections"
                  :key="section.id"
                  class="space-y-6"
                >
                  <SettingControl
                    v-for="setting in getVisibleSettings(section.settings)"
                    :key="setting.key"
                    :setting="setting"
                    :model-value="formValues[setting.key]"
                    @update:model-value="(val: any) => recordSettingChange(setting.key, val)"
                    @pick-media="openMediaPicker(setting.key)"
                  />
                </div>
              </div>
            </section>

            <!-- Type 3: Menu Locations -->
            <section
              v-if="selectedItem.id === 'identity-menus' && workspaceMode === 'design'"
              class="space-y-4"
            >
              <h4 class="text-[11px] font-bold tracking-wider text-muted-foreground/80 flex items-center gap-2 px-1">
                <MenuIcon class="w-3 h-3" />
                {{ t('features.theme_customizer.editor.sections.menus') }}
              </h4>
              <div class="bg-card border-border border-2 rounded-2xl p-8 shadow-2xl shadow-primary/5 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div
                  v-for="menuSetting in menuSections"
                  :key="menuSetting.key"
                  class="space-y-3 p-4 bg-muted/20 rounded-xl border border-border/50 transition-all hover:border-primary/30"
                >
                  <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-foreground/80 tracking-wide">{{ menuSetting.label }}</label>
                    <div
                      class="w-1.5 h-1.5 rounded-full"
                      :class="formValues[menuSetting.key] && formValues[menuSetting.key] !== 'none' ? 'bg-primary' : 'bg-muted-foreground/30'"
                    />
                  </div>
                  <Select
                    :model-value="String(formValues[menuSetting.key] || 'none')"
                    @update:model-value="(val: string) => recordSettingChange(menuSetting.key, val)"
                  >
                    <SelectTrigger class="w-full bg-background border-border/50">
                      <SelectValue :placeholder="t('features.theme_customizer.editor.menus.placeholder')" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem
                        v-for="opt in (menuSetting.options || [])"
                        :key="String(opt.value)"
                        :value="String(opt.value)"
                      >
                        {{ opt.label }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                  <p class="text-[10px] text-muted-foreground italic">
                    {{ menuSetting.description }}
                  </p>
                </div>
              </div>
            </section>

            <BindingsSection
              :selected-item="selectedItem"
              :workspace-mode="workspaceMode"
              :expanded-slots="expandedSlots"
              :active-binding-component-id="activeBindingComponentId"
              :categories="categories"
              :pages="pages"
              :preview-loading="previewLoading"
              :preview-results="previewResults"
              :toggle-slot="toggleSlot"
              :get-slot-config="getSlotConfig"
              :get-source-label="getSourceLabel"
              :get-source-icon="getSourceIcon"
              :update-binding="updateBinding"
              :get-fields-for-source="getFieldsForSource"
              :filter-preview-fields="filterPreviewFields"
              :preview-slot-data="previewSlotData"
              :save-history="saveHistory"
              @clear-preview="id => delete previewResults[id]"
            />
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-else
          class="h-full flex flex-col items-center justify-center text-muted-foreground p-12 text-center animate-in zoom-in-95 duration-500"
        >
          <div class="relative mb-8">
            <div class="absolute inset-[-40px] bg-primary/10 rounded-full blur-3xl animate-pulse" />
            <LayoutTemplate class="w-24 h-24 mb-4 relative opacity-40 text-primary" />
          </div>
          <h3 class="text-2xl font-black text-foreground tracking-tight">
            {{ t('features.theme_customizer.empty_state.title') }}
          </h3>
          <p class="text-sm mt-3 max-w-sm font-medium">
            {{ t('features.theme_customizer.empty_state.description') }}
          </p>
        </div>
      </main>
</div>

    <Dialog
      :open="showPreview"
      @update:open="(open) => showPreview = open"
    >
      <DialogContent class="w-[96vw] max-w-[1400px] h-[90vh] p-0 overflow-hidden">
        <div class="h-full flex flex-col bg-background">
          <div class="h-12 px-4 border-b border-border flex items-center shrink-0">
            <p class="text-sm font-semibold text-foreground">Theme Preview</p>
          </div>
          <div class="flex-1 min-h-0">
            <PreviewArea
              :preview-theme="previewTheme"
              preview-url="/"
            />
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Media Picker -->
    <MediaPicker
      v-model:open="showMediaPicker"
      @selected="handleMediaSelect"
    >
      <template #trigger>
        <span class="hidden" />
      </template>
    </MediaPicker>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, reactive, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter, onBeforeRouteLeave } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { 
    Button, Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger,
    Dialog, DialogContent
} from '@/shared/components/ui'
import SettingControl from '@/modules/Cms/components/themes/customizer/sidebar/SettingControl.vue'
import CustomizerSidebar from '@/modules/Cms/components/themes/customizer/sidebar/Sidebar.vue'
import BindingsSection from '@/modules/Cms/components/themes/customizer/editor/BindingsSection.vue'
import MediaPicker from '@/shared/components/media/MediaPicker.vue'

// Icons
import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js';
import Save from 'lucide-vue-next/dist/esm/icons/save.js';
import RotateCcw from 'lucide-vue-next/dist/esm/icons/rotate-ccw.js';
import LayoutTemplate from 'lucide-vue-next/dist/esm/icons/layout-template.js';
import Code2 from 'lucide-vue-next/dist/esm/icons/code-xml.js';
import MenuIcon from 'lucide-vue-next/dist/esm/icons/menu.js';
import Settings2 from 'lucide-vue-next/dist/esm/icons/settings-2.js';
import PanelsTopLeft from 'lucide-vue-next/dist/esm/icons/panels-top-left.js';
import Palette from 'lucide-vue-next/dist/esm/icons/palette.js';
import Type from 'lucide-vue-next/dist/esm/icons/type.js';
import ImageIcon from 'lucide-vue-next/dist/esm/icons/image.js';
import Share2 from 'lucide-vue-next/dist/esm/icons/share-2.js';
import PanelBottom from 'lucide-vue-next/dist/esm/icons/panel-bottom.js';
import UserCircle from 'lucide-vue-next/dist/esm/icons/circle-user.js';
import Newspaper from 'lucide-vue-next/dist/esm/icons/newspaper.js';
import GraduationCap from 'lucide-vue-next/dist/esm/icons/graduation-cap.js';
import BarChart3 from 'lucide-vue-next/dist/esm/icons/chart-column.js';
import MessageSquare from 'lucide-vue-next/dist/esm/icons/message-square.js';
import Megaphone from 'lucide-vue-next/dist/esm/icons/megaphone.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Layout from 'lucide-vue-next/dist/esm/icons/layout-dashboard.js';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import History from 'lucide-vue-next/dist/esm/icons/history.js';
import Undo2 from 'lucide-vue-next/dist/esm/icons/undo-2.js';
import Redo2 from 'lucide-vue-next/dist/esm/icons/redo-2.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import Sparkles from 'lucide-vue-next/dist/esm/icons/sparkles.js';
import Award from 'lucide-vue-next/dist/esm/icons/award.js';
import Briefcase from 'lucide-vue-next/dist/esm/icons/briefcase.js';

import api from '@/engine/api/client'
import toast from '@/shared/services/legacy-toast'
import type { ThemeSection } from '@/modules/Cms/types/theme'
import type { Theme } from '@/modules/Cms/types/theme'
import type { SlotBinding } from '@/modules/Cms/composables/useThemeDataBindings'
import { THEME_BINDING_REGISTRY } from '@/modules/Cms/config/themeBindingsRegistry'
import { useThemeCustomizer } from '@/modules/Cms/composables/useThemeCustomizer'
import { themeUsesJanariCanvas } from '@/modules/Cms/utils/themeManifest'
import PreviewArea from '@/modules/Cms/components/themes/customizer/preview/PreviewArea.vue'
import { parseResponse, ensureArray } from '@/shared/utils/responseParser'

const { t, te } = useI18n()
const route = useRoute()
const router = useRouter()
const slug = route.params.slug as string

const {
    theme,
    loading,
    saving,
    formValues,
    customCss,
    bindings,
    isDirty,
    canUndo,
    canRedo,
    fetchThemeData,
    saveAll,
    resetToInitial,
    resetToDefaults,
    undo,
    redo,
    saveHistory,
    recordSettingChange,
} = useThemeCustomizer(slug, t)
const availableMenus = ref<{ value: string | number; label: string }[]>([])
const previewTheme = computed<Theme>(() => {
    const base = (theme.value || {}) as Theme
    const baseSettings = (base.settings || {}) as Record<string, unknown>
    return {
        ...base,
        settings: {
            ...baseSettings,
            ...formValues.value,
        },
        custom_css: customCss.value,
    }
})

// ─────────────────────────────────────────────
// Schema & Organization
// ─────────────────────────────────────────────
interface ComponentSchema { 
    id: string; 
    name: string; 
    description: string; 
    icon: any; 
    slots: { id: string; label: string; props: { key: string; label: string }[] }[]; 
    manifestCategory?: string; // Links to manifest category label
}

const iconByRegistryKey: Record<string, any> = {
    hero: ImageIcon,
    principal: UserCircle,
    news: Newspaper,
    majors: GraduationCap,
    stats: BarChart3,
    testimonials: MessageSquare,
    cta: Megaphone,
}

const themeComponents = computed<ComponentSchema[]>(() =>
    THEME_BINDING_REGISTRY.map(component => ({
        id: component.id,
        name: t(component.nameKey),
        description: t(component.descriptionKey),
        icon: iconByRegistryKey[component.icon] || LayoutTemplate,
        manifestCategory: component.manifestCategory,
        slots: component.slots.map(slot => ({
            id: slot.id,
            label: t(slot.labelKey),
            props: slot.props.map(prop => ({
                key: prop.key,
                label: t(prop.labelKey),
            })),
        })),
    }))
)

// ─────────────────────────────────────────────
// Sidebar Navigation Logic
// ─────────────────────────────────────────────
const searchQuery = ref('')
const activeItemId = ref('')
const collapsedGroups = ref<string[]>([])
const expandedSlots = ref<string[]>([])
const sidebarCollapsed = ref(false)
const workspaceMode = ref<'design' | 'bindings' | 'advanced'>('design')
const showPreview = ref(false)

interface NavItem { 
    id: string; 
    label: string; 
    description: string; 
    icon: any; 
    manifestSections?: ThemeSection[]; 
    bindingComponent?: ComponentSchema; 
    hasBinding?: boolean;
}

const dedicatedManifestCategories = new Set([
    'General',
    'Education Info',
    'Social Media',
    'Appearance',
    'Buttons',
    'Colors',
    'Typography',
    'Fonts',
    'Layout',
    'Animations',
    'Footer',
    'Page Management',
    'PPDB Page',
    'About Page',
    'Academic Page',
    'Achievement Page',
    'Vocational Page',
    'Career Center Page',
    'Blog Page',
    'Contact Page',
    'Graduation Page',
])

const sidebarGroups = computed(() => {
    const groups = [
        { 
            id: 'identity', 
            label: t('features.theme_customizer.sidebar.categories.identity'), 
            items: [
                { id: 'identity-general', label: t('features.theme_customizer.sidebar.items.general'), description: t('features.theme_customizer.sidebar.items.general_desc'), icon: Globe, manifestSections: findSections(['General']), hasBinding: false },
                { id: 'identity-edu', label: t('features.theme_customizer.sidebar.items.edu'), description: t('features.theme_customizer.sidebar.items.edu_desc'), icon: Sparkles, manifestSections: findSections(['Education Info']), hasBinding: false },
                { id: 'identity-menus', label: t('features.theme_customizer.sidebar.items.menus.title'), description: t('features.theme_customizer.sidebar.items.menus.description'), icon: MenuIcon, hasBinding: false },
                { id: 'identity-social', label: t('features.theme_customizer.sidebar.items.social'), description: t('features.theme_customizer.sidebar.items.social_desc'), icon: Share2, manifestSections: findSections(['Social Media']), hasBinding: false },
            ] 
        },
        { 
            id: 'design', 
            label: t('features.theme_customizer.sidebar.categories.design'), 
            items: [
                { id: 'design-branding', label: t('features.theme_customizer.sidebar.items.styles'), description: t('features.theme_customizer.sidebar.items.styles_desc'), icon: Palette, manifestSections: findSections(['Appearance', 'Buttons']), hasBinding: false },
                { id: 'design-colors', label: t('features.theme_customizer.sidebar.items.palette'), description: t('features.theme_customizer.sidebar.items.palette_desc'), icon: Sparkles, manifestSections: findSections(['Colors']), hasBinding: false },
                { id: 'design-typo', label: t('features.theme_customizer.sidebar.items.typo'), description: t('features.theme_customizer.sidebar.items.typo_desc'), icon: Type, manifestSections: findSections(['Typography', 'Fonts']), hasBinding: false },
                { id: 'styling-css', label: t('features.theme_customizer.sidebar.items.css'), description: t('features.theme_customizer.sidebar.items.css_desc'), icon: Code2, hasBinding: false },
            ] 
        },
        { 
            id: 'components', 
            label: t('features.theme_customizer.sidebar.categories.components'), 
            items: themeComponents.value.map(comp => ({
                id: `comp-${comp.id}`,
                label: comp.name,
                description: comp.description,
                icon: comp.icon,
                bindingComponent: comp,
                // Only show manifest sections here when category isn't already covered by other sidebar groups.
                manifestSections: comp.manifestCategory && !dedicatedManifestCategories.has(comp.manifestCategory)
                    ? findSections([comp.manifestCategory])
                    : [],
                hasBinding: hasComponentBindings(comp.id)
            }))
        },
        { 
            id: 'interaction', 
            label: t('features.theme_customizer.sidebar.categories.interaction'), 
            items: [
                { id: 'ux-layout', label: t('features.theme_customizer.sidebar.items.layout'), description: t('features.theme_customizer.sidebar.items.layout_desc'), icon: PanelsTopLeft, manifestSections: findSections(['Layout']), hasBinding: false },
                { id: 'ux-motion', label: t('features.theme_customizer.sidebar.items.motion'), description: t('features.theme_customizer.sidebar.items.motion_desc'), icon: Sparkles, manifestSections: findSections(['Animations']), hasBinding: false },
                { id: 'ux-footer', label: t('features.theme_customizer.sidebar.items.footer'), description: t('features.theme_customizer.sidebar.items.footer_desc'), icon: PanelBottom, manifestSections: findSections(['Footer']), hasBinding: false },
            ] 
        },
        { 
            id: 'special-pages', 
            label: t('features.theme_customizer.sidebar.categories.special_pages'), 
            items: [
                { id: 'page-management', label: t('features.theme_customizer.sidebar.items.page_management', 'Lifecycle & Controls'), description: 'Manage enable/disable status for all pages', icon: Settings2, manifestSections: findSections(['Page Management']), hasBinding: false },
                { id: 'page-ppdb', label: t('features.theme_customizer.sidebar.items.page_ppdb'), description: t('features.theme_customizer.sidebar.items.page_ppdb_desc'), icon: GraduationCap, manifestSections: findSections(['PPDB Page']), hasBinding: false },
                { id: 'page-about', label: t('features.theme_customizer.sidebar.items.page_about', 'About Page'), description: 'Customize the About/Profile page', icon: UserCircle, manifestSections: findSections(['About Page']), hasBinding: false },
                { id: 'page-academic', label: t('features.theme_customizer.sidebar.items.page_academic', 'Academic Page'), description: 'Customize the Academic & Curriculum page', icon: GraduationCap, manifestSections: findSections(['Academic Page']), hasBinding: false },
                { id: 'page-achievement', label: t('features.theme_customizer.sidebar.items.page_achievement', 'Achievement Page'), description: 'Customize the Student Achievements page', icon: Award, manifestSections: findSections(['Achievement Page']), hasBinding: false },
                { id: 'page-vocation', label: t('features.theme_customizer.sidebar.items.page_vocation', 'Vocational Page'), description: 'Customize the Majors/Program Keahlian page', icon: GraduationCap, manifestSections: findSections(['Vocational Page']), hasBinding: false },
                { id: 'page-career', label: t('features.theme_customizer.sidebar.items.page_career', 'Career Center'), description: 'Customize the BKK / Career Center page', icon: Briefcase, manifestSections: findSections(['Career Center Page']), hasBinding: false },
                { id: 'page-blog', label: t('features.theme_customizer.sidebar.items.page_blog', 'Blog Page'), description: 'Customize the News & Information page', icon: Newspaper, manifestSections: findSections(['Blog Page']), hasBinding: false },
                { id: 'page-contact', label: t('features.theme_customizer.sidebar.items.page_contact', 'Contact Page'), description: 'Customize the Contact Us page', icon: Globe, manifestSections: findSections(['Contact Page']), hasBinding: false },
                { id: 'page-graduation', label: t('features.theme_customizer.sidebar.items.page_graduation', 'Graduation Page'), description: t('features.theme_customizer.sidebar.items.page_graduation_desc'), icon: GraduationCap, manifestSections: findSections(['Graduation Page']), hasBinding: false },
            ] 
        }
    ]
    return groups
})

const filteredGroups = computed(() => {
    if (!searchQuery.value) return sidebarGroups.value
    const query = searchQuery.value.toLowerCase()
    return sidebarGroups.value.map(g => ({
        ...g,
        items: g.items.filter(i => 
            i.label.toLowerCase().includes(query) || 
            i.description.toLowerCase().includes(query)
        )
    })).filter(g => g.items.length > 0)
})

const selectedItem = computed(() => {
    for (const g of sidebarGroups.value) {
        const found = g.items.find(i => i.id === activeItemId.value)
        if (found) return found as NavItem
    }
    return null
})

const activeBindingComponentId = computed(() => selectedItem.value?.bindingComponent?.id || '')
const flatNavItems = computed(() => sidebarGroups.value.flatMap((group) => group.items as NavItem[]))

const activeGroupLabel = computed(() => {
    for (const g of sidebarGroups.value) {
        if (g.items.some(i => i.id === activeItemId.value)) return g.label
    }
    return ''
})

function selectItem(item: NavItem) {
    activeItemId.value = item.id
    if (item.id === 'styling-css') {
        workspaceMode.value = 'advanced'
    } else if (item.bindingComponent) {
        workspaceMode.value = 'bindings'
    } else {
        workspaceMode.value = 'design'
    }
    if (item.bindingComponent && item.bindingComponent.slots.length > 0) {
        ensureComponentBindings(item.bindingComponent.id)
        expandedSlots.value = [item.bindingComponent.slots[0]!.id]
    }
}

function getAllNavItems(): NavItem[] {
    return sidebarGroups.value.flatMap((group) => group.items as NavItem[]);
}

function pickItemForMode(mode: 'design' | 'bindings' | 'advanced'): NavItem | null {
    const items = getAllNavItems();
    if (mode === 'advanced') {
        return items.find((item) => item.id === 'styling-css') || null;
    }
    if (mode === 'bindings') {
        return items.find((item) => !!item.bindingComponent) || null;
    }
    return items.find((item) => !item.bindingComponent && item.id !== 'styling-css') || null;
}

function ensureSelectionForMode(mode: 'design' | 'bindings' | 'advanced') {
    const current = selectedItem.value;
    if (current && isItemCompatibleWithMode(current)) return;
    const fallback = pickItemForMode(mode);
    if (fallback) selectItem(fallback);
}

function isItemCompatibleWithMode(item: NavItem | null): boolean {
    if (!item) return true
    if (item.id === 'styling-css') return workspaceMode.value === 'advanced'
    if (item.bindingComponent) return workspaceMode.value === 'bindings'
    return workspaceMode.value === 'design'
}

const modeHintText = computed(() => {
    if (!selectedItem.value) return ''
    if (selectedItem.value.id === 'styling-css') return 'Custom CSS tersedia di mode Advanced.'
    if (selectedItem.value.bindingComponent) return 'Data binding tersedia di mode Bindings.'
    return 'Pengaturan visual tersedia di mode Design.'
})

watch(
    workspaceMode,
    (mode) => {
        ensureSelectionForMode(mode);
    },
    { immediate: false }
);

watch(
    sidebarGroups,
    () => {
        if (!selectedItem.value) {
            ensureSelectionForMode(workspaceMode.value);
        }
    },
    { immediate: true }
);

function toggleGroup(groupId: string) {
    if (collapsedGroups.value.includes(groupId)) {
        collapsedGroups.value = collapsedGroups.value.filter(g => g !== groupId)
    } else {
        collapsedGroups.value.push(groupId)
    }
}

// Helper to find manifest sections by category labels
function findSections(catLabels: string[]): ThemeSection[] {
    if (!theme.value?.manifest?.settings_schema) return []
    const schema = theme.value.manifest.settings_schema
    const sections: Record<string, ThemeSection> = {}

    Object.keys(schema).forEach(key => {
        const s = schema[key]
        if (s && catLabels.includes(s.category || 'General') && !s.hidden) {
            const cat = s.category || 'General'
            // Slugify category for translation lookup (e.g. "Education Info" -> "education_info")
            const catKey = cat.toLowerCase()
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '');
                
            const translatedLabel = te(`features.theme_customizer.items.manifest_categories.${catKey}`) 
                ? t(`features.theme_customizer.items.manifest_categories.${catKey}`) 
                : cat;

            if (!sections[cat]) sections[cat] = { id: cat, label: translatedLabel, settings: [] }
            sections[cat].settings.push({ key, ...s })
        }
    })
    return Object.values(sections)
}

function getVisibleSettings(settings: any[]) {
    if (!Array.isArray(settings)) return [];
    if (!themeUsesJanariCanvas(theme.value)) return settings;

    const preset = String(formValues.value.color_preset || 'custom');
    const isMonochromePreset = preset === 'monochrome_clean';
    const allowPrimaryColor = preset !== 'monochrome_clean';

    return settings.filter((setting: any) => {
        const key = String(setting?.key || '');
        if (!key) return true;

        // Skip hidden settings
        if (setting.hidden) return false;

        // color_background is hidden schema; UI uses bg_light/bg_dark tokens instead
        if (key === 'color_background') return false;

        // monochrome_variant only makes sense for monochrome preset
        if (key === 'monochrome_variant') {
            return isMonochromePreset;
        }

        // texture and texture strength available for ALL presets

        if (key === 'color_primary') {
            return allowPrimaryColor;
        }

        return true;
    });
}

// ─────────────────────────────────────────────
// Data Binding Logic
// ─────────────────────────────────────────────
const categories = ref<any[]>([])
const pages = ref<any[]>([])
const previewLoading = ref<string | null>(null)
const previewResults = reactive<Record<string, any[]>>({})

function getSlotConfig(compId: string, slotId: string): SlotBinding {
    if (!bindings.value[compId]) bindings.value[compId] = { slots: {} }
    if (!bindings.value[compId].slots[slotId]) {
        bindings.value[compId].slots[slotId] = { sourceType: 'static', categoryFilter: 'all', tagFilter: 'all', pageSlug: '', limit: 5, orderBy: 'published_at', orderDir: 'desc', propMapping: {} }
    } else if (!bindings.value[compId].slots[slotId].propMapping) {
        // Migration/Repair: Ensure propMapping exists
        bindings.value[compId].slots[slotId].propMapping = {}
    }
    return bindings.value[compId].slots[slotId]!
}

function updateBinding(compId: string, slotId: string, field: string, value: any) {
    const config = getSlotConfig(compId, slotId);
    (config as any)[field] = value
    saveHistory()
}

function ensureComponentBindings(compId: string) {
    if (!bindings.value[compId]) bindings.value[compId] = { slots: {} }
    const comp = themeComponents.value.find(c => c.id === compId)
    const compBindings = bindings.value[compId]
    if (comp && compBindings) {
        comp.slots.forEach(slot => {
            if (!compBindings.slots[slot.id]) {
                compBindings.slots[slot.id] = { sourceType: 'static', categoryFilter: 'all', tagFilter: 'all', pageSlug: '', limit: 5, orderBy: 'published_at', orderDir: 'desc', propMapping: {} }
            } else {
                const s = compBindings.slots[slot.id];
                if (s && !s.propMapping) {
                    s.propMapping = {};
                }
            }
        })
    }
}

function hasComponentBindings(compId: string): boolean {
    const b = bindings.value[compId];
    if (!b || !b.slots) return false;
    const slots = b.slots as Record<string, SlotBinding>;
    return Object.values(slots).some((slot) => slot.sourceType !== 'static');
}

function toggleSlot(slotId: string) {
    if (expandedSlots.value.includes(slotId)) expandedSlots.value = expandedSlots.value.filter(s => s !== slotId)
    else expandedSlots.value.push(slotId)
}

function getSourceLabel(src: string) {
    return ({ 
        static: t('features.theme_customizer.sources.static'), 
        api_posts: t('features.theme_customizer.sources.api_posts'), 
        api_pages: t('features.theme_customizer.sources.api_pages'), 
        api_categories: t('features.theme_customizer.sources.api_categories') 
    } as any)[src] || src
}

function getSourceIcon(src: string) {
    return ({ static: LayoutTemplate, api_posts: Newspaper, api_pages: Layout, api_categories: Sparkles } as any)[src] || Search
}

function getFieldsForSource(src: string) {
    if (src === 'api_posts') return [
        { value: 'title', label: t('features.theme_customizer.items.news_title') }, 
        { value: 'excerpt', label: t('features.theme_customizer.items.short_info') }, 
        { value: 'content', label: t('features.theme_customizer.items.message') },
        { value: 'thumbnail', label: t('features.theme_customizer.items.thumbnail') }, 
        { value: 'published_at', label: t('features.theme_customizer.items.date') },
        { value: 'category.name', label: t('common.labels.category') }, 
        { value: 'slug', label: t('features.theme_customizer.items.path') },
        { value: 'views', label: t('features.theme_customizer.editor.bindings.sort_options.views') }
    ]
    if (src === 'api_pages') return [
        { value: 'title', label: t('features.theme_customizer.items.title') }, 
        { value: 'thumbnail', label: t('features.theme_customizer.items.thumbnail') }, 
        { value: 'slug', label: t('features.theme_customizer.items.path') }
    ]
    if (src === 'api_categories') return [
        { value: 'name', label: t('common.labels.name') }, 
        { value: 'slug', label: t('features.theme_customizer.items.path') },
        { value: 'posts_count', label: t('features.theme_customizer.items.counter') }
    ]
    return []
}

async function previewSlotData(slotId: string) {
    if (!activeItemId.value) return
    const compId = activeItemId.value.replace('comp-', '')
    const config = getSlotConfig(compId, slotId)
    if (config.sourceType === 'static') return
    
    previewLoading.value = slotId
    try {
        let results: any[] = []
        if (config.sourceType === 'api_posts') {
            const params: any = { status: 'published', type: 'post', per_page: config.limit || 5, sort_by: config.orderBy || 'published_at' }
            if (config.categoryFilter && config.categoryFilter !== 'all') params.category = config.categoryFilter
            const res = await api.get('/manage/cms/contents', { params })
            const parsed = parseResponse<any>(res)
            results = ensureArray<any>(parsed.data)
        } else if (config.sourceType === 'api_pages') {
            const res = await api.get('/manage/cms/contents', { params: { type: 'page', status: 'published' } })
            const parsed = parseResponse<any>(res)
            results = ensureArray<any>(parsed.data)
            if (config.pageSlug) {
                results = results.filter((item) => String(item?.slug || '') === String(config.pageSlug))
            }
        } else if (config.sourceType === 'api_categories') {
            const res = await api.get('/manage/cms/categories')
            const parsed = parseResponse<any>(res)
            results = ensureArray<any>(parsed.data)
        }
        previewResults[slotId] = results
    } catch { toast.error(t('features.theme_customizer.messages.error'), t('features.theme_customizer.messages.probe_failed')) }
    finally { previewLoading.value = null }
}

function filterPreviewFields(item: any) {
    const fields = ['title', 'name', 'slug', 'published_at', 'status']
    const out: any = {}
    fields.forEach(f => { if (item[f]) out[f] = item[f] })
    return out
}

// ─────────────────────────────────────────────
// Actions
// ─────────────────────────────────────────────

async function fetchCategories() {
    try {
        const r = await api.get('/manage/cms/categories')
        const parsed = parseResponse<any>(r)
        categories.value = ensureArray<any>(parsed.data)
    } catch { /* silent */ }
}

async function fetchMenus() {
    try {
        const r = await api.get('/manage/layout/menus')
        const parsed = parseResponse<any>(r)
        const data = ensureArray<any>(parsed.data)
        availableMenus.value = data.map((m: any) => ({ value: m.id, label: m.name }))
        availableMenus.value.unshift({ value: 'none', label: t('features.theme_customizer.editor.menus.placeholder') })
    } catch { /* silent */ }
}

async function fetchPages() {
    try {
        const r = await api.get('/manage/cms/contents', { params: { type: 'page' } })
        const parsed = parseResponse<any>(r)
        pages.value = ensureArray<any>(parsed.data)
    } catch { /* silent */ }
}

const menuSections = computed(() => {
    if (!theme.value?.manifest?.menus) return []
    const menus = theme.value.manifest.menus
    return Object.entries(menus).map(([locKey, locLabel]) => {
        const locTransKey = `features.theme_customizer.items.menus.locations.${locKey}`;
        const finalLabel = te(locTransKey) ? t(locTransKey) : String(locLabel);

        return {
            key: `menu_location_${locKey}`,
            label: finalLabel,
            type: 'select',
            category: 'Menus',
            options: availableMenus.value,
            description: t('features.theme_customizer.editor.menus.description', { label: finalLabel })
        };
    })
})

function handleBack() {
    if (isDirty.value) {
        if (confirm(t('features.theme_customizer.messages.confirm_exit'))) router.push({ name: 'themes' })
    } else router.push({ name: 'themes' })
}

const handleBeforeUnload = (event: BeforeUnloadEvent) => {
    if (!isDirty.value) return;
    event.preventDefault();
    event.returnValue = '';
};

// Media
const showMediaPicker = ref(false)
const activeMediaField = ref<string | null>(null)
function openMediaPicker(key: string) { activeMediaField.value = key; showMediaPicker.value = true; }
function handleMediaSelect(m: { url: string }) { if (activeMediaField.value) recordSettingChange(activeMediaField.value, m.url); showMediaPicker.value = false; }

const handleKey = (e: KeyboardEvent) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'z') { e.preventDefault(); undo(); }
    if ((e.ctrlKey || e.metaKey) && e.key === 'y') { e.preventDefault(); redo(); }
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); if (isDirty.value) saveAll(); }
}

onMounted(() => {
    fetchThemeData();
    fetchMenus();
    fetchCategories();
    fetchPages();
    window.addEventListener('keydown', handleKey);
    window.addEventListener('beforeunload', handleBeforeUnload);
});

onBeforeRouteLeave((_to, _from, next) => {
    if (!isDirty.value) {
        next();
        return;
    }
    if (confirm(t('features.theme_customizer.messages.confirm_exit'))) next();
    else next(false);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKey);
    window.removeEventListener('beforeunload', handleBeforeUnload);
});
</script>
