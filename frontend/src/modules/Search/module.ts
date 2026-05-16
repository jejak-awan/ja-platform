import type { JanariModule } from '@/engine/types/module';
import searchRoutes from './router';
import searchNavigation from './navigation';

export const SearchModule: JanariModule = {
    id: 'search',
    name: 'Search & Indexing',
    routes: searchRoutes,
    navigation: searchNavigation,
};

export default SearchModule;
