<template>
  <aside
    :class="[ 'fixed inset-y-0 left-0 z-50 bg-sidebar text-sidebar-foreground border-r border-border', sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0', sidebarMinimized ? 'w-[68px]' : 'w-64' ]"
  >
    <!-- Floating Toggle Button (Desktop) -->
    <button
      class="hidden lg:flex absolute -right-3 top-5 items-center justify-center h-6 w-6 rounded-full border border-border bg-sidebar text-muted-foreground hover:text-foreground shadow-sm z-[51]"
      :title="sidebarMinimized ? t('common.navigation.sidebar.expand') : t('common.navigation.sidebar.minimize')"
      :aria-label="sidebarMinimized ? t('common.navigation.sidebar.expand') : t('common.navigation.sidebar.minimize')"
      @click="$emit('toggle-minimize')"
    >
      <component
        :is="getIcon('chevron-left')"
        v-if="!sidebarMinimized"
      />
      <component
        :is="getIcon('chevron-right')"
        v-else
      />
    </button>

    <div class="flex flex-col h-full">
      <!-- Logo -->
      <div class="flex items-center justify-between h-16 px-4 border-b border-border">
        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger as-child>
              <a
                href="/"
                target="_blank"
                rel="noopener noreferrer"
                class="block hover:opacity-80 focus:outline-none"
              >
                <AdminLogo :minimized="sidebarMinimized" />
              </a>
            </TooltipTrigger>
            <TooltipContent
              side="bottom"
              :side-offset="10"
            >
              {{ getVisitTooltip }}
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
        <div class="flex items-center gap-2">
          <!-- Mobile Close Button -->
          <button
            class="lg:hidden text-muted-foreground hover:text-accent-foreground"
            :aria-label="t('common.actions.close')"
            @click="$emit('close')"
          >
            <component :is="getIcon('x')" />
          </button>
        </div>
      </div>

      <!-- Active Context Indicator (Ultra Premium & Unified) -->
      <div
        v-if="!sidebarMinimized"
        class="px-3 pt-5 pb-3 border-b border-border bg-sidebar"
      >
        <!-- School Unit Context -->
        <div v-if="schoolStore.currentSchool?.is_multi_unit" class="space-y-1">
          <transition name="context-fade" mode="out-in">
           <div 
             :key="unitStore.activeUnitId ?? 'global'"
             class="flex items-center gap-3 p-3 rounded-2xl transition-all duration-500 border border-white/20 group/unit relative overflow-hidden shadow-md"
             :class="[
               unitStore.activeUnitId === 0 
                 ? 'bg-gradient-to-br from-amber-500 via-amber-600 to-orange-600 text-white' 
                 : 'bg-gradient-to-br from-blue-600 via-indigo-700 to-blue-800 text-white'
             ]"
           >
             <!-- Subtle glass overlay -->
             <div class="absolute inset-0 bg-white/5 opacity-0 group-hover/unit:opacity-100 transition-opacity duration-500" />
             
             <!-- Icon Box: Perfectly Centered -->
             <div 
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/30 bg-white/10 shadow-[inset_0_1px_1px_rgba(255,255,255,0.3)] shrink-0 transition-transform duration-500 group-hover/unit:scale-105"
             >
                <component 
                 :is="getIcon(unitStore.activeUnitId === 0 ? 'globe' : 'building')" 
                 class="w-5 h-5 text-white" 
                />
             </div>

             <!-- Text Content -->
             <div class="flex flex-col min-w-0 relative z-10">
                 <div class="flex items-center gap-1.5">
                    <span class="text-[11px] font-black truncate tracking-tight uppercase text-white leading-tight">
                        {{ unitStore.activeUnitId === 0 ? 'Sistem Global' : unitStore.activeUnit?.name }}
                    </span>
                    <div v-if="unitStore.activeUnitId === 0" class="w-1.5 h-1.5 rounded-full bg-white animate-pulse shadow-[0_0_8px_rgba(255,255,255,0.8)]" />
                 </div>
                 <span class="text-[9px] font-bold truncate uppercase tracking-[0.12em] leading-none mt-1 text-white/80">
                     {{ unitStore.activeUnitId === 0 ? 'Ekosistem Pusat' : (unitStore.activeUnit?.level || 'Unit') }}
                 </span>
             </div>
           </div>
          </transition>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
        <!-- Dashboard (standalone) -->
        <router-link
          :to="{ name: 'dashboard' }"
          class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl group"
          :class="[ $route.name === 'dashboard' ? 'bg-accent text-foreground' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' ]"
          :title="sidebarMinimized ? t('common.navigation.menu.dashboard') : ''"
          @click="$emit('close')"
        >
          <component
            :is="getIcon('dashboard')"
            class="w-5 h-5 flex-shrink-0"
          />
          <span
            v-if="!sidebarMinimized"
            class="ml-3 truncate"
          >{{ t('common.navigation.menu.dashboard') }}</span>
        </router-link>

        <!-- Collapsible Sections -->
        <template
          v-for="section in sidebarSections"
          :key="section.key"
        >
          <div
            v-if="(filteredNavigation[section.key]?.length ?? 0) > 0"
            class="pt-2"
          >
            <!-- EXPANDED MODE: Accordion Style -->
            <template v-if="!sidebarMinimized">
              <!-- Section Header -->
              <button
                class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-muted-foreground hover:text-foreground tracking-wide rounded-xl hover:bg-accent"
                @click="toggleSection(section.key)"
              >
                <div class="flex items-center gap-2">
                  <component
                    :is="section.icon"
                    class="w-4 h-4"
                  />
                  <span>{{ t(section.labelKey) }}</span>
                </div>
                <component 
                  :is="getIcon('chevron-down')" 
                  :class="{ 'rotate-180': expandedSections[section.key] }"
                />
              </button>

              <!-- Section Items -->
              <div 
                v-show="expandedSections[section.key]"
                class="mt-1 space-y-0.5"
              >
                <template
                  v-for="item in filteredNavigation[section.key]"
                  :key="item.name || item.label"
                >
                  <div
                    v-if="item.type === 'divider'"
                    class="py-2 px-9 flex items-center gap-2"
                  >
                    <div class="h-px bg-border flex-1" />
                    <span class="text-[10px] uppercase font-bold text-muted-foreground/30 tracking-widest whitespace-nowrap">{{ getNavigationLabel(item) }}</span>
                    <div class="h-px bg-border flex-1" />
                  </div>
                                    
                  <!-- SUB-DROPDOWN -->
                  <div
                    v-else-if="item.children && item.children.length > 0"
                    class="space-y-0.5"
                  >
                    <button 
                      class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-xl group pl-9"
                      :class="[ isSubSectionActive(item) ? 'text-foreground hover:bg-accent' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' ]"
                      @click="toggleSubSection(item.label || '')"
                    >
                      <div class="flex items-center gap-2.5">
                        <component
                          :is="getIcon(item.icon || '')"
                          class="w-4 h-4 flex-shrink-0"
                        />
                        <span class="truncate">{{ getNavigationLabel(item) }}</span>
                      </div>
                      <component 
                        :is="getIcon('chevron-down')" 
                        :class="{ 'rotate-180': expandedSubSections[item.label || ''] }"
                        class="w-3.5 h-3.5"
                      />
                    </button>
                                        
                    <div 
                      v-show="expandedSubSections[item.label || '']"
                      class="mt-0.5 space-y-0.5"
                    >
                      <router-link
                        v-for="subItem in item.children"
                        :key="subItem.name || subItem.label"
                        :to="subItem.name ? { name: subItem.name } : (subItem.to || '')"
                        class="flex items-center px-3 py-1.5 text-xs font-medium rounded-xl group pl-16"
                        :class="[ $route.name === subItem.name ? 'bg-accent text-foreground font-semibold' : 'text-muted-foreground/80 hover:bg-accent hover:text-accent-foreground' ]"
                        @click="$emit('close')"
                      >
                        <component
                          :is="getIcon(subItem.icon || subItem.name || '')"
                          class="w-3.5 h-3.5 flex-shrink-0 mr-2"
                        />
                        <span class="truncate">{{ getNavigationLabel(subItem) }}</span>
                      </router-link>
                    </div>
                  </div>

                  <!-- NORMAL ITEM -->
                  <router-link
                    v-else
                    :to="item.name ? { name: item.name } : (item.to || '')"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-xl group pl-9"
                    :class="[ $route.name === item.name ? 'bg-accent text-foreground' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' ]"
                    @click="$emit('close')"
                  >
                    <component
                      :is="getIcon(item.icon || item.name || '')"
                      class="w-4 h-4 flex-shrink-0 mr-2.5"
                    />
                    <span class="truncate">{{ getNavigationLabel(item) }}</span>
                  </router-link>
                </template>
              </div>
            </template>

            <!-- MINIMIZED MODE: Group Icon with Floating Menu -->
            <div
              v-else
              class="flex justify-center p-1"
            >
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <button
                    class="w-full flex items-center justify-center p-2.5 rounded-xl cursor-pointer focus:outline-none"
                    :class="[ isSectionActive(section.key) ? 'bg-accent text-foreground' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' ]"
                  >
                    <component
                      :is="section.icon"
                      class="w-5 h-5"
                    />
                  </button>
                </DropdownMenuTrigger>

                <DropdownMenuContent
                  side="right"
                  :side-offset="12"
                  class="w-56 p-0 overflow-hidden"
                >
                  <!-- Header -->
                  <div class="px-3 py-2 text-xs font-semibold text-muted-foreground tracking-wide border-b border-border bg-muted/30">
                    {{ t(section.labelKey) }}
                  </div>
                                    
                  <div class="max-h-[80vh] overflow-y-auto py-1">
                    <!-- Items -->
                    <template
                      v-for="item in filteredNavigation[section.key]"
                      :key="item.name || item.label"
                    >
                      <div
                        v-if="item.type === 'divider'"
                        class="py-2 px-3 flex items-center gap-2"
                      >
                        <div class="h-px bg-border flex-1" />
                        <span class="text-[10px] uppercase font-bold text-muted-foreground/30 tracking-widest whitespace-nowrap">{{ getNavigationLabel(item) }}</span>
                        <div class="h-px bg-border flex-1" />
                      </div>
                                            
                      <!-- Sub-category Header in Popover -->
                      <div
                        v-else-if="item.children && item.children.length > 0"
                        class="mt-2 first:mt-0"
                      >
                        <DropdownMenuLabel class="px-3 py-1 text-[10px] uppercase font-bold text-muted-foreground/50 tracking-widest">
                          {{ getNavigationLabel(item) }}
                        </DropdownMenuLabel>
                                                
                        <DropdownMenuItem
                          v-for="subItem in item.children"
                          :key="subItem.name || subItem.label"
                          as-child
                        >
                          <router-link
                            :to="subItem.name ? { name: subItem.name } : (subItem.to || '')"
                            class="flex items-center px-3 py-1.5 text-xs font-medium cursor-pointer"
                            :class="[ $route.name === subItem.name ? 'text-foreground bg-accent' : 'text-muted-foreground' ]"
                            @click="$emit('close')"
                          >
                            <component
                              :is="getIcon(subItem.icon || subItem.name || '')"
                              class="w-3.5 h-3.5 flex-shrink-0 mr-2 opacity-70"
                            />
                            <span class="truncate">{{ getNavigationLabel(subItem) }}</span>
                          </router-link>
                        </DropdownMenuItem>
                      </div>

                      <DropdownMenuItem
                        v-else
                        as-child
                      >
                        <router-link
                          :to="item.name ? { name: item.name } : (item.to || '')"
                          class="flex items-center px-3 py-2 text-sm font-medium cursor-pointer"
                          :class="[ $route.name === item.name ? 'text-foreground bg-accent' : 'text-muted-foreground' ]"
                          @click="$emit('close')"
                        >
                          <component
                            :is="getIcon(item.icon || item.name || '')"
                            class="w-4 h-4 flex-shrink-0 mr-2.5 opacity-70"
                          />
                          <span class="truncate">{{ getNavigationLabel(item) }}</span>
                        </router-link>
                      </DropdownMenuItem>
                    </template>
                  </div>
                </DropdownMenuContent>
              </DropdownMenu>
            </div>
          </div>
        </template>
      </nav>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted, type Component } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { navigationGroups, type NavItem } from '@/utils/navigation';
