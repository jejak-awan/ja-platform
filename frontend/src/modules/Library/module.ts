import type { JanariModule } from '@/engine/types/module';
import libraryRoutes from './router';
import libraryNavigation from './navigation';

export const LibraryModule: JanariModule = {
    id: 'library',
    name: 'Library & Metadata',
    routes: libraryRoutes,
    navigation: libraryNavigation,
};

export default LibraryModule;
