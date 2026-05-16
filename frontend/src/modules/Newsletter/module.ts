import type { JanariModule } from '@/engine/types/module';
import newsletterRoutes from './router';
import newsletterNavigation from './navigation';

export const NewsletterModule: JanariModule = {
    id: 'newsletter',
    name: 'Marketing & Newsletter',
    routes: newsletterRoutes,
    navigation: newsletterNavigation,
};

export default NewsletterModule;
