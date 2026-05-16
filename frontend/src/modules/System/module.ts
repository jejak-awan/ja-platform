import { defineAsyncComponent } from 'vue';
import type { JanariModule } from '@/engine/types/module';
import coreRoutes from './router/index';
import { coreNavigation } from './navigation';

export const CoreModule: JanariModule = {
    id: 'core',
    name: 'System Core',
    routes: coreRoutes,
    navigation: coreNavigation,
    dashboards: [
        {
            id: 'core-admin',
            priority: 100,
            routeName: 'core.dashboard',
            condition: (user) => user?.roles?.some((r: any) => r.name === 'super') ?? false,
            component: defineAsyncComponent(() => import('./components/dashboard/AdminDashboard.vue'))
        }
    ]
};

export default CoreModule;
