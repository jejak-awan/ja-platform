import type { JanariModule } from '@/engine/types/module';
import layoutRoutes from './router';
import layoutNavigation from './navigation';

export const LayoutModule: JanariModule = {
    id: 'layout',
    name: 'Site Layout & Navigation',
    routes: layoutRoutes,
    navigation: layoutNavigation,
};

export default LayoutModule;
