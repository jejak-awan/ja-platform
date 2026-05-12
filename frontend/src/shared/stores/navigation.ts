import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { NavItem } from '@/shared/utils/navigation';

export const useNavigationStore = defineStore('navigation', () => {
    const registry = ref<Record<string, NavItem[]>>({});

    const registerModuleNavigation = (moduleId: string, items: NavItem[]) => {
        registry.value[moduleId] = items;
    };

    /**
     * Aggregates all registered navigation items.
     * Order can be defined here if needed.
     */
    const navigationGroups = computed(() => {
        const groups: Record<string, NavItem[]> = {};
        
        // Define order if desired
        const order = ['cms', 'school', 'core'];
        
        order.forEach(id => {
            if (registry.value[id]) {
                groups[id] = registry.value[id];
            }
        });

        // Add any modules not in the defined order
        Object.keys(registry.value).forEach(id => {
            const items = registry.value[id];
            if (!order.includes(id) && items) {
                groups[id] = items;
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
