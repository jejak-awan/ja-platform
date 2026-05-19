<template>
  <div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-foreground flex items-center gap-2">
          <Puzzle class="w-7 h-7 text-indigo-500" />
          {{ trans.title }}
        </h1>
        <p class="text-sm text-muted-foreground mt-1">
          {{ trans.subtitle }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <Button
          variant="secondary"
          class="flex items-center gap-2 hover:bg-secondary/80"
          @click="openGitModal"
        >
          <GitBranch class="w-4 h-4" />
          {{ trans.gitBtn }}
        </Button>
        <Button
          class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white border-0"
          @click="openUploadModal"
        >
          <UploadIcon class="w-4 h-4" />
          {{ trans.uploadBtn }}
        </Button>
      </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-card/30 backdrop-blur-md p-4 rounded-xl border border-border/50">
      <div class="relative flex-1 max-w-md">
        <SearchIcon class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="trans.searchPlaceholder"
          class="w-full bg-background border border-border/70 rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 text-foreground"
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
      <span class="text-sm text-muted-foreground mt-4">{{ trans.loading }}</span>
    </div>

    <!-- Extensions Grid List -->
    <div
      v-else-if="filteredExtensions.length === 0"
      class="flex flex-col items-center justify-center py-16 bg-card/20 border border-dashed border-border rounded-xl animate-fade-in"
    >
      <Puzzle class="w-12 h-12 text-muted-foreground/50 mb-3" />
      <h3 class="text-base font-semibold text-foreground">{{ trans.noExtensions }}</h3>
      <p class="text-sm text-muted-foreground max-w-sm text-center mt-1">
        {{ trans.noExtensionsSub }}
      </p>
    </div>

    <div v-else class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <ExtensionCard
          v-for="ext in paginatedExtensions"
          :key="ext.slug"
          :ext="ext"
          :locale="locale"
          :trans="trans"
          :get-localized-description="getLocalizedDescription"
          @toggle-feature="toggleFeatureStatus"
          @toggle-status="toggleExtensionStatus"
          @configure="openSettingsModal"
          @uninstall="uninstallExtension"
        />
      </div>

      <!-- Standard Shared Pagination Component -->
      <div v-if="totalPages > 1" class="mt-8 pt-4 border-t border-border/30">
        <Pagination
          v-model:current-page="currentPage"
          v-model:per-page="itemsPerPage"
          :total-items="filteredExtensions.length"
          :show-per-page="true"
          :show-page-numbers="true"
          :per-page-options="[6, 12, 18, 24, 30]"
          class="bg-transparent border-0 px-0 py-0"
        />
      </div>
    </div>

    <!-- ZIP Upload Modal Component -->
    <UploadModal
      v-model:open="uploadModalOpen"
      :trans="trans"
      :uploading="uploading"
      :upload-error="uploadError"
      @upload="uploadZip"
      @clear-error="uploadError = ''"
    />

    <!-- Configure Settings Modal Component -->
    <ConfigureModal
      v-model:open="settingsModalOpen"
      v-model:raw-settings-json="rawSettingsJson"
      :trans="trans"
      :active-ext-config="activeExtConfig"
      @save="saveSettings"
    />

    <!-- Git Integration Modal Component -->
    <GitModal
      v-model:open="gitModalOpen"
      :trans="trans"
      :cloning="cloning"
      :clone-error="cloneError"
      @clone="cloneGitRepo"
      @clear-error="cloneError = ''"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';
import api from '@/engine/api/client';
import toast from '@/shared/services/toastService';
import { useConfirm } from '@/shared/composables/useConfirm';
import { Button, Pagination } from '@/shared/components/ui';

// Sub-components
import ExtensionCard from './components/ExtensionCard.vue';
import UploadModal from './components/UploadModal.vue';
import GitModal from './components/GitModal.vue';
import ConfigureModal from './components/ConfigureModal.vue';

// Lucide icons
import SearchIcon from 'lucide-vue-next/dist/esm/icons/search.js';
import Puzzle from 'lucide-vue-next/dist/esm/icons/puzzle.js';
import UploadIcon from 'lucide-vue-next/dist/esm/icons/upload.js';
import GitBranch from 'lucide-vue-next/dist/esm/icons/git-branch.js';

interface FeatureItem {
    id: string;
    extension_slug: string;
    slug: string;
    name: string;
    description?: string;
    category: string;
    is_active: boolean;
}

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
    features?: FeatureItem[];
}

