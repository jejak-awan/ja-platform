<template>
  <div>
    <!-- Header -->
    <div class="mb-10 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-foreground">
          {{ $t('modules.core.users.title') }}
        </h1>
        <p class="text-muted-foreground">
          {{ $t('modules.core.users.subtitle') }}
        </p>
      </div>
      <router-link
        v-if="authStore.hasPermission('create users')"
        :to="{ name: 'users.create' }"
      >
        <Button class="gap-2 rounded-xl">
          <Plus class="w-4 h-4" />
          {{ $t('modules.core.users.createNew') }}
        </Button>
      </router-link>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-10">
      <Card
        :class="cn('p-4 cursor-pointer hover:shadow-md hover:border-primary/50 rounded-xl border-border/50 ', { 'border-primary ring-2 ring-primary/20': verificationFilter === 'all' && !activeStatFilter })"
        @click="clearFilters"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted-foreground">
              {{ $t('modules.core.users.stats.total') }}
            </p>
            <p class="text-2xl font-bold text-foreground">
              {{ stats.total }}
            </p>
          </div>
          <Users class="w-8 h-8 text-primary opacity-80" />
        </div>
      </Card>
      <Card
        :class="cn('p-4 cursor-pointer hover:shadow-md hover:border-primary/50 rounded-xl border-border/50 ', { 'border-primary ring-2 ring-primary/20': verificationFilter === 'verified' })"
        @click="setVerificationFilter('verified')"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted-foreground">
              {{ $t('modules.core.users.stats.verified') }}
            </p>
            <p class="text-2xl font-bold text-primary">
              {{ stats.verified }}
            </p>
          </div>
          <CheckCircle class="w-8 h-8 text-primary opacity-80" />
        </div>
      </Card>
      <Card
        :class="cn('p-4 cursor-pointer hover:shadow-md hover:border-warning/50 rounded-xl border-border/50 ', { 'border-warning ring-2 ring-warning/20': verificationFilter === 'unverified' })"
        @click="setVerificationFilter('unverified')"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted-foreground">
              {{ $t('modules.core.users.stats.unverified') }}
            </p>
            <p class="text-2xl font-bold text-warning">
              {{ stats.unverified }}
            </p>
          </div>
          <AlertCircle class="w-8 h-8 text-warning opacity-80" />
        </div>
      </Card>
      <Card
        :class="cn('p-4 cursor-pointer hover:shadow-md hover:border-success/50 rounded-xl border-border/50 ', { 'border-success ring-2 ring-success/20': activeStatFilter === 'recent' })"
        @click="setStatFilter('recent')"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted-foreground">
              {{ $t('modules.core.users.stats.recent') }}
            </p>
            <p class="text-2xl font-bold text-success">
              {{ stats.recent }}
            </p>
          </div>
          <UserPlus class="w-8 h-8 text-success opacity-80" />
        </div>
      </Card>
      <Card
        :class="cn('p-4 cursor-pointer hover:shadow-md hover:border-info/50 rounded-xl border-border/50 ', { 'border-info ring-2 ring-info/20': activeStatFilter === 'active' })"
        @click="setStatFilter('active')"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted-foreground">
              {{ $t('modules.core.users.stats.active') }}
            </p>
            <p class="text-2xl font-bold text-info">
              {{ stats.active }}
            </p>
          </div>
          <Activity class="w-8 h-8 text-info opacity-80" />
        </div>
      </Card>
    </div>

    <!-- content -->
    <Card class="overflow-hidden rounded-xl border-border/50 shadow-sm">
      <!-- Filters & Actions -->
      <div class="px-6 py-4 border-b border-border/40">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <!-- Left: Search / Filters -->
          <div class="flex items-center gap-3 w-full md:w-auto flex-wrap">
            <div class="relative w-full md:w-72">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
              <Input
                v-model="search"
                type="text"
                :placeholder="$t('modules.core.users.search')"
                class="pl-9"
              />
            </div>
            <Select v-model="roleFilter">
              <SelectTrigger class="w-[160px]">
                <SelectValue :placeholder="$t('modules.core.users.allRoles')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ $t('modules.core.users.allRoles') }}
                </SelectItem>
                <SelectItem
                  v-for="role in roles"
                  :key="role.id"
                  :value="role.name"
                >
                  {{ role.name }}
                </SelectItem>
              </SelectContent>
            </Select>
            <Select v-model="verificationFilter">
              <SelectTrigger class="w-[160px]">
                <SelectValue :placeholder="$t('modules.core.users.filters.all')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ $t('modules.core.users.filters.all') }}
                </SelectItem>
                <SelectItem value="verified">
                  {{ $t('modules.core.users.filters.verifiedOnly') }}
                </SelectItem>
                <SelectItem value="unverified">
                  {{ $t('modules.core.users.filters.unverifiedOnly') }}
                </SelectItem>
              </SelectContent>
            </Select>
            <Select v-model="trashedFilter">
              <SelectTrigger class="w-[160px]">
                <SelectValue :placeholder="$t('common.labels.status')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="without">
                  {{ $t('common.labels.activeOnly') }}
                </SelectItem>
                <SelectItem value="with">
                  {{ $t('common.labels.includesTrashed') }}
                </SelectItem>
                <SelectItem value="only">
                  {{ $t('common.labels.trashedOnly') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Right: Actions -->
          <div class="flex items-center gap-2">
            <div
              v-if="selectedIds.length > 0"
              class="flex items-center gap-3 p-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10 mr-2"
            >
              <span class="text-xs font-semibold text-primary uppercase tracking-wider">
                {{ selectedIds.length }} selected
              </span>
              <div class="h-4 w-px bg-primary/20" />
              <Select
                v-model="bulkActionSelection"
                @update:model-value="handleBulkAction"
              >
                <SelectTrigger class="w-[140px] h-7 border-primary/20 text-xs shadow-none">
                  <SelectValue :placeholder="$t('modules.cms.content.list.bulkActions')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    value="force_logout"
                    class="text-warning focus:text-warning"
                  >
                    {{ $t('modules.core.users.actions.forceLogout') }}
                  </SelectItem>
                  <SelectItem
                    value="verify"
                    class="text-primary focus:text-primary"
                  >
                    {{ $t('modules.core.users.actions.verify') }}
                  </SelectItem>
                  <SelectItem
                    v-if="trashedFilter !== 'only'"
                    value="delete"
                    class="text-destructive focus:text-destructive"
                  >
                    {{ $t('common.actions.delete') }}
                  </SelectItem>
                  <SelectItem
                    v-if="trashedFilter === 'only'"
                    value="restore"
                    class="text-success focus:text-success"
                  >
                    {{ $t('common.actions.restore') }}
                  </SelectItem>
                  <SelectItem
                    v-if="trashedFilter === 'only'"
                    value="force_delete"
                    class="text-destructive focus:text-destructive"
                  >
                    {{ $t('common.actions.forceDelete') }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </div>
      </div>

      <CardContent class="p-0">
        <DataTable
          :table="table"
          :loading="loading"
          :empty-message="$t('modules.core.users.empty')"
        />

        <!-- Pagination -->
        <Pagination
          v-if="pagination && pagination.total > 0"
          :current-page="pagination.current_page"
          :total-items="pagination.total"
          :per-page="Number(pagination.per_page || 10)"
          class="border-none shadow-none mt-4 px-6 py-4"
          @page-change="changePage"
          @update:per-page="changePerPage"
        />
      </CardContent>
    </Card>

    <!-- Create/Edit Modal Removed -->
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';
import api from '@/engine/api/client';
import { cn } from '@/shared/utils/lib-utils';
import { parseResponse, ensureArray, type PaginationData } from '@/shared/utils/responseParser';
import { useToast } from '@/shared/composables/useToast';
import {
    Pagination,
    Button,
    Input,
    Card,
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
    Checkbox,
    Badge,
    DataTable
} from '@/shared/components/ui';
import { h } from 'vue';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
    getSortedRowModel,
    type SortingState,
    type RowSelectionState
} from '@tanstack/vue-table';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Users from 'lucide-vue-next/dist/esm/icons/users.js';
import LogOut from 'lucide-vue-next/dist/esm/icons/log-out.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import CheckCheck from 'lucide-vue-next/dist/esm/icons/check-check.js';
import CheckCircle from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import AlertCircle from 'lucide-vue-next/dist/esm/icons/circle-alert.js';
import UserPlus from 'lucide-vue-next/dist/esm/icons/user-plus.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import RotateCcw from 'lucide-vue-next/dist/esm/icons/rotate-ccw.js';
import { useAuthStore, ROLE_RANKS } from '@/modules/Core/stores/auth';
import { useConfirm } from '@/shared/composables/useConfirm';
import type { User, Role } from '@/engine/types/auth';

const { t } = useI18n();
const toast = useToast();
const router = useRouter();
const loading = ref(false);
const users = ref<User[]>([]);
const roles = ref<Role[]>([]);
const search = ref('');
const roleFilter = ref('all');
const verificationFilter = ref('all');
const trashedFilter = ref('without');
const activeStatFilter = ref<string | null>(null);
const pagination = ref<PaginationData | null>(null);
const authStore = useAuthStore();

const { confirm } = useConfirm();
const columnHelper = createColumnHelper<User>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            checked: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
            'onUpdate:checked': (val) => table.toggleAllPageRowsSelected(!!val),
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: row.getIsSelected(),
            'onUpdate:checked': (val) => row.toggleSelected(!!val),
        }),
        size: 50,
    }),
    columnHelper.accessor('name', {
        header: t('modules.core.users.table.user'),
        cell: ({ row }) => {
            const user = row.original;
            const avatarUrl = user.avatar ? (typeof user.avatar === 'string' ? user.avatar : user.avatar.url) : null;
            
            return h('div', { class: 'flex items-center' }, [
                h('div', { class: 'flex-shrink-0 h-9 w-9' }, [
                    avatarUrl 
                        ? h('img', { src: avatarUrl, class: 'h-9 w-9 rounded-full object-cover' })
                        : h('div', { class: 'h-9 w-9 rounded-full bg-muted flex items-center justify-center border border-border/40' }, [
                            h('span', { class: 'text-muted-foreground font-medium text-xs' }, user?.name?.charAt(0)?.toUpperCase() || 'U')
                        ])
                ]),
                h('div', { class: 'ml-4' }, [
                    h('div', { class: 'text-sm font-medium text-foreground flex items-center gap-2' }, [
                        user.name,
                        user.deleted_at ? h(Badge, { variant: 'destructive', class: 'text-[10px] h-4 px-1' }, t('common.labels.deleted')) : null
                    ]),
                    user.phone ? h('div', { class: 'text-[10px] text-muted-foreground font-mono uppercase tracking-tight' }, user.phone) : null
                ])
            ]);
        }
    }),
    columnHelper.accessor('email', {
        header: t('modules.core.users.table.email'),
        cell: ({ row }) => {
            const user = row.original;
            return h('div', [
                h('div', { class: 'text-sm text-foreground' }, user.email),
                user.email_verified_at 
                    ? h('div', { class: 'text-[10px] text-primary font-bold uppercase tracking-wider' }, t('modules.core.users.status.verified'))
                    : h('div', { class: 'text-[10px] text-muted-foreground italic uppercase tracking-wider' }, t('modules.core.users.status.unverified'))
            ]);
        }
    }),
    columnHelper.accessor('roles', {
        header: t('modules.core.users.table.roles'),
        cell: ({ row }) => {
            const roles = row.original.roles || [];
            if (roles.length === 0) return h('span', { class: 'text-xs text-muted-foreground italic' }, t('modules.core.users.status.noRoles'));
            
            return h('div', { class: 'flex flex-wrap gap-1.5' }, roles.map(role => h(Badge, {
                variant: 'secondary',
                class: 'h-5 text-[10px] px-2 font-semibold uppercase tracking-wider'
            }, role.name)));
        }
    }),
    columnHelper.accessor('last_login_at', {
        header: t('modules.core.users.table.lastLogin'),
        cell: ({ row }) => {
            const date = row.original.last_login_at;
            if (!date) return h('div', { class: 'text-xs text-muted-foreground/50' }, t('modules.core.users.status.never'));
            return h('div', { class: 'text-xs' }, formatDate(date));
        }
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-center' }, t('modules.core.users.table.actions')),
        cell: ({ row }) => {
            const user = row.original;
            const canManageUser = canManage(user);
            const canDeleteUser = canDelete(user);
            
            return h('div', { class: 'flex justify-center items-center gap-1' }, [
                !user.email_verified_at && authStore.hasPermission('edit users') && h(Button, {
                    variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-primary hover:bg-primary/10',
                    disabled: !canManageUser, onClick: () => verifyUser(user), title: t('modules.core.users.actions.verify')
                }, [h(CheckCheck, { class: 'w-4 h-4' })]),
                
                authStore.hasPermission('edit users') && h(Button, {
                    variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-warning hover:bg-warning/10',
                    disabled: !canManageUser, onClick: () => forceLogoutUser(user), title: t('modules.core.users.actions.forceLogout')
                }, [h(LogOut, { class: 'w-4 h-4' })]),
                
                authStore.hasPermission('edit users') && h(Button, {
                    variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-primary hover:bg-primary/10',
                    disabled: !canManageUser, onClick: () => editUser(user), title: t('common.actions.edit')
                }, [h(Pencil, { class: 'w-4 h-4' })]),
                
                user.deleted_at 
                    ? [
                        authStore.hasPermission('delete users') && h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-success hover:bg-success/10',
                            onClick: () => restoreUser(user), title: t('common.actions.restore')
                        }, [h(RotateCcw, { class: 'w-4 h-4' })]),
                        authStore.hasPermission('delete users') && h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-destructive hover:bg-destructive/10',
                            onClick: () => forceDeleteUser(user), title: t('common.actions.forceDelete')
                        }, [h(Trash2, { class: 'w-4 h-4' })])
                    ]
                    : [
                        authStore.hasPermission('delete users') && h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-destructive hover:bg-destructive/10',
                            disabled: !canDeleteUser, onClick: () => deleteUser(user), title: t('common.actions.delete')
                        }, [h(Trash2, { class: 'w-4 h-4' })])
                    ]
            ]);
        }
    })
];