import { getIcon } from '@/utils/icons';
import { useAuthStore } from '@/modules/Core/stores/auth';
import { useCmsStore } from '@/modules/Cms/stores/cms';
import { useUnitStore } from '@/modules/School/stores/unit';
import { useSchoolStore } from '@/modules/School/stores/school';
import AdminLogo from '@/modules/Core/components/layouts/AdminLogo.vue';
// Inline SVG icons from icons.ts - no lucide bundle needed
import { 
    Tooltip, 
    TooltipContent, 
    TooltipProvider, 
    TooltipTrigger,
    DropdownMenu,
    DropdownMenuTrigger,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel
} from '@/components/ui';
import type { User } from '@/types/core/auth';

interface SidebarSection {
    key: string;
    labelKey: string;
    icon: Component | string;
}

defineProps<{
    sidebarMinimized?: boolean;
    sidebarOpen?: boolean;
    user?: User | null;
}>();

defineEmits<{
    (e: 'toggle-minimize'): void;
    (e: 'close'): void;
    (e: 'logout'): void;
}>();

const { t, te } = useI18n();
const $route = useRoute();
const authStore = useAuthStore();
const unitStore = useUnitStore();
const schoolStore = useSchoolStore();

const cmsStore = useCmsStore();

const sidebarSections: SidebarSection[] = [
    { key: 'cms', labelKey: 'common.navigation.sections.cms', icon: getIcon('layers') },
    { key: 'school', labelKey: 'common.navigation.sections.school', icon: getIcon('package') },
    { key: 'core', labelKey: 'common.navigation.sections.core', icon: getIcon('settings') },
];

