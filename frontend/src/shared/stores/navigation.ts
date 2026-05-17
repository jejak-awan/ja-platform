import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { NavItem } from '@/shared/utils/navigation';

export const useNavigationStore = defineStore('navigation', () => {
    const registry = ref<Record<string, NavItem[]>>({});

    const registerModuleNavigation = (moduleId: string, items: NavItem[]) => {
        registry.value[moduleId] = items;
    };

    const navigationGroups = computed(() => {
        const groups: Record<string, NavItem[]> = {
            academic: [],
            studio: [],
            design: [],
            operations: [],
            monitoring: []
        };

        // Fallback mapping in case a module item doesn't explicitly define a group
        const mapModuleToGroup = (moduleId: string): string => {
            switch (moduleId) {
                case 'school':
                    return 'academic';
                case 'cms':
                case 'media':
                case 'forms':
                case 'newsletter':
                case 'search':
                    return 'studio';
                case 'layout':
                    return 'design';
                case 'system':
                case 'infra':
                case 'library':
                    return 'operations';
                default:
                    return moduleId;
            }
        };

        // Populate groups based on item.group or fallback mapping
        Object.entries(registry.value).forEach(([moduleId, items]) => {
            if (!items) return;
            items.forEach(item => {
                const targetGroup = item.group || mapModuleToGroup(moduleId);
                if (!groups[targetGroup]) {
                    groups[targetGroup] = [];
                }
                const groupArr = groups[targetGroup];
                if (groupArr) {
                    groupArr.push(item);
                }
            });
        });

        // Sort items inside each group based on priority (descending, higher priority first)
        Object.keys(groups).forEach(key => {
            const arr = groups[key];
            if (arr) {
                arr.sort((a, b) => (b.priority || 0) - (a.priority || 0));
            }
        });

        return groups;
    });

    return {
        registry,
        registerModuleNavigation,
        navigationGroups
    };
});
