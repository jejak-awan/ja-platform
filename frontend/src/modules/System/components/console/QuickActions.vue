<template>
  <Card class="quick-actions h-full border-border/40 bg-card">
    <CardHeader class="pb-3">
      <CardTitle class="text-xl font-bold flex items-center gap-2">
        <Zap class="w-5 h-5 text-warning fill-warning" />
        {{ $t('features.dashboard.widgets.quickActions.title') }}
      </CardTitle>
    </CardHeader>
    
    <CardContent>
      <div class="grid grid-cols-2 gap-3">
        <!-- Create Post -->
        <button
          v-if="authStore.hasPermission('create content')"
          class="flex flex-col items-center justify-center p-3 rounded-lg hover:bg-primary/5 group relative overflow-hidden"
          :disabled="loading"
          @click="handleAction('create-post')"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-primary/10 text-primary group-hover:scale-110">
            <FileEdit class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('features.dashboard.widgets.quickActions.createPost') }}
          </span>
        </button>
 
        <!-- Create Page -->
        <button
          v-if="authStore.hasPermission('create content')"
          class="flex flex-col items-center justify-center p-3 rounded-lg hover:bg-primary/5 group"
          :disabled="loading"
          @click="handleAction('create-page')"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-info/10 text-info group-hover:scale-110">
            <PlusSquare class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('features.dashboard.widgets.quickActions.createPage') }}
          </span>
        </button>
 
        <!-- Upload Media -->
        <button
          v-if="authStore.hasPermission('upload media') || authStore.hasPermission('create media')"
          class="flex flex-col items-center justify-center p-3 rounded-lg hover:bg-primary/5 group"
          :disabled="loading"
          @click="handleAction('upload-media')"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-success/10 text-success group-hover:scale-110">
            <Upload class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('features.dashboard.widgets.quickActions.uploadMedia') }}
          </span>
        </button>
 
        <!-- Create Category -->
        <button
          v-if="authStore.hasPermission('create categories')"
          class="flex flex-col items-center justify-center p-3 rounded-lg hover:bg-primary/5 group"
          :disabled="loading"
          @click="handleAction('create-category')"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-warning/10 text-warning group-hover:scale-110">
            <Hash class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('features.dashboard.widgets.quickActions.createCategory') }}
          </span>
        </button>
 
        <!-- Create Tag -->
        <button
          v-if="authStore.hasPermission('create tags')"
          class="flex flex-col items-center justify-center p-3 rounded-lg hover:bg-primary/5 group"
          :disabled="loading"
          @click="handleAction('create-tag')"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-primary/10 text-primary group-hover:scale-110">
            <Tag class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('features.dashboard.widgets.quickActions.createTag') }}
          </span>
        </button>
 
        <!-- Manage Users -->
        <button
          v-if="authStore.hasPermission('view users')"
          class="flex flex-col items-center justify-center p-3 rounded-lg hover:bg-primary/5 group"
          :disabled="loading"
          @click="handleAction('manage-users')"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-primary/10 text-primary group-hover:scale-110">
            <UserCog class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('features.dashboard.widgets.quickActions.manageUsers') }}
          </span>
        </button>
 
        <!-- View Comments -->
        <button
          v-if="authStore.hasPermission('view comments')"
          class="flex flex-col items-center justify-center p-3 rounded-lg hover:bg-primary/5 group"
          :disabled="loading"
          @click="handleAction('view-comments')"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-warning/10 text-warning group-hover:scale-110">
            <MessageSquare class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('features.dashboard.widgets.quickActions.viewComments') }}
          </span>
        </button>
 
        <!-- Settings -->
        <button
          v-if="authStore.hasPermission('view settings')"
          class="flex flex-col items-center justify-center p-3 rounded-lg hover:bg-slate-500/5 group"
          :disabled="loading"
          @click="handleAction('settings')"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-muted text-muted-foreground group-hover:scale-110">
            <Settings class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('features.dashboard.widgets.quickActions.settings') }}
          </span>
        </button>

        <!-- Command Runner -->
        <button
          v-if="authStore.hasPermission('manage system')"
          class="flex flex-col items-center justify-center p-3 rounded-lg hover:bg-primary/5 group"
          :disabled="loading"
          @click="handleAction('command-runner')"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-warning/10 text-warning group-hover:scale-110">
            <Terminal class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('features.dashboard.widgets.quickActions.commandRunner') }}
          </span>
        </button>
      </div>
      
      <!-- Recent Actions -->
      <div
        v-if="showRecent && recentActions.length > 0"
        class="mt-6 pt-4 border-t border-border/40"
      >
        <h4 class="text-xs font-bold text-muted-foreground mb-3">
          {{ $t('features.dashboard.widgets.quickActions.recentActions') }}
        </h4>
        <div class="space-y-1">
          <div
            v-for="action in recentActions.slice(0, 3)"
            :key="action.id"
            class="flex items-center p-2 rounded-lg text-sm text-muted-foreground hover:text-foreground hover:bg-muted/50 cursor-pointer group"
            @click="repeatAction(action)"
          >
            <Clock class="w-4 h-4 mr-2 opacity-50 group-hover:opacity-100" />
            <span class="flex-1 truncate font-medium">{{ getActionLabel(action.action) }}</span>
            <span class="text-[10px] tabular-nums opacity-50">{{ formatTime(action.timestamp) }}</span>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, type RouteLocationRaw } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/modules/System/stores/auth';
