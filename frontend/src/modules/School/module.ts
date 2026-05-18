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
            routeName: 'schools.dashboard',
            condition: (_user, auth) => auth.hasPermission('view schools'),
            component: defineAsyncComponent(() => import('./components/dashboard/SchoolConsoleDashboard.vue'))
        },
        {
            id: 'school-teacher',
            priority: 80,
            routeName: 'teacher.dashboard',
            condition: (_user, auth) => auth.hasPermission('view teacher portal'),
            component: defineAsyncComponent(() => import('./components/dashboard/TeacherDashboard.vue'))
        },
        {
            id: 'school-osis',
            priority: 70,
            routeName: 'osis.index',
            condition: (_user, auth) => auth.hasPermission('view osis'),
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
            routeName: 'student.dashboard',
            condition: (_user, auth) => auth.isAtLeastRole('siswa'),
            component: defineAsyncComponent(() => import('./components/dashboard/StudentDashboard.vue'))
        }
    ]
};

export default SchoolModule;