const expandedSections = ref<Record<string, boolean>>({});
const expandedSubSections = ref<Record<string, boolean>>({});

const initializeExpandedSections = () => {
    if (sidebarSections.length > 0 && sidebarSections[0]?.key) {
        expandedSections.value[sidebarSections[0].key] = true;
    }
};

const isItemActive = (item: NavItem): boolean => {
    if (!$route) return false;
    if (item.name === $route.name) return true;
    if (item.children && item.children.length > 0) {
        return item.children.some(child => child.name === $route.name);
    }
    return false;
};

const autoExpandActiveSection = () => {
    if (!$route) return;
    for (const section of sidebarSections) {
        const items = filteredNavigation.value[section.key] || [];
        
        let sectionHasActive = false;
        items.forEach(item => {
            if (isItemActive(item)) {
                sectionHasActive = true;
                // If it's a sub-section with children, expand it
                if (item.label && item.children && item.children.some(c => c.name === $route.name)) {
                    expandedSubSections.value[item.label] = true;
                }
            }
        });

        if (sectionHasActive) {
            expandedSections.value[section.key] = true;
        }
    }
};

const toggleSection = (key: string) => {
    const isCurrentlyExpanded = expandedSections.value[key];
    expandedSections.value = {};
    expandedSections.value[key] = !isCurrentlyExpanded;
};