const { locale } = useI18n();
const router = useRouter();
const route = useRoute();

const extensions = ref<ExtensionItem[]>([]);
const loading = ref(false);
const searchQuery = ref('');
const activeTab = ref('all');

// Computed dictionary translations based on active locale
const trans = computed(() => {
    const isId = locale.value === 'id';
    return {
        title: isId ? 'Ekstensi & Galeri Aplikasi' : 'Extensions & App Store',
        subtitle: isId 
            ? 'Kelola modul dinamis dan plugin plug-and-play secara aman dengan gerbang sandbox.' 
            : 'Manage dynamic plug-and-play modules and plugins securely with sandbox gating.',
        searchPlaceholder: isId ? 'Cari berdasarkan nama, slug, atau pembuat...' : 'Search by name, slug, or author...',
        gitBtn: isId ? 'Integrasi Git' : 'Git Integration',
        uploadBtn: isId ? 'Unggah ZIP' : 'Upload ZIP',
        all: isId ? 'Semua' : 'All',
        modules: isId ? 'Modul' : 'Modules',
        plugins: isId ? 'Plugin' : 'Plugins',
        author: isId ? 'Pembuat' : 'Author',
        license: isId ? 'Lisensi' : 'License',
        core: isId ? 'Core Sistem' : 'System Core',
        locked: isId ? 'Core Terkunci' : 'Locked Core',
        activate: isId ? 'Aktifkan' : 'Activate',
        deactivate: isId ? 'Nonaktifkan' : 'Deactivate',
        uninstall: isId ? 'Copot Pemasangan' : 'Uninstall',
        configure: isId ? 'Konfigurasi' : 'Configure',
        constituentFeatures: isId ? 'Daftar Fitur Bawaan' : 'Constituent Features',
        noFeatures: isId ? 'Tidak ada fitur terdaftar.' : 'No features registered.',
        noExtensions: isId ? 'Ekstensi tidak ditemukan' : 'No extensions found',
        noExtensionsSub: isId ? 'Coba cari dengan kata kunci lain atau unggah ZIP baru untuk memasangnya.' : 'Try searching for different keywords or upload a new ZIP package to install.',
        yes: isId ? 'Ya' : 'Yes',
        no: isId ? 'Tidak' : 'No',
        page: isId ? 'Halaman' : 'Page',
        next: isId ? 'Berikutnya' : 'Next',
        prev: isId ? 'Sebelumnya' : 'Previous',
        showing: isId ? 'Menampilkan' : 'Showing',
        of: isId ? 'dari' : 'of',
        loading: isId ? 'Memindai ekstensi sistem...' : 'Scanning system extensions...',

        // Modals
        uploadTitle: isId ? 'Unggah ZIP Ekstensi' : 'Upload Extension ZIP',
        sandboxNoticeTitle: isId ? 'Pemindai Sandbox Aktif' : 'Sandbox Gate Scanner Enabled',
        sandboxNoticeDesc: isId
            ? 'Semua file PHP di dalam berkas ZIP akan dipindai secara real-time dari perintah sistem terlarang (exec, eval, shell_exec) sebelum diekstraksi.'
            : 'All PHP files inside the ZIP will be scanned in real-time for forbidden system commands (exec, eval, shell_exec) before extraction.',
        dragDropLabel: isId ? 'Seret & lepas file ZIP di sini, atau klik untuk memilih' : 'Drag & drop ZIP here, or click to browse',
        manifestNotice: isId ? 'Paket ZIP harus berisi file manifest.json' : 'ZIP package must contain manifest.json',
        cancel: isId ? 'Batal' : 'Cancel',
        installing: isId ? 'Memindai Keamanan & Mengekstrak...' : 'Security Scanning & Extracting...',
        installBtn: isId ? 'Pasang Ekstensi' : 'Install Extension',
        
        configTitle: isId ? 'Konfigurasi Pengaturan' : 'Configure Settings',
        configDesc: isId ? 'Edit variabel pengaturan yang disimpan dalam database untuk paket ini.' : 'Edit settings variables stored in database for this package.',
        saveBtn: isId ? 'Simpan Pengaturan' : 'Save Settings',
        
        gitTitle: isId ? 'Integrasi Repositori Git' : 'Git Repository Integration',
        gitDesc: isId ? 'Tarik dan klon repositori ekstensi Git privat/publik secara dinamis ke dalam JA-Platform!' : 'Pull and clone private/public Git extension repositories dynamically into the JA-Platform!',
        repoUrl: isId ? 'URL Repositori' : 'Repository URL',
        gitNotice: isId ? 'Integrasi tarikan dinamis Git memerlukan konfigurasi token SSH. Fitur ini saat ini berjalan di bawah konsol simulasi dry-run.' : 'Git dynamic pull integrations require SSH token configurations. Feature is currently running under dry-run console.',
        understood: isId ? 'Dimengerti' : 'Understood'
    };
});

