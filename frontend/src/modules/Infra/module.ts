import type { JanariModule } from '@/engine/types/module';
import infraRoutes from './router';
import infraNavigation from './navigation';

export const InfraModule: JanariModule = {
    id: 'infra',
    name: 'Infrastructure & DevOps',
    routes: infraRoutes,
    navigation: infraNavigation,
};

export default InfraModule;