const toggleSubSection = (key: string) => {
    expandedSubSections.value[key] = !expandedSubSections.value[key];
};

const isSubSectionActive = (item: NavItem) => {
    return isItemActive(item);
};

const isSectionActive = (key: string) => {
    const items = filteredNavigation.value[key] || [];
    return items.some(item => isItemActive(item));
};

const filteredNavigation = computed(() => {
    const filtered: Record<string, NavItem[]> = {};
    const activeUnitId = Number(unitStore.activeUnitId);
    const isGlobal = activeUnitId === 0;

    for (const [group, items] of Object.entries(navigationGroups)) {
        filtered[group] = items
            .map(item => {
                const newItem = { ...item };
                if (newItem.children) {
                    newItem.children = newItem.children.filter(child => {
                        // 1. Context check — only hide 'unit' items in global mode
                        //    'global' items remain visible in unit mode (settings, users, etc.)
                        const context = child.context || item.context || 'both';
                        if (isGlobal && context === 'unit') return false;

                        // 2. Role check (priority)
                        if (child.role) {
                            const roles = Array.isArray(child.role) ? child.role : [child.role];
                            const hasRole = authStore.user?.roles?.some(r => roles.includes(r.name));
                            if (!hasRole) return false;
                        }

                        if (!child.permission) return true;
                        return authStore.hasPermission(child.permission);
                    });
                }
                return newItem;
            })
            .filter(item => {
                // 1. Context check — only hide 'unit' items in global mode
                const context = item.context || 'both';
                if (isGlobal && context === 'unit') return false;

                // 2. Role check (priority)
                if (item.role) {
                    const roles = Array.isArray(item.role) ? item.role : [item.role];
                    const hasRole = authStore.user?.roles?.some(r => roles.includes(r.name));
                    if (!hasRole) return false;
                }

                // If it's a parent item with children, only show if it has visible children
                if (item.children && item.children.length === 0 && !item.to) {
                    return false;
                }
                if (!item.permission) return true;
                return authStore.hasPermission(item.permission);
            });
    }
    return filtered;
});

