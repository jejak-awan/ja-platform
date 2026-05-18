<template>
  <div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-foreground flex items-center gap-2">
          <Puzzle class="w-7 h-7 text-indigo-500" />
          Extensions & App Store
        </h1>
        <p class="text-sm text-muted-foreground mt-1">
          Manage dynamic plug-and-play modules and plugins securely with sandbox gating.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <Button
          variant="secondary"
          class="flex items-center gap-2"
          @click="openGitModal"
        >
          <GitBranch class="w-4 h-4" />
          Git Integration
        </Button>
        <Button
          class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white border-0"
          @click="openUploadModal"
        >
          <Upload class="w-4 h-4" />
          Upload ZIP
        </Button>
      </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-card/30 backdrop-blur-md p-4 rounded-xl border border-border/50">
      <div class="relative flex-1 max-w-md">
        <Search class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search by name, slug, or author..."
          class="w-full bg-background border border-border/70 rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
        />
      </div>

      <div class="flex items-center gap-2">
        <Button
          v-for="tab in filterTabs"
          :key="tab.value"
          variant="ghost"
          :class="['px-4 py-2 text-sm rounded-lg border-0 transition-colors', activeTab === tab.value ? 'bg-indigo-500/10 text-indigo-400 font-semibold' : 'text-muted-foreground hover:bg-muted/10']"
          @click="activeTab = tab.value"
        >
          {{ tab.label }}
        </Button>
      </div>
    </div>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="flex flex-col items-center justify-center py-20"
    >
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-500" />
      <span class="text-sm text-muted-foreground mt-4">Scanning system extensions...</span>
    </div>

    <!-- Extensions Grid List -->
    <div
      v-else-if="filteredExtensions.length === 0"
      class="flex flex-col items-center justify-center py-16 bg-card/20 border border-dashed border-border rounded-xl"
    >
      <Puzzle class="w-12 h-12 text-muted-foreground/50 mb-3" />
      <h3 class="text-base font-semibold text-foreground">No extensions found</h3>
      <p class="text-sm text-muted-foreground max-w-sm text-center mt-1">
        Try searching for different keywords or upload a new ZIP package to install.
      </p>
    </div>

    <div
      v-else
      class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
    >
      <Card
        v-for="ext in filteredExtensions"
        :key="ext.slug"
        class="group relative flex flex-col justify-between overflow-hidden bg-card/20 backdrop-blur-md border border-border/50 hover:border-indigo-500/40 transition-all duration-300 rounded-xl"
      >
        <CardContent class="p-6 flex-1">
          <!-- Card Header Info -->
          <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
              <div :class="['p-2.5 rounded-xl', ext.type === 'module' ? 'bg-indigo-500/10 text-indigo-400' : 'bg-emerald-500/10 text-emerald-400']">
                <Layers
                  v-if="ext.type === 'module'"
                  class="w-5 h-5"
                />
                <Puzzle
                  v-else
                  class="w-5 h-5"
                />
              </div>
              <div>
                <h3 class="font-bold text-foreground text-base tracking-tight leading-none flex items-center gap-1.5">
                  {{ ext.name }}
                  <span class="text-xs text-muted-foreground font-normal">v{{ ext.version }}</span>
                </h3>
                <span class="text-xs text-indigo-400/80 font-mono mt-1 block">{{ ext.slug }}</span>
              </div>
            </div>

            <Badge :variant="ext.status === 'active' ? 'success' : 'secondary'">
              {{ ext.status }}
            </Badge>
          </div>

          <p class="text-sm text-muted-foreground mt-4 line-clamp-2">
            Dynamic system {{ ext.type }} supporting modular platform features.
          </p>

          <!-- Meta Info Table -->
          <div class="mt-6 space-y-1.5 border-t border-border/30 pt-4 text-xs">
            <div class="flex justify-between">
              <span class="text-muted-foreground">Author:</span>
              <span class="font-medium text-foreground">{{ ext.author || 'Jejakawan' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-muted-foreground">License:</span>
              <span class="font-mono text-foreground">{{ ext.license || 'MIT' }}</span>
            </div>
            <div class="flex justify-between" v-if="ext.is_core">
              <span class="text-muted-foreground">System Core:</span>
              <span class="text-indigo-400 font-semibold">Yes</span>
            </div>
          </div>
        </CardContent>

        <!-- Card Footer Actions -->
        <CardFooter class="px-6 py-4 bg-muted/20 border-t border-border/30 flex items-center justify-between gap-2">
          <div>
            <Button
              v-if="ext.status === 'active'"
              variant="secondary"
              size="sm"
              class="flex items-center gap-1.5"
              @click="openSettingsModal(ext)"
            >
              <Settings class="w-3.5 h-3.5" />
              Configure
            </Button>
          </div>

          <div class="flex items-center gap-2">
            <!-- Uninstall Inactive Non-Core -->
            <Button
              v-if="ext.status === 'inactive' && !ext.is_core"
              variant="destructive"
              size="sm"
              class="px-2"
              @click="uninstallExtension(ext.slug)"
            >
              <Trash2 class="w-4 h-4" />
            </Button>

            <!-- Activate / Deactivate Toggle -->
            <Button
              v-if="!ext.is_core"
              :variant="ext.status === 'active' ? 'destructive' : 'secondary'"
              size="sm"
              class="flex items-center gap-1.5 font-semibold text-xs border-0"
              @click="toggleExtensionStatus(ext)"
            >
              <Power class="w-3.5 h-3.5" />
              {{ ext.status === 'active' ? 'Deactivate' : 'Activate' }}
            </Button>
            <span
              v-else
              class="text-xs text-muted-foreground font-mono"
            >Locked Core</span>
          </div>
        </CardFooter>
      </Card>
    </div>

    <!-- ZIP Upload Modal -->
    <Dialog v-model:open="uploadModalOpen">
      <DialogContent class="sm:max-w-md bg-card border border-border/80 rounded-xl">
        <DialogHeader>
          <DialogTitle class="flex items-center gap-2">
            <Upload class="w-5 h-5 text-indigo-500" />
            Upload Extension ZIP
          </DialogTitle>
        </DialogHeader>

        <!-- Security Scanner Notice -->
        <div class="bg-indigo-500/10 border border-indigo-500/20 p-3 rounded-lg flex gap-3 text-xs text-indigo-400 mt-2">
          <ShieldCheck class="w-6 h-6 shrink-0 mt-0.5" />
          <div>
            <span class="font-bold block">Sandbox Gate Scanner Enabled</span>
            All PHP files inside the ZIP will be scanned in real-time for forbidden system commands (exec, eval, shell_exec) before extraction.
          </div>
        </div>

        <div class="mt-6 space-y-4">
          <div
            class="border-2 border-dashed border-border/80 rounded-xl p-8 flex flex-col items-center justify-center cursor-pointer hover:border-indigo-500/50 transition-colors"
            @dragover.prevent
            @drop.prevent="handleFileDrop"
            @click="triggerFileInput"
          >
            <input
              ref="fileInput"
              type="file"
              accept=".zip"
              class="hidden"
              @change="handleFileSelect"
            />
            <Upload class="w-10 h-10 text-muted-foreground/60 mb-2 animate-bounce" />
            <span class="text-sm font-semibold text-foreground text-center">
              {{ selectedFile ? selectedFile.name : 'Drag & drop ZIP here, or click to browse' }}
            </span>
            <span class="text-xs text-muted-foreground mt-1">ZIP package must contain manifest.json</span>
          </div>

          <div
            v-if="uploadError"
            class="bg-rose-500/10 border border-rose-500/20 p-3 rounded-lg flex gap-2 text-xs text-rose-400"
          >
            <AlertTriangle class="w-5 h-5 shrink-0" />
            <span>{{ uploadError }}</span>
          </div>
        </div>

        <DialogFooter class="mt-6 flex items-center justify-end gap-2">
          <Button
            variant="secondary"
            @click="uploadModalOpen = false"
          >
            Cancel
          </Button>
          <Button
            :disabled="!selectedFile || uploading"
            class="bg-indigo-600 hover:bg-indigo-700 text-white border-0"
            @click="uploadZip"
          >
            <span v-if="uploading">Security Scanning & Extracting...</span>
            <span v-else>Install Extension</span>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Configure Settings Modal -->
    <Dialog v-model:open="settingsModalOpen">
      <DialogContent class="sm:max-w-lg bg-card border border-border/80 rounded-xl">
        <DialogHeader>
          <DialogTitle class="flex items-center gap-2">
            <Settings class="w-5 h-5 text-indigo-500" />
            Configure {{ activeExtConfig?.name }} Settings
          </DialogTitle>
        </DialogHeader>

        <div class="mt-4 space-y-4">
          <div class="space-y-1">
            <label class="text-xs text-muted-foreground uppercase font-mono">Dynamic Settings Schema</label>
            <p class="text-xs text-muted-foreground mb-4">Edit settings variables stored in database for this package.</p>
            <textarea
              v-model="rawSettingsJson"
              rows="6"
              class="w-full bg-background border border-border rounded-lg p-3 font-mono text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
              placeholder="{}"
            />
          </div>
        </div>

        <DialogFooter class="mt-6 flex items-center justify-end gap-2">
          <Button
            variant="secondary"
            @click="settingsModalOpen = false"
          >
            Cancel
          </Button>
          <Button
            class="bg-indigo-600 hover:bg-indigo-700 text-white border-0"
            @click="saveSettings"
          >
            Save Settings
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Git Integration Placeholder -->
    <Dialog v-model:open="gitModalOpen">
      <DialogContent class="sm:max-w-md bg-card border border-border/80 rounded-xl">
        <DialogHeader>
          <DialogTitle class="flex items-center gap-2">
            <GitBranch class="w-5 h-5 text-indigo-500" />
            Git Repository Integration
          </DialogTitle>
        </DialogHeader>

        <div class="mt-4 space-y-4 text-sm text-muted-foreground leading-relaxed">
          <p>
            Pull and clone private/public Git extension repositories dynamically into the JA-Platform!
          </p>
          <div class="space-y-2">
            <label class="text-xs text-foreground font-semibold">Repository URL</label>
            <input
              type="text"
              placeholder="https://github.com/username/ja-plugin-whatsapp.git"
              class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground focus:outline-none"
              disabled
            />
          </div>
          <div class="bg-amber-500/10 border border-amber-500/20 p-3 rounded-lg text-xs text-amber-400 mt-2">
            Git dynamic pull integrations require SSH token configurations. Feature is currently running under dry-run console.
          </div>
        </div>

        <DialogFooter class="mt-6">
          <Button
            class="bg-indigo-600 hover:bg-indigo-700 text-white border-0"
            @click="gitModalOpen = false"
          >
            Understood
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import api from '@/engine/api/client';
import toast from '@/shared/services/toastService';
import { useConfirm } from '@/shared/composables/useConfirm';
import { Card, CardContent, CardFooter, Badge, Button, Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/shared/components/ui';

// Lucide icons
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Puzzle from 'lucide-vue-next/dist/esm/icons/puzzle.js';
import Layers from 'lucide-vue-next/dist/esm/icons/layers.js';
import Upload from 'lucide-vue-next/dist/esm/icons/upload.js';
import GitBranch from 'lucide-vue-next/dist/esm/icons/git-branch.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Power from 'lucide-vue-next/dist/esm/icons/power.js';
import AlertTriangle from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';

interface ExtensionItem {
    id: string;
    slug: string;
    type: 'module' | 'plugin';
    name: string;
    version: string;
    status: 'active' | 'inactive';
    is_core: boolean;
    author?: string;
    license?: string;
    settings?: Record<string, unknown>;
}

const extensions = ref<ExtensionItem[]>([]);
const loading = ref(false);
const searchQuery = ref('');
const activeTab = ref('all');

const filterTabs = [
    { label: 'All', value: 'all' },
    { label: 'Modules', value: 'module' },
    { label: 'Plugins', value: 'plugin' }
];

// Modals state
const uploadModalOpen = ref(false);
const settingsModalOpen = ref(false);
const gitModalOpen = ref(false);

const selectedFile = ref<File | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const uploading = ref(false);
const uploadError = ref('');

const activeExtConfig = ref<ExtensionItem | null>(null);
const rawSettingsJson = ref('{}');

const { confirm } = useConfirm();

// Fetch Extensions
const fetchExtensions = async () => {
    loading.value = true;
    try {
        const response = await api.get('/manage/infra/extensions');
        if (response.data?.success) {
            extensions.value = response.data.data || [];
        }
    } catch (err: unknown) {
        toast.error('Failed to load extensions list.');
    } finally {
        loading.value = false;
    }
};

// Filter logic
const filteredExtensions = computed(() => {
    return extensions.value.filter(ext => {
        const matchesSearch = ext.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            ext.slug.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (ext.author || '').toLowerCase().includes(searchQuery.value.toLowerCase());
        
        const matchesTab = activeTab.value === 'all' || ext.type === activeTab.value;
        return matchesSearch && matchesTab;
    });
});

// Toggle Status (Activate / Deactivate)
const toggleExtensionStatus = async (ext: ExtensionItem) => {
    const action = ext.status === 'active' ? 'deactivate' : 'activate';
    const isActivating = action === 'activate';

    const confirmed = await confirm({
        title: `${isActivating ? 'Activate' : 'Deactivate'} ${ext.name}?`,
        message: isActivating
            ? `Are you sure you want to activate ${ext.name}? This will run all database schema migrations dynamically.`
            : `Are you sure you want to deactivate ${ext.name}? Features will immediately become unavailable.`,
        variant: isActivating ? 'warning' : 'danger',
        confirmText: isActivating ? 'Activate' : 'Deactivate'
    });

    if (!confirmed) return;

    try {
        const response = await api.post(`/manage/infra/extensions/${ext.slug}/${action}`);
        if (response.data?.success) {
            toast.success(`${ext.name} has been successfully ${isActivating ? 'activated' : 'deactivated'}!`);
            await fetchExtensions();
        } else {
            toast.error(response.data?.message || `Failed to ${action} extension.`);
        }
    } catch (err: unknown) {
        toast.error(`Error performing action: ${action}`);
    }
};

// Uninstall
const uninstallExtension = async (slug: string) => {
    const confirmed = await confirm({
        title: 'Uninstall Extension?',
        message: 'Are you sure you want to completely uninstall this extension? This will PERMANENTLY ERASE all custom code folders and file assets on the disk! This action is irreversible.',
        variant: 'danger',
        confirmText: 'Uninstall & Purge File'
    });

    if (!confirmed) return;

    try {
        const response = await api.delete(`/manage/infra/extensions/${slug}/uninstall`);
        if (response.data?.success) {
            toast.success('Extension purged and deleted successfully!');
            await fetchExtensions();
        }
    } catch (err: unknown) {
        toast.error('Failed to uninstall extension.');
    }
};

// ZIP uploader triggers
const openUploadModal = () => {
    selectedFile.value = null;
    uploadError.value = '';
    uploadModalOpen.value = true;
};

const triggerFileInput = () => {
    fileInput.value?.click();
};

const handleFileSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        selectedFile.value = target.files[0] ?? null;
        uploadError.value = '';
    }
};

const handleFileDrop = (e: DragEvent) => {
    if (e.dataTransfer && e.dataTransfer.files.length > 0) {
        selectedFile.value = e.dataTransfer.files[0] ?? null;
        uploadError.value = '';
    }
};

const uploadZip = async () => {
    if (!selectedFile.value) return;

    uploading.value = true;
    uploadError.value = '';

    const formData = new FormData();
    formData.append('file', selectedFile.value);

    try {
        const response = await api.post('/manage/infra/extensions/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        if (response.data?.success) {
            toast.success('Dynamic plugin installed successfully! Sandbox guard scan PASSED! ✅');
            uploadModalOpen.value = false;
            await fetchExtensions();
        } else {
            uploadError.value = response.data?.message || 'Failed to install ZIP.';
        }
    } catch (err: any) {
        uploadError.value = err.response?.data?.message || 'Security gate: Banned keyword/command execution code detected!';
    } finally {
        uploading.value = false;
    }
};

// Configure Settings Modal
const openSettingsModal = (ext: ExtensionItem) => {
    activeExtConfig.value = ext;
    rawSettingsJson.value = JSON.stringify(ext.settings || {}, null, 2);
    settingsModalOpen.value = true;
};

const saveSettings = async () => {
    if (!activeExtConfig.value) return;

    let parsedSettings: Record<string, unknown>;
    try {
        parsedSettings = JSON.parse(rawSettingsJson.value);
    } catch (e) {
        toast.error('Invalid JSON structure. Please verify formatting.');
        return;
    }

    try {
        const response = await api.put(`/manage/infra/extensions/${activeExtConfig.value.slug}/settings`, {
            settings: parsedSettings
        });

        if (response.data?.success) {
            toast.success('Configuration parameters updated successfully!');
            settingsModalOpen.value = false;
            await fetchExtensions();
        }
    } catch (err: unknown) {
        toast.error('Failed to save settings.');
    }
};

const openGitModal = () => {
    gitModalOpen.value = true;
};

onMounted(() => {
    fetchExtensions();
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;  
  overflow: hidden;
}
</style>