const getLocalizedDescription = (ext: ExtensionItem) => {
    const isId = locale.value === 'id';
    if (!isId) {
        const descEnMap: Record<string, string> = {
            system: 'Core system configurations, RBAC user/role permissions, pulse activity logs, settings, and dynamic localizations.',
            security: 'Robust enterprise shield, featuring Multi-Factor Authentication (2FA), firewall rate limit challenge, honeypots, and file integrity check.',
            analytics: 'High-performance visitor traffic auditing, dashboard stats trackers, and event lifecycle counters.',
            infra: 'Mission-critical operations: dynamic Redis caching managers, scheduled cron automation, and database backups.',
            ai: 'Intelligent copilot workspace powered by Gemini models for auto-translations and smart content generation.',
            media: 'Central enterprise file explorer featuring drag-and-drop uploads, directory structures, and image cropper.',
            cms: 'Premium publishing engine with draft autosaving, comment moderation gates, sitemaps, and layout templates.',
            school: 'State-of-the-art academic scheduler, PPDB online admissions, BK student behavior logs, logistics and vacancies.',
            forms: 'Dynamic drag-and-drop form designer with validator engines and response exports.',
            layout: 'Section block layout customizer and widget builder.',
            library: 'Smart book library circulation cataloguing and medical UKS log tracking.',
            newsletter: 'Campaign email broadcast automation and dynamic subscriber newsletters.',
            search: 'High-speed global full-text query indexer.',
            member: 'Membership profile signups and premium subscription packages.'
        };
        return descEnMap[ext.slug] || `Dynamic system ${ext.type} supporting modular platform features.`;
    }
    const descIdMap: Record<string, string> = {
        system: 'Pengaturan core sistem, perizinan pengguna (RBAC), log aktivitas pulse, dan lokalisasi terjemahan.',
        security: 'Perisai keamanan enterprise kokoh, dilengkapi Otentikasi 2FA, rate limit firewall, honeypot, dan pengecekan integritas file.',
        analytics: 'Audit lalu lintas pengunjung berkinerja tinggi, dasbor statistik, dan pelacak event interaksi.',
        infra: 'Operasi kritikal: manajemen Redis cache, penjadwal cron otomatis, dan backup database.',
        ai: 'Asisten kecerdasan buatan (AI Copilot) didukung model Gemini untuk auto-translate dan kreasi konten pintar.',
        media: 'Manajer media pusat dengan drag-and-drop upload, struktur folder, dan pemotong gambar terintegrasi.',
        cms: 'Mesin penerbitan konten premium dengan autosave draft, moderasi komentar, sitemap SEO, dan template tata letak.',
        school: 'Sistem akademik sekolah cerdas, PPDB online, log BK kesiswaan, manajemen asrama logistik, dan lowongan karir.',
        forms: 'Desainer formulir dinamis serbaguna dengan mesin validator dan ekspor repons excel.',
        layout: 'Kustomisasi tata letak widget halaman depan dan pengelolaan blok seksi.',
        library: 'Sirkulasi perpustakaan buku pintar, katalogisasi digital, dan pelacak rekam medis UKS.',
        newsletter: 'Automasi broadcast email kampanye pemasaran dan pelacakan langganan newsletter.',
        search: 'Mesin pencarian indeks teks penuh berkecepatan tinggi secara global.',
        member: 'Pendaftaran keanggotaan dan paket berlangganan premium khusus.'
    };
    return descIdMap[ext.slug] || `Modul sistem dinamis untuk mendukung fitur-fitur modular platform.`;
};

