<template>
  <div class="p-4 md:p-8">
    <component :is="activeDashboard" />
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, type Component } from 'vue';
import { useAuthStore } from '@/modules/Core/stores/auth';

const authStore = useAuthStore();

// Dynamically import dashboard components to improve initial load
const AdminDashboard = defineAsyncComponent(() => import('@/modules/Core/components/dashboard/AdminDashboard.vue'));
const SchoolAdminDashboard = defineAsyncComponent(() => import('@/modules/School/components/dashboard/SchoolAdminDashboard.vue'));
const TeacherDashboard = defineAsyncComponent(() => import('@/modules/School/components/dashboard/TeacherDashboard.vue'));
const StudentDashboard = defineAsyncComponent(() => import('@/modules/School/components/dashboard/StudentDashboard.vue'));
const OsisDashboard = defineAsyncComponent(() => import('@/modules/School/components/dashboard/OsisDashboard.vue'));
const ParentDashboard = defineAsyncComponent(() => import('@/modules/School/components/dashboard/ParentDashboard.vue'));
const CreatorDashboard = defineAsyncComponent(() => import('@/modules/Core/components/dashboard/CreatorDashboard.vue'));
const ViewerDashboard = defineAsyncComponent(() => import('@/modules/Core/components/dashboard/ViewerDashboard.vue'));

// Determine which dashboard to show based on roles and permissions
const activeDashboard = computed<Component>(() => {
    // 1. Super Admin / Core System Admin
    if (authStore.user?.roles?.some(r => r.name === 'super-admin')) {
        return AdminDashboard;
    }

    // 2. School Leadership & Administration
    if (authStore.isAtLeastRole('admin-bk')) { // Includes all admin-* roles as they are >= 85
        return SchoolAdminDashboard;
    }

    // 3. Teachers & Homeroom Teachers
    if (authStore.isAtLeastRole('guru')) {
        return TeacherDashboard;
    }

    // 4. OSIS Management
    if (authStore.isAtLeastRole('admin-osis')) {
        return OsisDashboard;
    }

    // 5. Parents
    if (authStore.isAtLeastRole('orang-tua')) {
        return ParentDashboard;
    }

    // 6. Students
    if (authStore.isAtLeastRole('siswa')) {
        return StudentDashboard;
    }
    
    // Fallback: Creators (Editor, Author)
    if (authStore.hasPermission('create content') || authStore.hasPermission('edit content') || authStore.hasPermission('upload media')) {
        return CreatorDashboard;
    }
    
    // Viewer Dashboard: Fallback for read-only users (Subscriber)
    return ViewerDashboard;
});
</script>
