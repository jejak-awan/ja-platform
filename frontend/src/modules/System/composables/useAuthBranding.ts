import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useSystemStore } from '@/modules/System/stores/system';


export function useAuthBranding() {
    const route = useRoute();
    const systemStore = useSystemStore();
    


    const branding = computed(() => {
        const context = route.meta.authContext || 'system';
        
        if (context === 'tenant') {
            return {
                name: systemStore.siteSettings?.site_name || systemStore.appIdentity.app_name,
                logo: systemStore.siteSettings?.site_logo || systemStore.appIdentity.app_logo,
                description: systemStore.siteSettings?.site_description || '',
                type: 'tenant'
            };
        }
        
        // Default to system (Core)
        return {
            name: systemStore.appIdentity.app_name,
            logo: systemStore.appIdentity.app_logo,
            description: '',
            type: 'system'
        };
    });

    return {
        branding,
        context: computed(() => route.meta.authContext || 'system')
    };
}