const filterTabs = computed(() => [
    { label: trans.value.all, value: 'all' },
    { label: trans.value.modules, value: 'module' },
    { label: trans.value.plugins, value: 'plugin' }
]);

// Pagination state
const currentPage = ref(1);
const itemsPerPage = ref(6);
const totalPages = computed(() => Math.ceil(filteredExtensions.value.length / itemsPerPage.value));
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage.value);
const endIndex = computed(() => startIndex.value + itemsPerPage.value);
const paginatedExtensions = computed(() => {
    return filteredExtensions.value.slice(startIndex.value, endIndex.value);
});

// Watch query search or active tab filters to reset current page back to 1
watch([searchQuery, activeTab], () => {
    currentPage.value = 1;
});

// Modals state
const uploadModalOpen = ref(false);
const settingsModalOpen = ref(false);
const gitModalOpen = ref(false);

const uploading = ref(false);
const uploadError = ref('');

const cloning = ref(false);
const cloneError = ref('');

const activeExtConfig = ref<ExtensionItem | null>(null);
const rawSettingsJson = ref('{}');

const togglingFeature = ref<Record<string, boolean>>({});

const toggleFeatureStatus = async (feature: FeatureItem) => {
    togglingFeature.value[feature.slug] = true;
    const targetStatus = !feature.is_active;

    try {
        const response = await api.put(`/manage/infra/extensions/features/${feature.slug}/toggle`, {
            is_active: targetStatus
        });

        if (response.data?.success) {
            feature.is_active = targetStatus;
            toast.success(`Feature ${feature.name} has been ${targetStatus ? 'enabled' : 'disabled'}!`);
        } else {
            toast.error(response.data?.message || 'Failed to update feature status.');
        }
    } catch (err: unknown) {
        toast.error('Failed to toggle feature.');
    } finally {
        togglingFeature.value[feature.slug] = false;
    }
};

const { confirm } = useConfirm();

