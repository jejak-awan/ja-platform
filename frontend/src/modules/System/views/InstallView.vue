<template>
  <div class="min-h-screen bg-background flex items-center justify-center p-4">
    <Card class="w-full max-w-lg border-border/40 shadow-2xl bg-card/50 backdrop-blur-xl overflow-hidden">
      <!-- Header -->
      <div class="bg-primary p-8 text-primary-foreground relative overflow-hidden">
        <div class="relative z-10 flex justify-between items-start">
          <div>
            <h1 class="text-3xl font-bold tracking-tight mb-2">JA-Platform</h1>
            <p class="text-primary-foreground/80 font-medium">{{ t('modules.system.installer.title') }}</p>
          </div>
          
          <!-- Language Switcher -->
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="ghost" size="icon" class="text-primary-foreground hover:bg-white/10 h-8 w-8">
                <Languages class="h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuItem 
                v-for="lang in availableLocales" 
                :key="lang.code"
                @click="setLocale(lang.code)"
                :class="{ 'bg-muted font-bold': currentLocale === lang.code }"
              >
                {{ lang.name }}
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
        <!-- Decorative Background Circle -->
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl pointer-events-none" />
      </div>

      <CardContent class="p-8">
        <div v-if="step === 'requirements'" class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
          <div class="space-y-2">
            <h2 class="text-xl font-semibold text-foreground">{{ t('modules.system.installer.requirements.title') }}</h2>
            <p class="text-sm text-muted-foreground">{{ t('modules.system.installer.requirements.description') }}</p>
          </div>

          <div v-if="loading" class="flex justify-center py-8">
            <Loader2 class="w-8 h-8 animate-spin text-primary" />
          </div>

          <div v-else class="space-y-6">
            <div class="space-y-3">
              <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">{{ t('modules.system.installer.requirements.core') }}</h3>
              <div 
                v-for="(status, key) in requirements" 
                :key="key"
                v-show="!String(key).startsWith('ext_') && String(key) !== 'php_version'"
                class="flex items-center justify-between p-2.5 rounded-lg border border-border/50 bg-muted/30"
              >
                <span class="text-sm font-medium capitalize">{{ String(key).replace('_', ' ') }}</span>
                <div class="flex items-center gap-2">
                  <span class="text-xs text-muted-foreground">{{ status === true ? t('modules.system.installer.requirements.status_ok') : (key === 'php_version' ? status : t('modules.system.installer.requirements.status_missing')) }}</span>
                  <CheckCircle2 v-if="status === true" class="w-4 h-4 text-green-500" />
                  <XCircle v-else class="w-4 h-4 text-destructive" />
                </div>
              </div>
            </div>

            <div class="space-y-3">
              <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">{{ t('modules.system.installer.requirements.extensions') }}</h3>
              <div class="grid grid-cols-2 gap-2">
                <div 
                  v-for="(status, key) in requirements" 
                  :key="key"
                  v-show="String(key).startsWith('ext_')"
                  class="flex items-center justify-between p-2 rounded border border-border/40 bg-card"
                >
                  <span class="text-xs font-medium">{{ String(key).replace('ext_', '') }}</span>
                  <CheckCircle2 v-if="status === true" class="w-3.5 h-3.5 text-green-500" />
                  <XCircle v-else class="w-3.5 h-3.5 text-destructive" />
                </div>
              </div>
            </div>

            <!-- Troubleshooting Section -->
            <div v-if="!isRequirementsMet" class="mt-6 p-4 rounded-lg bg-destructive/5 border border-destructive/20 animate-in fade-in zoom-in duration-300">
                <h3 class="text-sm font-bold text-destructive mb-2 flex items-center gap-2">
                    <XCircle class="w-4 h-4" />
                    {{ t('modules.system.installer.troubleshooting.title', { os: serverOS.distro !== 'unknown' ? serverOS.distro : serverOS.family }) }}
                </h3>
                <ul class="text-xs space-y-2 text-muted-foreground list-disc pl-4">
                    <li v-if="!requirements.php_supported">{{ t('modules.system.installer.troubleshooting.php_version') }}</li>
                    <li v-if="!requirements.writable_env">{{ t('modules.system.installer.troubleshooting.writable_env') }}</li>
                    <li v-if="!requirements.writable_storage">{{ t('modules.system.installer.troubleshooting.writable_storage') }}</li>
                    
                    <!-- Debian/Ubuntu -->
                    <li v-if="!isRequirementsMet && serverOS.distro === 'debian'">
                      {{ t('modules.system.installer.troubleshooting.install_missing') }} <code class="bg-muted px-1 rounded text-foreground">sudo apt install php8.3-{common,pgsql,bcmath,curl,gd,intl,xml,zip,mbstring}</code>
                    </li>

                    <!-- RHEL/CentOS/AlmaLinux -->
                    <li v-if="!isRequirementsMet && serverOS.distro === 'rhel'">
                      {{ t('modules.system.installer.troubleshooting.install_missing') }} <code class="bg-muted px-1 rounded text-foreground">sudo dnf install php-{common,pgsql,bcmath,curl,gd,intl,xml,zip,mbstring}</code>
                    </li>

                    <!-- Windows -->
                    <li v-if="!isRequirementsMet && serverOS.family === 'Windows'">
                      {{ t('modules.system.installer.troubleshooting.windows_hint') }}
                    </li>

                    <!-- Other/Unknown -->
                    <li v-if="!isRequirementsMet && serverOS.distro === 'unknown' && serverOS.family !== 'Windows'">
                      Install the missing PHP extensions listed above using your OS package manager.
                    </li>
                </ul>
            </div>
          </div>

          <Button 
            class="w-full h-11" 
            :disabled="loading || !isRequirementsMet"
            @click="step = 'config'"
          >
            {{ t('modules.system.installer.requirements.continue') }}
            <ArrowRight class="ml-2 w-4 h-4" />
          </Button>
        </div>

        <div v-if="step === 'config'" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
          <div class="space-y-2">
            <h2 class="text-xl font-semibold text-foreground">{{ t('modules.system.installer.config.title') }}</h2>
            <p class="text-sm text-muted-foreground">{{ t('modules.system.installer.config.description') }}</p>
          </div>

          <div class="space-y-4">
            <div class="space-y-2">
              <Label>{{ t('modules.system.installer.config.app_name') }}</Label>
              <Input v-model="form.app_name" placeholder="My JA-Platform" />
            </div>
            <div class="space-y-2">
              <Label>{{ t('modules.system.installer.config.app_url') }}</Label>
              <Input v-model="form.app_url" placeholder="https://example.com" />
            </div>
            
            <Separator class="my-4" />
            
            <div class="space-y-2">
              <Label>{{ t('modules.system.installer.config.db_connection') }}</Label>
              <Select v-model="form.db_connection">
                <SelectTrigger>
                  <SelectValue placeholder="Select Connection" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="mysql">MySQL / MariaDB</SelectItem>
                  <SelectItem value="pgsql">PostgreSQL</SelectItem>
                  <SelectItem value="sqlite">SQLite</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <template v-if="form.db_connection !== 'sqlite'">
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label>{{ t('modules.system.installer.config.db_host') }}</Label>
                  <Input v-model="form.db_host" placeholder="127.0.0.1" />
                </div>
                <div class="space-y-2">
                  <Label>{{ t('modules.system.installer.config.db_port') }}</Label>
                  <Input v-model="form.db_port" placeholder="3306" />
                </div>
              </div>
              <div class="space-y-2">
                <Label>{{ t('modules.system.installer.config.db_database') }}</Label>
                <Input v-model="form.db_database" placeholder="ja_platform" />
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label>{{ t('modules.system.installer.config.db_username') }}</Label>
                  <Input v-model="form.db_username" placeholder="root" />
                </div>
                <div class="space-y-2">
                  <Label>{{ t('modules.system.installer.config.db_password') }}</Label>
                  <Input v-model="form.db_password" type="password" />
                </div>
              </div>
            </template>
            <template v-else>
               <div class="space-y-2">
                  <Label>{{ t('modules.system.installer.config.db_database') }}</Label>
                  <Input v-model="form.db_database" placeholder="absolute path to .sqlite" />
                </div>
            </template>
          </div>

          <div class="flex gap-3">
            <Button variant="outline" class="flex-1" @click="step = 'requirements'">{{ t('modules.system.installer.config.back') }}</Button>
            <Button class="flex-[2] h-11" :disabled="submitting" @click="handleInstall">
              <Loader2 v-if="submitting" class="mr-2 w-4 h-4 animate-spin" />
              {{ t('modules.system.installer.config.run') }}
            </Button>
          </div>
        </div>

        <div v-if="step === 'success'" class="text-center space-y-6 animate-in zoom-in duration-500">
          <div class="w-20 h-20 bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
            <CheckCircle2 class="w-12 h-12 text-green-500" />
          </div>
          <div class="space-y-2">
            <h2 class="text-2xl font-bold text-foreground">{{ t('modules.system.installer.success.title') }}</h2>
            <p class="text-muted-foreground">{{ t('modules.system.installer.success.description') }}</p>
          </div>
          <Button class="w-full h-11" @click="finish">
            {{ t('modules.system.installer.success.finish') }}
          </Button>
        </div>
      </CardContent>

      <div v-if="step !== 'success'" class="bg-muted/50 p-4 border-t border-border/40 text-center">
        <p class="text-xs text-muted-foreground">
          Powered by JA-Platform &bull; Version 2.0
        </p>
      </div>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { 
  Card, CardContent, Button, Input, Label, Select, 
  SelectContent, SelectItem, SelectTrigger, SelectValue, 
  Separator, DropdownMenu, DropdownMenuContent, 
  DropdownMenuItem, DropdownMenuTrigger 
} from '@/shared/components/ui';
import { Loader2, CheckCircle2, XCircle, ArrowRight, Languages } from 'lucide-vue-next';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useI18n } from 'vue-i18n';
import { getAvailableLocales, getLocale, setLocale } from '@/engine/i18n';

