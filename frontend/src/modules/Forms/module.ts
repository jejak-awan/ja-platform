import type { JanariModule } from '@/engine/types/module';
import formsRoutes from './router';
import formsNavigation from './navigation';

export const FormsModule: JanariModule = {
    id: 'forms',
    name: 'Interactive Forms',
    routes: formsRoutes,
    navigation: formsNavigation,
};

export default FormsModule;
