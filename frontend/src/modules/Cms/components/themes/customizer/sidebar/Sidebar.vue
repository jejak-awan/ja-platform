<template>
  <aside
    :class="[
      'border-r border-border bg-card flex flex-col shrink-0 shadow-xl z-10 transition-all duration-300',
      sidebarCollapsed ? 'w-20' : 'w-80'
    ]"
  >
    <div class="p-3 border-b bg-muted/20 flex items-center justify-between">
      <span
        v-if="!sidebarCollapsed"
        class="text-[11px] font-bold uppercase tracking-widest text-muted-foreground"
      >Customizer Menu</span>
      <Button
        variant="ghost"
        size="icon"
        class="h-8 w-8 ml-auto"
        :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
        @click="emit('update:sidebarCollapsed', !sidebarCollapsed)"
      >
        <ChevronsRight
          v-if="sidebarCollapsed"
          class="w-4 h-4"
        />
        <ChevronsLeft
          v-else
          class="w-4 h-4"
        />
      </Button>
    </div>

    <div
      v-if="!sidebarCollapsed"
      class="p-4 border-b bg-muted/20"
    >
      <div class="relative">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
        <input
          :value="searchQuery"
          :placeholder="t('features.theme_customizer.sidebar.search_placeholder')"
          class="w-full pl-9 pr-4 py-2 bg-background border rounded-lg text-xs focus:ring-2 focus:ring-primary/20 outline-none transition-all"
          @input="emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
        >
      </div>
    </div>

    <div
      v-if="!sidebarCollapsed"
      class="flex-1 overflow-y-auto custom-scrollbar px-3 py-4 space-y-6"
    >
      <div
        v-for="group in groups"
        :key="group.id"
        class="space-y-1"
      >
        <button
          class="w-full flex items-center justify-between px-3 py-1 mb-1 text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60 hover:text-foreground transition-colors group"
          @click="emit('toggleGroup', group.id)"
        >
          <span>{{ group.label }}</span>
          <ChevronDown
            class="w-3 h-3 transition-transform duration-200"
            :class="{ '-rotate-90': collapsedGroups.includes(group.id) }"
          />
        </button>

        <div
          v-show="!collapsedGroups.includes(group.id)"
          class="space-y-0.5 animate-in slide-in-from-top-1 duration-200"
        >
          <button
            v-for="item in group.items"
            :key="item.id"
            class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-3 group relative overflow-hidden"
            :class="activeItemId === item.id
              ? 'bg-primary text-primary-foreground shadow-md shadow-primary/20'
              : 'hover:bg-muted text-foreground/80 hover:text-foreground'"
            @click="emit('selectItem', item)"
          >
            <component
              :is="item.icon"
              class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110"
            />
            <span class="truncate">{{ item.label }}</span>
            <div
              v-if="activeItemId === item.id"
              class="ml-auto flex items-center gap-1"
            >
              <div class="w-1.5 h-1.5 rounded-full bg-white animate-pulse" />
            </div>
            <span
              v-else-if="item.hasBinding"
              class="ml-auto w-1.5 h-1.5 rounded-full bg-primary/40"
            />
          </button>
        </div>
      </div>
    </div>

    <div
      v-else
      class="flex-1 overflow-y-auto custom-scrollbar px-2 py-3 space-y-2"
    >
      <button
        v-for="item in flatItems"
        :key="`mini-${item.id}`"
        class="w-full h-11 rounded-lg flex items-center justify-center transition-all"
        :title="item.label"
        :class="activeItemId === item.id
          ? 'bg-primary text-primary-foreground shadow-md shadow-primary/20'
          : 'text-muted-foreground hover:text-foreground hover:bg-muted'"
        @click="emit('selectItem', item)"
      >
        <component
          :is="item.icon"
          class="w-4 h-4"
        />
      </button>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { Button } from '@/components/ui'
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js'
import ChevronsLeft from 'lucide-vue-next/dist/esm/icons/chevrons-left.js'
import ChevronsRight from 'lucide-vue-next/dist/esm/icons/chevrons-right.js'
import Search from 'lucide-vue-next/dist/esm/icons/search.js'

interface SidebarItem {
  id: string;
  label: string;
  description: string;
  icon: any;
  hasBinding?: boolean;
}

interface SidebarGroup {
  id: string;
  label: string;
  items: SidebarItem[];
}

defineProps<{
  groups: SidebarGroup[];
  flatItems: SidebarItem[];
  activeItemId: string;
  collapsedGroups: string[];
  searchQuery: string;
  sidebarCollapsed: boolean;
}>()

const emit = defineEmits<{
  (e: 'selectItem', item: SidebarItem): void;
  (e: 'toggleGroup', groupId: string): void;
  (e: 'update:searchQuery', value: string): void;
  (e: 'update:sidebarCollapsed', value: boolean): void;
}>()

const { t } = useI18n()
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: hsl(var(--muted-foreground) / 0.3);
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: hsl(var(--muted-foreground) / 0.5);
}
</style>
