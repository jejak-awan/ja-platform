<template>
  <div 
    class="min-h-screen bg-background text-foreground admin-instant admin-layout"
    :class="{ 'no-transitions': resizing }"
  >
    <!-- Sidebar -->
    <AdminSidebar
      :sidebar-minimized="sidebarMinimized"
      :sidebar-open="sidebarOpen"
      :user="authStore.user || undefined"
      @toggle-minimize="toggleSidebarMinimize"
      @close="closeSidebar"
      @logout="handleLogout"
    />

    <!-- Mobile Backdrop -->
    <div 
      v-if="sidebarOpen" 
      class="fixed inset-0 z-40 bg-background/60 lg:hidden"
      @click="closeSidebar"
    />

    <!-- Main Content -->
    <div
      :class="[
        'min-h-screen',
        sidebarMinimized ? 'lg:pl-[68px]' : 'lg:pl-64'
      ]"
    >
      <!-- Top Navbar -->
      <AdminNavbar
        :is-authenticated="authStore.isAuthenticated"
        :user="authStore.user || undefined"
        @toggle-sidebar="toggleSidebarOpen"
        @logout="handleLogout"
      />

      <!-- Page Content -->
      <main class="p-6 relative overflow-hidden">
        <router-view v-slot="{ Component, route: slotRoute }">
          <transition name="fade" mode="out-in">
            <div :key="String(slotRoute.name || slotRoute.path)">
              <component :is="Component" />
            </div>
          </transition>
        </router-view>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/modules/Core/stores/auth';
import { useCmsStore } from '@/modules/Cms/stores/cms';
import { useSidebar } from '@/composables/useSidebar';
import { useHead } from '@unhead/vue';
import { useI18n } from 'vue-i18n';
import AdminSidebar from '@/modules/Core/components/layouts/AdminSidebar.vue';
import AdminNavbar from '@/modules/Core/components/layouts/AdminNavbar.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const cmsStore = useCmsStore();
const { t, te } = useI18n();
const { sidebarMinimized, sidebarOpen, toggleSidebarMinimize, toggleSidebarOpen, closeSidebar } = useSidebar();

// Use shared mounted state for synchronized transitions

// Resize Throttling
const resizing = ref(false);
let resizeTimer: ReturnType<typeof setTimeout> | null = null;

const handleResize = () => {
    resizing.value = true;
    document.body.classList.add('no-transitions');
    if (resizeTimer) clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        resizing.value = false;
        document.body.classList.remove('no-transitions');
    }, 200);
};

onMounted(() => {
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    if (resizeTimer) clearTimeout(resizeTimer);
    document.body.classList.remove('no-transitions');
    window.removeEventListener('resize', handleResize);
});

// Reactive Global Title Management
const pageTitle = ref('JA CMS');

watch([() => route?.name, () => cmsStore.siteSettings?.site_name, () => route?.meta], () => {
    // Stability guard
    if (!route) return;

    const siteName = cmsStore.siteSettings?.site_name || 'JA CMS';
    
    // 1. Route Meta Title
    if (route.meta?.title) {
        const titleKey = route.meta.title as string;
        const title = te(titleKey) ? t(titleKey) : titleKey;
        pageTitle.value = `${siteName} | ${title}`;
        return;
    }
    
    // 2. Auto-generated from Route Name
    if (route.name) {
        const name = String(route.name);
        const segments = name.replace(/-([a-z])/g, (_, g1) => (g1 || '').toUpperCase()).split('.');
        const camelName = segments[0] || ''; 
        const key = `common.navigation.menu.${camelName}`;
        
        let label: string;
        if (te(key)) {
            label = t(key);
        } else {
            const baseLabel = name.split('.').pop() || name;
            label = baseLabel.charAt(0).toUpperCase() + baseLabel.slice(1);
        }
        
        pageTitle.value = `${siteName} | ${label}`;
        return;
    }

    pageTitle.value = siteName;
}, { immediate: true });

useHead({
    title: pageTitle
});

const handleLogout = async () => {
    await authStore.logout();
    router.push({ name: 'login' });
};

</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