// Fetch Extensions
const fetchExtensions = async () => {
    loading.value = true;
    try {
        const response = await api.get('/manage/infra/extensions');
        if (Array.isArray(response.data)) {
            extensions.value = response.data;
        } else if (response.data?.success) {
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
        const isSuccess = response.status === 200 || response.status === 201 || response.data?.success || (response.data && response.data.slug === ext.slug);
        if (isSuccess) {
            toast.success(`${ext.name} has been successfully ${isActivating ? 'activated' : 'deactivated'}!`);
            await fetchExtensions();
        } else {
            toast.error(`Failed to ${action} extension.`);
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
        const isSuccess = response.status === 200 || response.status === 204 || response.data?.success || response.data === null;
        if (isSuccess) {
            toast.success('Extension purged and deleted successfully!');
            await fetchExtensions();
        }
    } catch (err: unknown) {
        toast.error('Failed to uninstall extension.');
    }
};

// ZIP uploader triggers
const openUploadModal = () => {
    uploadError.value = '';
    uploadModalOpen.value = true;
};

const uploadZip = async (file: File) => {
    uploading.value = true;
    uploadError.value = '';

    const formData = new FormData();
    formData.append('file', file);

    try {
        const response = await api.post('/manage/infra/extensions/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        const isSuccess = response.status === 200 || response.status === 201 || response.data?.success || (response.data && response.data.slug);
        if (isSuccess) {
            toast.success('Dynamic plugin installed successfully! Sandbox guard scan PASSED! ✅');
            uploadModalOpen.value = false;
            await fetchExtensions();
        } else {
            uploadError.value = 'Failed to install ZIP.';
        }
    } catch (err: any) {
        uploadError.value = err.response?.data?.message || 'Security gate: Banned keyword/command execution code detected!';
    } finally {
        uploading.value = false;
    }
};

// Configure Settings Modal
const openSettingsModal = (ext: ExtensionItem) => {
    // Check if the module has a dedicated settings page route
    const routeMap: Record<string, { name: string; query?: Record<string, string> } | string> = {
        system: { name: 'settings', query: { tab: 'system' } },
        security: { name: 'settings', query: { tab: 'security' } },
        infra: { name: 'settings', query: { tab: 'performance' } },
        ai: { name: 'settings', query: { tab: 'ai' } },
        media: { name: 'settings', query: { tab: 'media' } },
        analytics: { name: 'settings', query: { tab: 'analytics' } },
        cms: { name: 'cms-settings' },
        school: { name: 'schools.index' }, // Links directly to School Unit Management!
        forms: '/dash/forms', // Forms module settings
        library: '/dash/library',
        newsletter: '/dash/newsletter',
        search: { name: 'settings', query: { tab: 'system' } } // Search sits under system settings
    };

    const target = routeMap[ext.slug];
    if (target) {
        toast.success(`Redirecting to ${ext.name} configuration...`);
        if (typeof target === 'object' && target.name) {
            router.push({
                name: target.name,
                params: {
                    dashboard_slug: route.params.dashboard_slug || 'dash',
                    workspace_uuid: route.params.workspace_uuid || 'system'
                },
                query: target.query
            });
        } else if (typeof target === 'string') {
            const activeSlug = route.params.dashboard_slug || 'dash';
            const activeWorkspace = route.params.workspace_uuid || 'system';
            const resolvedPath = target.replace('/dash', `/${activeSlug}/${activeWorkspace}`);
            router.push(resolvedPath);
        }
    } else {
        // Fallback: If no dedicated path exists (e.g. customized plugin with raw config settings)
        activeExtConfig.value = ext;
        rawSettingsJson.value = JSON.stringify(ext.settings || {}, null, 2);
        settingsModalOpen.value = true;
    }
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

        const isSuccess = response.status === 200 || response.data?.success || (response.data && response.data.slug === activeExtConfig.value.slug);
        if (isSuccess) {
            toast.success('Configuration parameters updated successfully!');
            settingsModalOpen.value = false;
            await fetchExtensions();
        }
    } catch (err: unknown) {
        toast.error('Failed to save settings.');
    }
};

const openGitModal = () => {
    cloneError.value = '';
    gitModalOpen.value = true;
};

const cloneGitRepo = async (repoUrl: string) => {
    cloning.value = true;
    cloneError.value = '';

    try {
        const response = await api.post('/manage/infra/extensions/git-clone', {
            repo_url: repoUrl
        });

        const isSuccess = response.status === 200 || response.status === 201 || response.data?.success;
        if (isSuccess) {
            toast.success('Dynamic plugin cloned and installed successfully from Git! ✅');
            gitModalOpen.value = false;
            await fetchExtensions();
        } else {
            cloneError.value = 'Failed to clone extension.';
        }
    } catch (err: any) {
        cloneError.value = err.response?.data?.message || 'Security gate: Banned keyword or Git signature validation failed!';
    } finally {
        cloning.value = false;
    }
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
