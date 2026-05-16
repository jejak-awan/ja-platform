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
                <TheLogo :minimized="sidebarMinimized" />
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

      <!-- Navigation -->
      <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
        <!-- Dashboard (standalone) -->
        <router-link
          :to="dashboardLink"
          class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl group"
          :class="[ isDashboardActive ? 'bg-accent text-foreground' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' ]"
          :title="sidebarMinimized ? dashboardLabel : ''"
          @click="$emit('close')"
        >
          <component
            :is="getIcon('dashboard')"
            class="w-5 h-5 flex-shrink-0"
          />
          <span
            v-if="!sidebarMinimized"
            class="ml-3 truncate font-semibold"
          >
            {{ dashboardLabel }}
          </span>
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
                    <span class="text-[10px] font-bold text-muted-foreground/40 tracking-wider whitespace-nowrap">{{ getNavigationLabel(item) }}</span>
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
                        :to="subItem.to || (subItem.name ? { name: subItem.name } : '')"
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
                    :to="item.to || (item.name ? { name: item.name } : '')"
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
                        <DropdownMenuLabel class="px-3 py-1 text-[10px] font-bold text-muted-foreground/60 tracking-wider">
                          {{ getNavigationLabel(item) }}
                        </DropdownMenuLabel>
                                                
                        <DropdownMenuItem
                          v-for="subItem in item.children"
                          :key="subItem.name || subItem.label"
                          as-child
                        >
                          <router-link
                            :to="subItem.to || (subItem.name ? { name: subItem.name } : '')"
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
                          :to="item.to || (item.name ? { name: item.name } : '')"
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
import { useNavigationStore } from '@/shared/stores/navigation';
import type { NavItem } from '@/shared/utils/navigation';
import { getIcon } from '@/shared/utils/icons';
import { useAuthStore } from '@/modules/System/stores/auth';
import { useSystemStore } from '@/modules/System/stores/system';
import { useWorkspaceStore } from '@/engine/stores/workspace';
import TheLogo from '@/shared/layouts/partials/TheLogo.vue';
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
} from '@/shared/components/ui';
import type { User } from '@/engine/types/auth';

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
const systemStore = useSystemStore();
const workspaceStore = useWorkspaceStore();
const navigationStore = useNavigationStore();

const activeUnitId = computed(() => workspaceStore.activeId);

// Dynamic Dashboard logic that doesn't depend on School/Cms modules directly
const dashboardLink = computed(() => {
    const isGlobal = activeUnitId.value === 0 || activeUnitId.value === '0';
    if (isGlobal) {
        return workspaceStore.activeContextType === 'system' 
            ? { name: 'system.dashboard' } 
            : { name: 'dashboard' }; // Let the router handle the default landing
    }
    // Generic approach: If there's an active ID, we assume we're in a scoped dashboard
    return { name: 'dashboard' };
});

const dashboardLabel = computed(() => {
    const isGlobal = activeUnitId.value === 0 || activeUnitId.value === '0';
    if (isGlobal) {
        return workspaceStore.activeContextType === 'system' 
            ? t('common.navigation.menu.dashboard') 
            : t('common.navigation.menu.foundationDashboard');
    }
    return t('common.navigation.menu.scopedDashboard');
});

const isDashboardActive = computed(() => {
    const names = ['dashboard', 'system.dashboard', 'foundation.dashboard', 'schools.dashboard', 'schools.index'];
    return names.includes(String($route.name));
});

const sidebarSections = computed<SidebarSection[]>(() => {
    const isGlobal = activeUnitId.value === 0 || activeUnitId.value === '0';
    const isSuperAdmin = authStore.getRoleRank() >= 100;
    const contextType = workspaceStore.activeContextType;
    
    // Core sections available in all contexts (Engine handles filtering of items)
    const sections: SidebarSection[] = [
        { key: 'school', labelKey: 'common.navigation.sections.school', icon: getIcon('package') },
        { key: 'cms', labelKey: 'common.navigation.sections.cms', icon: getIcon('layers') },
    ];

    if (isSuperAdmin && isGlobal && contextType === 'system') {
        sections.push({ key: 'system', labelKey: 'common.navigation.sections.core', icon: getIcon('settings') });
    }

    return sections;
});

const expandedSections = ref<Record<string, boolean>>({});
const expandedSubSections = ref<Record<string, boolean>>({});

const initializeExpandedSections = () => {
    if (sidebarSections.value.length > 0 && sidebarSections.value[0]?.key) {
        expandedSections.value[sidebarSections.value[0].key] = true;
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
    if (!$route.name) return;
    const currentRouteName = String($route.name);
    
    for (const section of sidebarSections.value) {
        const items = filteredNavigation.value[section.key] || [];
        
        let sectionHasActive = false;
        items.forEach(item => {
            const isActive = item.name === currentRouteName || 
                           (item.children && item.children.some(c => c.name === currentRouteName));
            
            if (isActive) {
                sectionHasActive = true;
                if (item.label && item.children && item.children.some(c => c.name === currentRouteName)) {
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

const isSubSectionActive = (item: NavItem) => isItemActive(item);

const isSectionActive = (key: string) => {
    const items = filteredNavigation.value[key] || [];
    return items.some(item => isItemActive(item));
};

const filteredNavigation = computed(() => {
    const filtered: Record<string, NavItem[]> = {};
    const isGlobal = activeUnitId.value === 0 || activeUnitId.value === '0';

    for (const [group, items] of Object.entries(navigationStore.navigationGroups)) {
        filtered[group] = items
            .filter((item: NavItem) => {
                const role = Array.isArray(item.role) ? item.role[0] : item.role;
                if (role && !authStore.isAtLeastRole(role)) return false;
                if (item.permission && !authStore.hasPermission(item.permission)) return false;
                if (item.context && item.context !== 'both') {
                    if (item.context === 'foundation' && !isGlobal) return false;
                    if (item.context === 'unit' && isGlobal) return false;
                }
                return true;
            })
            .map((item: NavItem) => {
                const filteredChildren = item.children?.filter(child => {
                    if (child.permission && !authStore.hasPermission(child.permission)) return false;
                    if (child.context && child.context !== 'both') {
                        if (child.context === 'foundation' && !isGlobal) return false;
                        if (child.context === 'unit' && isGlobal) return false;
                    }
                    return true;
                });
                
                const lk = item.labelKey || '';
                const resolvedLabel = lk && te(lk) ? t(lk) : (item.label || item.name || '');
                
                return { 
                    ...item, 
                    label: resolvedLabel,
                    children: filteredChildren 
                };
            });
    }
    return filtered;
});

const getNavigationLabel = (item: NavItem) => item.label || '';

const getVisitTooltip = computed(() => {
    const siteUrl = systemStore.siteSettings?.site_url || 'domain.com';
    let domain = siteUrl;
    try {
        const url = new URL(siteUrl.startsWith('http') ? siteUrl : `https://${siteUrl}`);
        domain = url.hostname;
    } catch { /* fallback */ }
    return t('common.navigation.visit_site', { url: domain });
});

watch(expandedSections, (newVal) => {
    localStorage.setItem('sidebarExpandedSections', JSON.stringify(newVal));
}, { deep: true });

onMounted(() => {
    const saved = localStorage.getItem('sidebarExpandedSections');
    if (saved) {
        try { expandedSections.value = JSON.parse(saved); } 
        catch { initializeExpandedSections(); }
    } else {
        initializeExpandedSections();
    }
    autoExpandActiveSection();
});

watch(() => $route.name, () => autoExpandActiveSection());
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