const getNavigationLabel = (item: NavItem) => {
    if (item.labelKey && te(item.labelKey)) return t(item.labelKey);

    if (item.type === 'divider') {
        const key = `common.navigation.sections.${item.label}`;
        return te(key) ? t(key) : item.label || '';
    }
    
    // For nested groups that don't have a route name but have a label
    if (!item.name && item.label) {
        // Try to translate from navigation.sections first
        const sectionKey = `common.navigation.sections.${(item.label || '').toLowerCase().replace(/\s+/g, '_')}`;
        if (te(sectionKey)) return t(sectionKey);
        return item.label;
    }

    if (!item.name) return item.label || '';
    
    const camelName = item.name.replace(/[-.]([a-z])/g, (g) => g[1] ? g[1].toUpperCase() : '');
    const key = `common.navigation.menu.${camelName}`;
    return te(key) ? t(key) : item.label || '';
};

const getVisitTooltip = computed(() => {
    const siteUrl = cmsStore.siteSettings?.site_url || 'domain.com';
    let domain = siteUrl;
    try {
        const url = new URL(siteUrl.startsWith('http') ? siteUrl : `https://${siteUrl}`);
        domain = url.hostname;
    } catch {
        // fallback
    }
    return t('common.navigation.visit_site', { url: domain });
});

watch(expandedSections, (newVal) => {
    localStorage.setItem('sidebarExpandedSections', JSON.stringify(newVal));
}, { deep: true });

watch(() => $route.name, () => {
    autoExpandActiveSection();
});

onMounted(() => {
    const saved = localStorage.getItem('sidebarExpandedSections');
    if (saved) {
        try {
            expandedSections.value = JSON.parse(saved);
        } catch {
            initializeExpandedSections();
        }
    } else {
        initializeExpandedSections();
    }
    autoExpandActiveSection();
});
</script>

<style scoped>
.context-fade-enter-active,
.context-fade-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.context-fade-enter-from {
  opacity: 0;
  transform: translateY(4px) scale(0.98);
}

.context-fade-leave-to {
  opacity: 0;
  transform: translateY(-4px) scale(0.98);
}
</style>
