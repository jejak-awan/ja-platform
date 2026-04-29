<template>
  <div class="p-6 space-y-6">
    <div>
      <h2 class="text-3xl font-bold tracking-tight">
        {{ $t('features.profile.title') }}
      </h2>
      <p class="text-muted-foreground">
        {{ $t('features.profile.subtitle') }}
      </p>
    </div>

    <div class="w-full">
      <Tabs
        v-model="activeTab"
        class="w-full"
      >
        <div class="mb-10 flex items-center justify-between">
          <TabsList class="bg-transparent p-0 h-auto gap-0">
            <TabsTrigger
              value="profile"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <User class="w-4 h-4 mr-2" />
              {{ $t('features.profile.tabs.profile') }}
            </TabsTrigger>
            <TabsTrigger
              value="password"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <KeyRound class="w-4 h-4 mr-2" />
              {{ $t('features.profile.tabs.password') }}
            </TabsTrigger>
            <TabsTrigger
              value="two-factor"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <ShieldCheck class="w-4 h-4 mr-2" />
              {{ $t('features.profile.tabs.two-factor') }}
            </TabsTrigger>
            <TabsTrigger
              value="history"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <History class="w-4 h-4 mr-2" />
              {{ $t('features.profile.tabs.history') }}
            </TabsTrigger>
          </TabsList>
        </div>

        <TabsContent
          value="profile"
          class="space-y-4"
        >
          <Card>
            <CardHeader>
              <CardTitle>{{ $t('features.profile.tabs.profile') }}</CardTitle>
              <CardDescription>
                {{ $t('features.profile.subtitle') }}
              </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
              <!-- Avatar Upload -->
              <div class="flex items-center gap-x-6">
                <div class="relative">
                  <Avatar class="h-24 w-24">
                    <AvatarImage
                      :src="profileForm.avatar || ''"
                      alt="Avatar"
                    />
                    <AvatarFallback class="text-lg">
                      {{ getInitials(profileForm.name) }}
                    </AvatarFallback>
                  </Avatar>
                  <button
                    v-if="profileForm.avatar"
                    type="button"
                    class="absolute -top-1 -right-1 rounded-full bg-destructive p-1 text-destructive-foreground hover:bg-destructive/90 shadow-sm"
                    title="Remove Avatar"
                    @click="profileForm.avatar = null"
                  >
                    <X class="h-3 w-3" />
                  </button>
                </div>
                <div>
                  <MediaPicker
                    :label="$t('features.users.form.selectAvatar')"
                    @selected="(media: { url: string }) => profileForm.avatar = media.url"
                  />
                  <p class="mt-2 text-xs text-muted-foreground">
                    JPG, GIF or PNG. 1MB max.
                  </p>
                </div>
              </div>

              <Separator class="my-4" />

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <Label for="name">{{ $t('features.profile.form.name') }}</Label>
                  <Input
                    id="name"
                    v-model="profileForm.name"
                    type="text"
                  />
                </div>

                <div class="space-y-2">
                  <Label for="email">{{ $t('features.profile.form.email') }}</Label>
                  <Input
                    id="email"
                    v-model="profileForm.email"
                    type="email"
                  />
                </div>

                <div class="space-y-2">
                  <Label for="phone">{{ $t('features.profile.form.phone') }}</Label>
                  <Input
                    id="phone"
                    v-model="profileForm.phone"
                    type="text"
                  />
                </div>

                <div class="space-y-2">
                  <Label for="location">{{ $t('features.profile.form.location') }}</Label>
                  <Input
                    id="location"
                    v-model="profileForm.location"
                    type="text"
                  />
                </div>
              </div>

              <div class="space-y-2">
                <Label for="bio">{{ $t('features.profile.form.bio') }}</Label>
                <Textarea
                  id="bio"
                  v-model="profileForm.bio"
                  rows="4"
                />
              </div>

              <div class="flex justify-end">
                <Button
                  :disabled="saving || !isProfileDirty"
                  @click="updateProfile"
                >
                  <Loader2
                    v-if="saving"
                    class="mr-2 h-4 w-4"
                  />
                  {{ saving ? $t('features.profile.form.saving') : $t('features.profile.form.save') }}
                </Button>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent
          value="password"
          class="space-y-4"
        >
          <Card>
            <CardHeader>
              <CardTitle>{{ $t('features.profile.tabs.password') }}</CardTitle>
              <CardDescription>
                {{ $t('features.profile.form.passwordHelp') }}
              </CardDescription>
            </CardHeader>
            <form
              class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6"
              @submit.prevent="updatePassword"
            >
              <div class="space-y-2">
                <Label for="current_password">{{ $t('features.profile.form.currentPassword') }}</Label>
                <div class="relative">
                  <Input 
                    id="current_password" 
                    v-model="passwordForm.current_password" 
                    :type="showCurrentPassword ? 'text' : 'password'" 
                    name="current_password"
                    autocomplete="current-password"
                    class="pr-10"
                  />
                  <button
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                    @click="showCurrentPassword = !showCurrentPassword"
                  >
                    <Eye
                      v-if="!showCurrentPassword"
                      class="h-4 w-4"
                    />
                    <EyeOff
                      v-else
                      class="h-4 w-4"
                    />
                  </button>
                </div>
              </div>
              <div />
              <div class="space-y-2">
                <Label for="new_password">{{ $t('features.profile.form.newPassword') }}</Label>
                <div class="relative">
                  <Input 
                    id="new_password" 
                    v-model="passwordForm.password" 
                    :type="showNewPassword ? 'text' : 'password'" 
                    name="password"
                    autocomplete="new-password"
                    class="pr-10"
                  />
                  <button
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                    @click="showNewPassword = !showNewPassword"
                  >
                    <Eye
                      v-if="!showNewPassword"
                      class="h-4 w-4"
                    />
                    <EyeOff
                      v-else
                      class="h-4 w-4"
                    />
                  </button>
                </div>
              </div>
              <div class="space-y-2">
                <Label for="confirm_password">{{ $t('features.profile.form.confirmPassword') }}</Label>
                <div class="relative">
                  <Input 
                    id="confirm_password" 
                    v-model="passwordForm.password_confirmation" 
                    :type="showConfirmPassword ? 'text' : 'password'" 
                    name="password_confirmation"
                    autocomplete="new-password"
                    class="pr-10"
                  />
                  <button
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                    @click="showConfirmPassword = !showConfirmPassword"
                  >
                    <Eye
                      v-if="!showConfirmPassword"
                      class="h-4 w-4"
                    />
                    <EyeOff
                      v-else
                      class="h-4 w-4"
                    />
                  </button>
                </div>
              </div>
              <div class="flex justify-start">
                <Button
                  type="submit"
                  :disabled="changingPassword || !isPasswordValid"
                >
                  <Loader2
                    v-if="changingPassword"
                    class="mr-2 h-4 w-4"
                  />
                  {{ $t('common.actions.update') }} {{ $t('common.labels.password') }}
                </Button>
              </div>
            </form>
          </Card>
        </TabsContent>

        <TabsContent
          value="two-factor"
          class="space-y-4"
        >
          <Card>
            <CardHeader>
              <CardTitle>{{ $t('features.profile.tabs.twoFactor') }}</CardTitle>
              <CardDescription>
                {{ $t('features.profile.form.twoFactorDescription') || 'Secure your account with multi-factor authentication.' }}
              </CardDescription>
            </CardHeader>
            <CardContent class="pb-10">
              <TwoFactorSettings />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent
          value="history"
          class="space-y-4"
        >
          <Card>
            <CardHeader>
              <CardTitle>{{ $t('features.profile.tabs.history') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <LoginHistory />
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, onMounted, computed, defineAsyncComponent, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import toast from '@/services/toast';
import { useAuthStore } from '@/modules/Core/stores/auth';
import { isAxiosError } from 'axios';

// Standardized Async Components
const LoginHistory = defineAsyncComponent(() => import('@/modules/Core/components/admin/LoginHistory.vue'));
const TwoFactorSettings = defineAsyncComponent(() => import('@/modules/Core/components/admin/TwoFactorSettings.vue'));
const MediaPicker = defineAsyncComponent(() => import('@/modules/Cms/components/media/MediaPicker.vue'));

// Shadcn Components
// Shadcn Components
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
    Input,
    Button,
    Label,
    Textarea,
    Avatar,
    AvatarImage,
    AvatarFallback,
    Separator
} from '@/components/ui';

import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import User from 'lucide-vue-next/dist/esm/icons/user.js';
import KeyRound from 'lucide-vue-next/dist/esm/icons/key-round.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';
import History from 'lucide-vue-next/dist/esm/icons/history.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import EyeOff from 'lucide-vue-next/dist/esm/icons/eye-off.js';


interface ProfileForm {
    name: string;
    email: string;
    phone: string;
    bio: string;
    location: string;
    website: string;
    avatar: string | null;
}

const { t } = useI18n();
const authStore = useAuthStore();

const activeTab = ref('profile');
const saving = ref(false);
const changingPassword = ref(false);
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const profileForm: Ref<ProfileForm> = ref({
    name: '',
    email: '',
    phone: '',
    bio: '',
    location: '',
    website: '',
    avatar: null,
});

const initialProfileForm = ref<ProfileForm | null>(null);

const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const getInitials = (name: string | null | undefined): string => {
    if (!name) return 'U';
    return name
        .split(' ')
        .map((word) => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const isProfileDirty = computed(() => {
    if (!initialProfileForm.value) return false;
    return JSON.stringify(profileForm.value) !== JSON.stringify(initialProfileForm.value);
});

const isPasswordValid = computed(() => {
    return passwordForm.value.current_password && 
           passwordForm.value.password && 
           passwordForm.value.password.length >= 8 &&
           passwordForm.value.password === passwordForm.value.password_confirmation;
});

const fetchProfile = async () => {
    try {
        const response = await api.get('/profile');
        const data = response.data;
        if (data) {
            profileForm.value = {
                name: data.name || '',
                email: data.email || '',
                phone: data.phone || '',
                bio: data.bio || '',
                location: data.location || '',
                website: data.website || '',
                avatar: data.avatar || null,
            };
            initialProfileForm.value = JSON.parse(JSON.stringify(profileForm.value));
        }
    } catch (error: unknown) {
        logger.error('Error fetching profile:', error);
        toast.error(t('common.messages.error.default'));
    }
};

const updateProfile = async () => {
    saving.value = true;
    try {
        await api.put('/profile', profileForm.value);
        toast.success(t('features.profile.messages.updateSuccess'));
        await authStore.fetchUser();
        await fetchProfile(); // Re-fetch to update initial state
    } catch (error: unknown) {
        let msg = t('features.profile.messages.updateFailed');
        if (isAxiosError(error)) {
            msg = error.response?.data?.message || msg;
        }
        toast.error(msg);
    } finally {
        saving.value = false;
    }
};

const updatePassword = async () => {
    changingPassword.value = true;
    try {
        await api.put('/profile/password', passwordForm.value);
        toast.success(t('features.profile.messages.passwordSuccess'));
        passwordForm.value = {
            current_password: '',
            password: '',
            password_confirmation: '',
        };
    } catch (error: unknown) {
        let msg = t('features.profile.messages.passwordFailed');
        if (isAxiosError(error)) {
            msg = error.response?.data?.message || msg;
        }
        toast.error(msg);
    } finally {
        changingPassword.value = false;
    }
};

onMounted(() => {
    fetchProfile();
});
</script>
