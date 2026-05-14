import { defineAsyncComponent } from 'vue';
import type { JanariModule } from '@/engine/types/module';
import cmsRoutes from './router';
import { cmsNavigation } from './navigation';

export const CmsModule: JanariModule = {
    id: 'cms',
    name: 'Content Management',
    routes: cmsRoutes,
    navigation: cmsNavigation,
    dashboards: [
        {
            id: 'cms-creator',
            priority: 40,
            condition: (_user, auth) => auth.hasPermission('create content') || auth.hasPermission('edit content') || auth.hasPermission('upload media'),
            component: defineAsyncComponent(() => import('./components/dashboard/CreatorDashboard.vue'))
        },
        {
            id: 'cms-viewer',
            priority: 0,
            condition: () => true,
            component: defineAsyncComponent(() => import('./components/dashboard/ViewerDashboard.vue'))
        }
    ]
};

export default CmsModule;
