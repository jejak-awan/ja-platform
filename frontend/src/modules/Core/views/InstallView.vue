<template>
  <div class="min-h-screen bg-background flex items-center justify-center p-4">
    <Card class="w-full max-w-lg border-border/40 shadow-2xl bg-card/50 backdrop-blur-xl overflow-hidden">
      <!-- Header -->
      <div class="bg-primary p-8 text-primary-foreground relative overflow-hidden">
        <div class="relative z-10">
          <h1 class="text-3xl font-bold tracking-tight mb-2">JA-Platform</h1>
          <p class="text-primary-foreground/80 font-medium">Installation Wizard</p>
        </div>
        <!-- Decorative Background Circle -->
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl pointer-events-none" />
      </div>

      <CardContent class="p-8">
        <div v-if="step === 'requirements'" class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
          <div class="space-y-2">
            <h2 class="text-xl font-semibold text-foreground">System Check</h2>
            <p class="text-sm text-muted-foreground">Checking if your server meets the requirements.</p>
          </div>

          <div v-if="loading" class="flex justify-center py-8">
            <Loader2 class="w-8 h-8 animate-spin text-primary" />
          </div>

          <div v-else class="space-y-3">
            <div 
              v-for="(status, key) in requirements" 
              :key="key"
              class="flex items-center justify-between p-3 rounded-lg border border-border/50 bg-muted/30"
            >
              <span class="text-sm font-medium capitalize">{{ String(key).replace('_', ' ') }}</span>
              <div class="flex items-center gap-2">
                <span class="text-xs text-muted-foreground">{{ status === true ? 'OK' : status }}</span>
                <CheckCircle2 v-if="status === true || (typeof status === 'string' && key === 'php_version')" class="w-5 h-5 text-green-500" />
                <XCircle v-else class="w-5 h-5 text-destructive" />
              </div>
            </div>

            <!-- Troubleshooting Section -->
            <div v-if="!isRequirementsMet" class="mt-6 p-4 rounded-lg bg-destructive/5 border border-destructive/20 animate-in fade-in zoom-in duration-300">
                <h3 class="text-sm font-bold text-destructive mb-2 flex items-center gap-2">
                    <XCircle class="w-4 h-4" />
                    How to Fix:
                </h3>
                <ul class="text-xs space-y-2 text-muted-foreground list-disc pl-4">
                    <li v-if="!requirements.php_supported">Upgrade PHP to 8.2 or higher.</li>
                    <li v-if="!requirements.writable_env">Run: <code class="bg-muted px-1 rounded text-foreground">chmod 666 .env</code></li>
                    <li v-if="!requirements.writable_storage">Run: <code class="bg-muted px-1 rounded text-foreground">chmod -R 775 storage bootstrap/cache</code></li>
                    <li v-if="!requirements.pdo_enabled">Install PDO: <code class="bg-muted px-1 rounded text-foreground">sudo apt install php-pgsql php-mysql</code></li>
                </ul>
            </div>
          </div>

          <Button 
            class="w-full h-11" 
            :disabled="loading || !isRequirementsMet"
            @click="step = 'config'"
          >
            Continue to Configuration
            <ArrowRight class="ml-2 w-4 h-4" />
          </Button>
        </div>

        <div v-if="step === 'config'" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
          <div class="space-y-2">
            <h2 class="text-xl font-semibold text-foreground">Basic Configuration</h2>
            <p class="text-sm text-muted-foreground">Set up your application identity and database.</p>
          </div>

          <div class="space-y-4">
            <div class="space-y-2">
              <Label>Application Name</Label>
              <Input v-model="form.app_name" placeholder="My JA-Platform" />
            </div>
            <div class="space-y-2">
              <Label>Application URL</Label>
              <Input v-model="form.app_url" placeholder="https://example.com" />
            </div>
            
            <Separator class="my-4" />
            
            <div class="space-y-2">
              <Label>Database Connection</Label>
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
                  <Label>Host</Label>
                  <Input v-model="form.db_host" placeholder="127.0.0.1" />
                </div>
                <div class="space-y-2">
                  <Label>Port</Label>
                  <Input v-model="form.db_port" placeholder="3306" />
                </div>
              </div>
              <div class="space-y-2">
                <Label>Database Name</Label>
                <Input v-model="form.db_database" placeholder="ja_platform" />
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label>Username</Label>
                  <Input v-model="form.db_username" placeholder="root" />
                </div>
                <div class="space-y-2">
                  <Label>Password</Label>
                  <Input v-model="form.db_password" type="password" />
                </div>
              </div>
            </template>
            <template v-else>
               <div class="space-y-2">
                  <Label>Database Path</Label>
                  <Input v-model="form.db_database" placeholder="absolute path to .sqlite" />
                </div>
            </template>
          </div>

          <div class="flex gap-3">
            <Button variant="outline" class="flex-1" @click="step = 'requirements'">Back</Button>
            <Button class="flex-[2] h-11" :disabled="submitting" @click="handleInstall">
              <Loader2 v-if="submitting" class="mr-2 w-4 h-4 animate-spin" />
              Run Installation
            </Button>
          </div>
        </div>

        <div v-if="step === 'success'" class="text-center space-y-6 animate-in zoom-in duration-500">
          <div class="w-20 h-20 bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
            <CheckCircle2 class="w-12 h-12 text-green-500" />
          </div>
          <div class="space-y-2">
            <h2 class="text-2xl font-bold text-foreground">Success!</h2>
            <p class="text-muted-foreground">JA-Platform has been installed successfully.</p>
          </div>
          <Button class="w-full h-11" @click="finish">
            Go to Dashboard
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
import { Card, CardContent, Button, Input, Label, Select, SelectContent, SelectItem, SelectTrigger, SelectValue, Separator } from '@/components/ui';
import { Loader2, CheckCircle2, XCircle, ArrowRight } from 'lucide-vue-next';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';

const step = ref('requirements');
const loading = ref(true);
const submitting = ref(false);
const requirements = ref<any>({});
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
  return requirements.value.php_supported && 
         requirements.value.writable_env && 
         requirements.value.writable_storage && 
         requirements.value.pdo_enabled;
});

const fetchStatus = async () => {
  try {
    const response = await api.get('/v1/install/status', { _skipManualRedirect: true } as any);
    requirements.value = response.data.requirements;
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
    const response = await api.post('/v1/install', form.value, { _skipManualRedirect: true } as any);
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
