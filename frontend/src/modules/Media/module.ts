import type { JanariModule } from '@/engine/types/module';
import mediaRoutes from './router';
import { mediaNavigation } from './navigation';

export const MediaModule: JanariModule = {
    id: 'media',
    name: 'Media Manager',
    routes: mediaRoutes,
    navigation: mediaNavigation,
};

export default MediaModule;