const sorting = ref<SortingState>([]);
const rowSelection = ref<RowSelectionState>({});

const table = useVueTable({
    get data() { return users.value },
    columns,
    state: {
        get sorting() { return sorting.value },
        get rowSelection() { return rowSelection.value },
    },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    onRowSelectionChange: updaterOrValue => {
        rowSelection.value = typeof updaterOrValue === 'function' ? updaterOrValue(rowSelection.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
    enableRowSelection: true,
});

// Sync selectedIds with rowSelection for bulk actions
watch(rowSelection, (newSelection) => {
    selectedIds.value = Object.keys(newSelection)
        .filter(key => newSelection[key])
        .map(id => Number(id));
}, { deep: true });

// Clear selection when users change (pagination/filter)
watch(users, () => {
    rowSelection.value = {};
});

const stats = ref<{
    total: number;
    verified: number;
    unverified: number;
    recent: number;
    active: number;
    by_role: Record<string, number>;
}>({
    total: 0,
    verified: 0,
    unverified: 0,
    recent: 0,
    active: 0,
    by_role: {},
});

const isSuperAdmin = (u: User) => u.roles?.some(r => (ROLE_RANKS[r.name] || 0) >= 100);

const canManage = (targetUser: User) => {
    // Self management is always allowed (for basic edits)
    if (targetUser.id === authStore.user?.id) return true;
    
    // Super Admin (Rank 100) can manage anyone
    if (authStore.getRoleRank() >= 100) return true;

    // Others must strictly be higher rank
    return authStore.isHigherThan(targetUser);
};

const canDelete = (targetUser: User) => {
    // Cannot delete self
    if (targetUser.id === authStore.user?.id) return false;
    
    const myRank = authStore.getRoleRank();
    
    // Non-Super Admins can only delete users with strictly lower rank
    if (myRank < 100) {
        if (!authStore.isHigherThan(targetUser)) return false;
    }
    
    // Super Admin protection for last one
    if (isSuperAdmin(targetUser)) {
        const superAdminCount = users.value.filter(u => isSuperAdmin(u)).length;
        // This is only a frontend check, backend will re-verify
        if ((pagination.value?.total || 0) <= 1 || superAdminCount <= 1) return false;
    }
    
    return true;
};

const fetchUsers = async () => {
    loading.value = true;
    try {
        const params: Record<string, string | number | boolean | undefined> = {
            page: pagination.value?.current_page || 1,
            per_page: pagination.value?.per_page || 10,
        };

        if (search.value) {
            params.search = search.value;
        }

        // Don't send 'all' role to API
        if (roleFilter.value && roleFilter.value !== 'all') {
            params.role = roleFilter.value;
        }

        // Add verification filter
        if (verificationFilter.value === 'verified') {
            params.verified = 1;
        } else if (verificationFilter.value === 'unverified') {
            params.verified = 0;
        }

        // Add trashed filter
        if (trashedFilter.value && trashedFilter.value !== 'without') {
            params.trashed = trashedFilter.value;
        }

        // Add stat filters
        if (activeStatFilter.value === 'recent') {
            params.recent = 1;
        } else if (activeStatFilter.value === 'active') {
            params.active = 1;
        }

        const response = await api.get('/admin/core/users', { params });
        const { data, pagination: paginationData } = parseResponse(response);
        // Ensure each user has roles array
        users.value = (ensureArray(data) as User[]).map((user: User) => ({
            ...user,
            roles: user.roles || [],
        }));
        if (paginationData) {
            pagination.value = paginationData;
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch users:', error);
        toast.error.action(error as Record<string, unknown>);
    } finally {
        loading.value = false;
    }
};

const fetchStats = async () => {
    try {
        const response = await api.get('/admin/core/users/stats');
        // The BaseApiController returns { success: true, data: { ... }, message: ... }
        // parseResponse returns { data: [...], pagination: ... } which is for lists.
        // We just need the raw data object here.
        if (response.data && response.data) {
            stats.value = response.data;
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch stats:', error);
    }
};

const setVerificationFilter = (filter: string) => {
    activeStatFilter.value = null;
    verificationFilter.value = filter;
};

const setStatFilter = (filter: string) => {
    verificationFilter.value = 'all';
    activeStatFilter.value = activeStatFilter.value === filter ? null : filter;
    fetchUsers();
};

const clearFilters = () => {
    verificationFilter.value = 'all';
    activeStatFilter.value = null;
    roleFilter.value = 'all';
    trashedFilter.value = 'without';
    search.value = '';
};

const fetchRoles = async () => {
    try {
        const response = await api.get('/admin/core/roles').catch(() => null);
        if (response) {
            const { data: rolesData } = parseResponse(response);
            roles.value = ensureArray(rolesData);
        } else {
            // Fallback: Extract unique roles from users
            const uniqueRoles = new Map();
            users.value.forEach(user => {
                user.roles?.forEach(role => {
                    if (!uniqueRoles.has(role.id)) {
                        uniqueRoles.set(role.id, role);
                    }
                });
            });
            roles.value = Array.from(uniqueRoles.values());
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch roles:', error);
    }
};

const changePage = (page: number) => {
    if (pagination.value) {
        pagination.value.current_page = page;
        fetchUsers();
    }
};

const changePerPage = (perPage: number | string) => {
    if (pagination.value) {
        pagination.value.per_page = Number(perPage);
        pagination.value.current_page = 1;
        fetchUsers();
    }
};

const editUser = (user: User) => {
    router.push({ name: 'users.edit', params: { id: user.id } });
};

const deleteUser = async (user: User) => {
    const confirmed = await confirm({
        title: t('common.messages.confirm.title'),
        message: t('modules.core.users.messages.deleteConfirm', { name: user.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) {
        return;
    }

    try {
        await api.delete(`/admin/core/users/${user.id}`);
        await fetchUsers();
        toast.success.delete('User');
    } catch (error: unknown) {
        logger.error('Failed to delete user:', error);
        toast.error.delete(error as Record<string, unknown>, 'User');
    }
};

const forceLogoutUser = async (user: User) => {
    const confirmed = await confirm({
        title: t('modules.core.users.actions.forceLogout'),
        message: t('modules.core.users.messages.forceLogoutConfirm', { name: user.name }),
        variant: 'warning',
        confirmText: t('modules.core.users.actions.forceLogout'),
    });

    if (!confirmed) {
        return;
    }

    try {
        await api.post(`/admin/core/users/${user.id}/force-logout`);
        
        toast.success.action('User forced logout');
    } catch (error: unknown) {
        logger.error('Failed to force logout user:', error);
        toast.error.action(error as Record<string, unknown>);
    }
};

const verifyUser = async (user: User) => {
    try {
        await api.post(`/admin/core/users/${user.id}/verify`);
        toast.success.action('User verified');
        await fetchUsers();
    } catch (error: unknown) {
        logger.error('Failed to verify user:', error);
        toast.error.action(error as Record<string, unknown>);
    }
};

const restoreUser = async (user: User) => {
    const confirmed = await confirm({
        title: 'Restore User',
        message: `Are you sure you want to restore ${user.name}?`,
        variant: 'info',
        confirmText: 'Restore',
    });

    if (!confirmed) return;

    try {
        await api.post(`/admin/core/users/${user.id}/restore`);
        toast.success.action('User restored');
        await fetchUsers();
    } catch (error: unknown) {
        logger.error('Failed to restore user:', error);
        toast.error.action(error as Record<string, unknown>);
    }
};

const forceDeleteUser = async (user: User) => {
    const confirmed = await confirm({
        title: 'Permanently Delete User',
        message: `Are you sure you want to PERMANENTLY delete ${user.name}? This action cannot be undone.`,
        variant: 'danger',
        confirmText: 'Force Delete',
    });

    if (!confirmed) return;

    try {
        await api.delete(`/admin/core/users/${user.id}/force-delete`);
        toast.success.action('User permanently deleted');
        await fetchUsers();
    } catch (error: unknown) {
        logger.error('Failed to force delete user:', error);
        toast.error.action(error as Record<string, unknown>);
    }
};

const selectedIds = ref<number[]>([]);


const bulkActionSelection = ref('');

const handleBulkAction = async (value: string) => {
    if (!value) return;
    await bulkAction(value);
    bulkActionSelection.value = '';
};

const bulkAction = async (action: string) => {
    if (selectedIds.value.length === 0) return;
    
    let confirmMessage = '';
    let confirmVariant = 'warning';
    let confirmTitle = t('modules.cms.content.list.bulkActions');

    if (action === 'delete') {
        confirmMessage = t('common.messages.confirm.bulkDelete', { count: selectedIds.value.length });
        confirmVariant = 'danger';
        confirmTitle = t('common.actions.delete');
    } else if (action === 'force_logout') {
        confirmMessage = t('modules.core.users.messages.bulkForceLogoutConfirm', { count: selectedIds.value.length }) || `Force logout ${selectedIds.value.length} users?`;
        confirmTitle = t('modules.core.users.actions.forceLogout');
    } else if (action === 'verify') {
        confirmMessage = t('modules.core.users.messages.bulkVerifyConfirm', { count: selectedIds.value.length }) || `Verify ${selectedIds.value.length} users?`;
        confirmTitle = t('modules.core.users.actions.verify');
    } else if (action === 'restore') {
        confirmMessage = `Restore ${selectedIds.value.length} users?`;
        confirmTitle = 'Restore Users';
        confirmVariant = 'info';
    } else if (action === 'force_delete') {
         confirmMessage = `Permanently delete ${selectedIds.value.length} users? This cannot be undone.`;
         confirmTitle = 'Force Delete Users';
        confirmVariant = 'danger';
    }

    const confirmed = await confirm({
        title: confirmTitle,
        message: confirmMessage,
        variant: confirmVariant as 'success' | 'warning' | 'info' | 'danger',
        confirmText: t('common.actions.confirm') || 'Confirm',
    });

    if (!confirmed) {
        bulkActionSelection.value = '';
        return;
    }

    try {
        await api.post('/admin/core/users/bulk-action', {
            ids: selectedIds.value,
            action: action
        });
        
        selectedIds.value = [];
        await fetchUsers();
        
        toast.success.action('Bulk action successful');
    } catch (error: unknown) {
        logger.error('Bulk action failed:', error);
        toast.error.action(error as Record<string, unknown>);
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

watch([search, roleFilter, verificationFilter, trashedFilter, activeStatFilter], () => {
    if (pagination.value) {
        pagination.value.current_page = 1;
    }
    fetchUsers();
});

const route = useRoute();

onMounted(() => {
    // Check for search query param from Global Search
    if (route.query.q) {
        search.value = route.query.q as string;
    }
    fetchUsers();
    fetchRoles();
    fetchStats();
});
</script>