const { t } = useI18n();
const availableLocales = getAvailableLocales();
const currentLocale = computed(() => getLocale());

const step = ref('requirements');
const loading = ref(true);
const submitting = ref(false);
const requirements = ref<any>({});
const serverOS = ref({ family: 'unknown', distro: 'unknown' });
const toast = useToast();

const form = ref({
  app_name: 'JA-Platform',
  app_url: window.location.origin,
  db_connection: 'pgsql',
  db_host: '127.0.0.1',
  db_port: '5432',
  db_database: 'ja_apps',
  db_username: 'postgres',
  db_password: ''
});

const isRequirementsMet = computed(() => {
  const basicMet = requirements.value.php_supported && 
         requirements.value.writable_env && 
         requirements.value.writable_storage && 
         requirements.value.pdo_enabled;
  
  if (!basicMet) return false;

  // Check all extensions
  return Object.keys(requirements.value)
    .filter(key => String(key).startsWith('ext_'))
    .every(key => requirements.value[key] === true);
});

const fetchStatus = async () => {
  try {
    const response = await api.get('/install/status', { _skipManualRedirect: true } as any);
    requirements.value = response.data.requirements;
    serverOS.value = response.data.os || { family: 'unknown', distro: 'unknown' };
    if (response.data.is_installed) {
        toast.info('Already installed', 'Redirecting...');
        setTimeout(() => window.location.href = '/', 1500);
    }
  } catch (err) {
    console.error('Failed to fetch status', err);
  } finally {
    loading.value = false;
  }
};

const handleInstall = async () => {
  submitting.value = true;
  try {
    const response = await api.post('/install', form.value, { _skipManualRedirect: true } as any);
    toast.success.default(response.data.message);
    step.value = 'success';
  } catch (err: any) {
    toast.error.default(err.response?.data?.message || 'Check your configuration');
  } finally {
    submitting.value = false;
  }
};

const finish = () => {
    window.location.href = '/';
};

onMounted(fetchStatus);
</script>
