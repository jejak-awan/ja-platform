import type { RouteRecordRaw } from 'vue-router';
import type { NavItem } from '@/shared/utils/navigation';
import type { Component } from 'vue';

export interface DashboardConfig {
    id: string;
    priority: number;
    condition: (user: any, authStore: any) => boolean;
    component: Component | (() => Promise<Component>);
    routeName?: string;
}

export interface JanariModule {
    id: string;
    name: string;
    routes?: RouteRecordRaw[];
    navigation?: NavItem[];
    dashboards?: DashboardConfig[];
    initialize?: () => Promise<void>;
}
