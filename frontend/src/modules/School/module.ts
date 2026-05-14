import { defineAsyncComponent } from 'vue';
import type { JanariModule } from '@/engine/types/module';
import schoolRoutes from './router/index';
import { schoolNavigation } from './navigation';

export const SchoolModule: JanariModule = {
    id: 'school',
    name: 'School Management',
    routes: schoolRoutes,
    navigation: schoolNavigation,
    dashboards: [
        {
            id: 'school-admin',
            priority: 90,
            condition: (_user, auth) => auth.isAtLeastRole('admin-bk'),
            component: defineAsyncComponent(() => import('./components/dashboard/SchoolAdminDashboard.vue'))
        },
        {
            id: 'school-teacher',
            priority: 80,
            condition: (_user, auth) => auth.isAtLeastRole('guru'),
            component: defineAsyncComponent(() => import('./components/dashboard/TeacherDashboard.vue'))
        },
        {
            id: 'school-osis',
            priority: 70,
            condition: (_user, auth) => auth.isAtLeastRole('admin-osis'),
            component: defineAsyncComponent(() => import('./components/dashboard/OsisDashboard.vue'))
        },
        {
            id: 'school-parent',
            priority: 60,
            condition: (_user, auth) => auth.isAtLeastRole('orang-tua'),
            component: defineAsyncComponent(() => import('./components/dashboard/ParentDashboard.vue'))
        },
        {
            id: 'school-student',
            priority: 50,
            condition: (_user, auth) => auth.isAtLeastRole('siswa'),
            component: defineAsyncComponent(() => import('./components/dashboard/StudentDashboard.vue'))
        }
    ]
};

export default SchoolModule;
