<template>
  <div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-10 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-foreground">
          {{ $t('common.actions.create') }} {{ $t('modules.core.users.table.user') }}
        </h1>
        <p class="text-muted-foreground">
          {{ $t('modules.core.users.subtitleCreate') }}
        </p>
      </div>
      <router-link :to="{ name: 'users.index' }">
        <Button
          variant="ghost"
          class="gap-2 rounded-xl"
        >
          <ArrowLeft class="w-4 h-4" />
          {{ $t('common.actions.back') }}
        </Button>
      </router-link>
    </div>

    <form
      class="space-y-6"
      @submit.prevent="handleSubmit"
    >
      <!-- Main Content -->
      <div class="bg-card border border-border/50 rounded-xl p-6 space-y-6 shadow-sm">
        <!-- Avatar -->
        <div>
          <label class="block text-sm font-medium text-foreground mb-2">
            {{ $t('modules.core.users.form.avatar') }}
          </label>
          <div class="flex items-center space-x-4">
            <div
              v-if="form.avatar"
              class="flex-shrink-0"
            >
              <img
                :src="form.avatar"
                :alt="form.name"
                class="h-24 w-24 rounded-full object-cover border border-border"
              >
            </div>
            <div
              v-else
              class="h-24 w-24 rounded-full bg-muted flex items-center justify-center border border-border"
            >
              <span class="text-muted-foreground font-medium text-2xl">
                {{ form.name?.charAt(0)?.toUpperCase() || 'U' }}
              </span>
            </div>
            <div>
              <MediaPicker
                :label="$t('modules.core.users.form.selectAvatar')"
                @selected="(media: { url: string }) => form.avatar = media.url"
              />
              <Button
                v-if="form.avatar"
                type="button"
                variant="destructive"
                size="sm"
                class="mt-2"
                @click="form.avatar = null"
              >
                {{ $t('modules.core.users.form.removeAvatar') }}
              </Button>
            </div>
          </div>
        </div>

        <!-- Basic Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-foreground">
              {{ $t('modules.core.users.form.name') }} <span class="text-destructive">*</span>
            </label>
            <Input
              v-model="form.name"
              type="text"
              required
              :class="{ 'border-destructive focus-visible:ring-destructive': errors.name }"
              :placeholder="$t('modules.core.users.form.placeholders.name')"
            />
            <p
              v-if="errors.name"
              class="text-sm text-destructive"
            >
              {{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}
            </p>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-foreground">
              {{ $t('modules.core.users.form.email') }} <span class="text-destructive">*</span>
            </label>
            <Input
              v-model="form.email"
              type="email"
              required
              :class="{ 'border-destructive focus-visible:ring-destructive': errors.email }"
              :placeholder="$t('modules.core.users.form.placeholders.email')"
            />
            <p
              v-if="errors.email"
              class="text-sm text-destructive"
            >
              {{ Array.isArray(errors.email) ? errors.email[0] : errors.email }}
            </p>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-foreground">
              {{ $t('modules.core.users.form.password') }} <span class="text-destructive">*</span>
            </label>
            <div class="relative">
              <Input
                v-model="form.password"
                name="password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                :class="[errors.password ? 'border-destructive focus-visible:ring-destructive' : '', 'pr-10']"
                :placeholder="$t('modules.core.users.form.placeholders.password') + ' (min 8, A-Z, a-z, 0-9)'"
              />
              <button
                type="button"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                @click="showPassword = !showPassword"
              >
                <Eye
                  v-if="!showPassword"
                  class="h-4 w-4"
                />
                <EyeOff
                  v-else
                  class="h-4 w-4"
                />
              </button>
            </div>
            <p
              v-if="errors.password"
              class="text-sm text-destructive"
            >
              {{ Array.isArray(errors.password) ? errors.password[0] : errors.password }}
            </p>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-foreground">
              {{ $t('modules.core.users.form.passwordConfirmation') || 'Confirm Password' }} <span class="text-destructive">*</span>
            </label>
            <div class="relative">
              <Input
                v-model="form.password_confirmation"
                name="password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                :class="[errors.password_confirmation ? 'border-destructive focus-visible:ring-destructive' : '', 'pr-10']"
                :placeholder="$t('modules.core.users.form.placeholders.passwordConfirmation') || 'Repeat password'"
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
            <p
              v-if="errors.password_confirmation"
              class="text-sm text-destructive"
            >
              {{ Array.isArray(errors.password_confirmation) ? errors.password_confirmation[0] : errors.password_confirmation }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-foreground mb-1">
              {{ $t('modules.core.users.form.phone') }}
            </label>
            <Input
              v-model="form.phone"
              type="tel"
              :placeholder="$t('modules.core.users.form.placeholders.phone')"
            />
          </div>
        </div>

        <!-- Additional Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-foreground mb-1">
              {{ $t('modules.core.users.form.bio') }}
            </label>
            <Textarea
              v-model="form.bio"
              :rows="3"
              :placeholder="$t('modules.core.users.form.placeholders.bio')"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-foreground mb-1">
              {{ $t('modules.core.users.form.website') }}
            </label>
            <Input
              v-model="form.website"
              type="url"
              :placeholder="$t('modules.core.users.form.placeholders.website')"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-foreground mb-1">
              {{ $t('modules.core.users.form.location') }}
            </label>
            <Input
              v-model="form.location"
              type="text"
              :placeholder="$t('modules.core.users.form.placeholders.location')"
            />
          </div>
        </div>

        <!-- Roles -->
        <div>
          <label class="block text-sm font-medium text-foreground mb-2">
            {{ $t('modules.core.users.form.roles') }} <span class="text-destructive">*</span>
          </label>
          <div
            v-if="loadingRoles"
            class="flex items-center text-sm text-muted-foreground"
          >
            <Loader2 class="w-4 h-4 mr-2" />
            {{ $t('common.messages.loading.default') }}
          </div>
          <div
            v-else-if="availableRoles.length > 0"
            class="flex flex-wrap gap-4"
          >
            <div
              v-for="role in availableRoles"
              :key="role.id"
              class="flex items-center space-x-2 border border-input px-3 py-2 rounded-md hover:bg-accent/50"
            >
              <Checkbox
                :id="`role-${role.id}`"
                :checked="form.roles.includes(role.id)"
                :disabled="getRoleRank(role.name) > authStore.getRoleRank()"
                @update:checked="(checked: boolean) => {
                  if (checked) form.roles.push(role.id);
                  else form.roles = form.roles.filter(id => id !== role.id);
                }"
              />
              <label
                :for="`role-${role.id}`"
                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer select-none"
                :class="{ 'opacity-50': getRoleRank(role.name) > authStore.getRoleRank() }"
              >
                {{ role.name }}
              </label>
            </div>
          </div>
          <p
            v-else
            class="text-sm text-destructive"
          >
            {{ $t('modules.core.users.modals.user.noRoles') }}
          </p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end space-x-4">
        <Button
          variant="outline"
          as-child
          class="rounded-xl"
        >
          <router-link :to="{ name: 'users.index' }">
            {{ $t('common.actions.cancel') }}
          </router-link>
        </Button>
        <Button
          type="submit"
          :disabled="saving || !isValid"
          class="rounded-xl px-8"
        >
          <Loader2
            v-if="saving"
            class="w-4 h-4 mr-2"
          />
          {{ saving ? $t('common.messages.loading.creating') : $t('common.actions.create') }}
        </Button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { createUserSchema } from '@/shared/schemas';
import {
    Button,
    Input,
    Textarea,
    Checkbox
} from '@/shared/components/ui';
import MediaPicker from '@/shared/components/media/MediaPicker.vue';
import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import EyeOff from 'lucide-vue-next/dist/esm/icons/eye-off.js';
import { useAuthStore, ROLE_RANKS } from '@/modules/System/stores/auth';
import type { Role } from '@/engine/types/auth';

const router = useRouter();
const { t } = useI18n();
const authStore = useAuthStore();
const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(createUserSchema);

const getRoleRank = (roleName: string) => ROLE_RANKS[roleName] || 0;

const saving = ref(false);
const loadingRoles = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const availableRoles = ref<Role[]>([]);

const form = ref<{
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    phone: string;
    bio: string;
    website: string;
    location: string;
    avatar: string | null;
    roles: number[];
}>({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    bio: '',
    website: '',
    location: '',
    avatar: null,
    roles: [],
});

const isValid = computed(() => {
    return !!form.value.name?.trim() && 
           !!form.value.email?.trim() && 
           !!form.value.password?.trim() &&
           !!form.value.password_confirmation?.trim() &&
           form.value.roles.length > 0;
});

const fetchRoles = async () => {
    loadingRoles.value = true;
    try {
        const response = await api.get('/manage/roles');
        const { data } = parseResponse(response);
        availableRoles.value = ensureArray(data);
    } catch (error: unknown) {
        logger.error('Failed to fetch roles:', error);
    } finally {
        loadingRoles.value = false;
    }
};

const handleSubmit = async () => {
    // Client-side validation first
    if (!validateWithZod(form.value)) {
        return;
    }

    if (form.value.roles.length === 0) {
        setErrors({ roles: [t('modules.core.users.messages.roleRequired')] });
        return;
    }

    saving.value = true;
    clearErrors();
    
    try {
        await api.post('/manage/system/users', form.value);
        toast.success.create('User');
        router.push({ name: 'users.index' });
    } catch (error: unknown) {
        const err = error as { response?: { status?: number; data?: { errors?: Record<string, string[]> } } };
        if (err.response?.status === 422) {
            setErrors(err.response.data?.errors || {});
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchRoles();
});
</script>
