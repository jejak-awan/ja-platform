<template>
  <div class="animate-in fade-in duration-700">
    <component
      :is="dashboardComponent"
      v-if="dashboardComponent"
    />
    <div
      v-else
      class="flex flex-col items-center justify-center min-h-[60vh] space-y-4"
    >
      <LucideIcon
        name="LayoutDashboard"
        class="w-16 h-16 text-muted-foreground/20 animate-pulse"
      />
      <p class="text-muted-foreground font-medium italic animate-pulse">
        {{ $t('common.messages.loading.default') }}...
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue';
import { useAuthStore } from '@/modules/System/stores/auth';
import { LucideIcon } from '@/shared/components/ui';

const authStore = useAuthStore();

// Dynamic Dashboard Components
const SchoolConsoleDashboard = defineAsyncComponent(() => import('@/modules/School/components/dashboard/SchoolAdminDashboard.vue'));
const TeacherDashboard = defineAsyncComponent(() => import('@/modules/School/components/dashboard/TeacherDashboard.vue'));
const StudentDashboard = defineAsyncComponent(() => import('@/modules/School/components/dashboard/StudentDashboard.vue'));
const OsisDashboard = defineAsyncComponent(() => import('@/modules/School/components/dashboard/OsisDashboard.vue'));
const ParentDashboard = defineAsyncComponent(() => import('@/modules/School/components/dashboard/ParentDashboard.vue'));

// Specialized role-based dashboards
const CurriculumDashboard = defineAsyncComponent(() => import('@/modules/School/components/dashboard/CurriculumDashboard.vue'));
const StudentAffairsDashboard = defineAsyncComponent(() => import('@/modules/School/components/dashboard/StudentAffairsDashboard.vue'));

const dashboardComponent = computed(() => {
  if (!authStore.user) return null;

  const roles = authStore.user.roles?.map(r => r.name) || [];

  if (roles.some(r => ['super', 'admin-yayasan', 'admin-unit', 'admin', 'operator-unit'].includes(r))) {
    return SchoolConsoleDashboard;
  }
  
  if (roles.includes('admin-kurikulum')) return CurriculumDashboard;
  if (roles.includes('admin-kesiswaan')) return StudentAffairsDashboard;
  if (roles.includes('guru') || roles.includes('wali-kelas')) return TeacherDashboard;
  if (roles.includes('siswa')) return StudentDashboard;
  if (roles.includes('admin-osis')) return OsisDashboard;
  if (roles.includes('orang-tua')) return ParentDashboard;

  // Fallback to Admin for high-rank roles
  if (authStore.isAdmin) return SchoolConsoleDashboard;

  return null;
});
</script>
