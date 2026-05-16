import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useSystemStore } from '@/modules/System/stores/system';


export function useAuthBranding() {
    const route = useRoute();
    const coreStore = useSystemStore();
    


    const branding = computed(() => {
        const context = route.meta.authContext || 'system';
        
        if (context === 'tenant') {
            return {
                name: coreStore.siteSettings?.site_name || coreStore.appIdentity.app_name,
                logo: coreStore.siteSettings?.site_logo || coreStore.appIdentity.app_logo,
                description: coreStore.siteSettings?.site_description || '',
                type: 'tenant'
            };
        }
        
        // Default to system (Core)
        return {
            name: coreStore.appIdentity.app_name,
            logo: coreStore.appIdentity.app_logo,
            description: '',
            type: 'system'
        };
    });

    return {
        branding,
        context: computed(() => route.meta.authContext || 'system')
    };
}