import { 
    Card, 
    CardHeader, 
    CardTitle, 
    CardContent 
} from '@/shared/components/ui';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import FileEdit from 'lucide-vue-next/dist/esm/icons/file-pen.js';
import PlusSquare from 'lucide-vue-next/dist/esm/icons/square-plus.js';
import Upload from 'lucide-vue-next/dist/esm/icons/upload.js';
import Hash from 'lucide-vue-next/dist/esm/icons/hash.js';
import Tag from 'lucide-vue-next/dist/esm/icons/tag.js';
import UserCog from 'lucide-vue-next/dist/esm/icons/user-cog.js';
import MessageSquare from 'lucide-vue-next/dist/esm/icons/message-square.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import Terminal from 'lucide-vue-next/dist/esm/icons/terminal.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';

interface RecentAction {
    id: string;
    action: string;
    timestamp: string;
}

const { t } = useI18n();
const router = useRouter();
const authStore = useAuthStore();

const props = withDefaults(defineProps<{
  showRecent?: boolean;
}>(), {
  showRecent: true,
});

const loading = ref(false);
const recentActions = ref<RecentAction[]>([]);

const actionRoutes: Record<string, RouteLocationRaw> = {
  'create-post': { name: 'contents.create', query: { type: 'post' } },
  'create-page': { name: 'contents.create', query: { type: 'page' } },
  'upload-media': { name: 'media' },
  'create-category': { name: 'categories.index' },
  'create-tag': { name: 'tags' },
  'manage-users': { name: 'users.index' },
  'view-comments': { name: 'comments.index' },
  'settings': { name: 'settings' },
  'command-runner': { name: 'scheduled-tasks', query: { action: 'run_command' } },
};

const actionLabels: Record<string, string> = {
  'create-post': 'features.dashboard.widgets.quickActions.createPost',
  'create-page': 'features.dashboard.widgets.quickActions.createPage',
  'upload-media': 'features.dashboard.widgets.quickActions.uploadMedia',
  'create-category': 'features.dashboard.widgets.quickActions.createCategory',
  'create-tag': 'features.dashboard.widgets.quickActions.createTag',
  'manage-users': 'features.dashboard.widgets.quickActions.manageUsers',
  'view-comments': 'features.dashboard.widgets.quickActions.viewComments',
  'settings': 'features.dashboard.widgets.quickActions.settings',
  'command-runner': 'features.dashboard.widgets.quickActions.commandRunner',
};

const getActionLabel = (action: string) => {
    const key = actionLabels[action];
    return key ? t(key) : action;
};

const saveRecentAction = (action: string) => {
  const newAction: RecentAction = {
    id: String(Date.now()),
    action,
    timestamp: new Date().toISOString(),
  };
  
  const stored = localStorage.getItem('quickActions_recent');
  const actions: RecentAction[] = stored ? JSON.parse(stored) : [];
  const filtered = actions.filter(a => a.action !== action);
  filtered.unshift(newAction);
  const limited = filtered.slice(0, 10);
  localStorage.setItem('quickActions_recent', JSON.stringify(limited));
  recentActions.value = limited;
};

const handleAction = (action: string) => {
  if (loading.value) return;
  
  loading.value = true;
  saveRecentAction(action);
  const route = actionRoutes[action];
  if (route) {
    router.push(route);
  }
  
  setTimeout(() => {
    loading.value = false;
  }, 500);
};

const repeatAction = (action: RecentAction) => {
  handleAction(action.action);
};

const formatTime = (timestamp: string) => {
  const date = new Date(timestamp);
  const now = new Date();
  const diff = now.getTime() - date.getTime();
  
  const minutes = Math.floor(diff / 60000);
  const hours = Math.floor(diff / 3600000);
  const days = Math.floor(diff / 86400000);
  
  if (minutes < 1) return t('features.dashboard.widgets.recentActivity.time.justNow');
  if (minutes < 60) return t('features.dashboard.widgets.recentActivity.time.ago', { time: `${minutes}m` });
  if (hours < 24) return t('features.dashboard.widgets.recentActivity.time.ago', { time: `${hours}h` });
  return t('features.dashboard.widgets.recentActivity.time.ago', { time: `${days}d` });
};

const loadRecentActions = () => {
  const stored = localStorage.getItem('quickActions_recent');
  if (stored) {
    try {
        recentActions.value = JSON.parse(stored);
    } catch {
        recentActions.value = [];
    }
  }
};

onMounted(() => {
  if (props.showRecent) {
    loadRecentActions();
  }
});
</script>

