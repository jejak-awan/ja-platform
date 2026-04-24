<template>
  <div class="p-6 max-w-4xl mx-auto">
    <div class="mb-8">
      <router-link
        :to="{ name: 'staff.index' }"
        class="text-sm text-primary hover:underline flex items-center gap-1 mb-2"
      >
        <LucideIcon
          name="ArrowLeft"
          class="w-3 h-3"
        /> Kembali ke Daftar PTK
      </router-link>
      <h1 class="text-2xl font-bold text-foreground">
        Edit Data PTK
      </h1>
      <p class="text-sm text-muted-foreground">
        Perbarui informasi profil pendidik atau tenaga kependidikan.
      </p>
    </div>

    <div
      v-if="fetching"
      class="flex justify-center p-12"
    >
      <LucideIcon
        name="Loader2"
        class="w-8 h-8 animate-spin text-primary"
      />
    </div>
    <StaffForm
      v-else
      :initial-data="staff"
      :is-edit="true"
      :loading="saving"
      @submit="handleSubmit"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { HRService } from '@/modules/School/services/HRService';
import { LucideIcon } from '@/components/ui';
import { useToast } from '@/composables/useToast';
import StaffForm from './components/StaffForm.vue';
import { parseResponse } from '@/utils/responseParser';

const router = useRouter();
const route = useRoute();
const toast = useToast();
const staff = ref(null);
const fetching = ref(true);
const saving = ref(false);

const fetchStaff = async () => {
    fetching.value = true;
    try {
        const response = await HRService.getStaffDetail(Number(route.params.id));
        staff.value = (parseResponse(response) as any).data;
    } catch (e) {
        toast.error.fromResponse(e);
        router.push({ name: 'staff.index' });
    } finally {
        fetching.value = false;
    }
}

const handleSubmit = async (formData: any) => {
  saving.value = true;
  try {
    await HRService.updateStaff(Number(route.params.id), formData);
    toast.success.action('Data staff berhasil diperbarui');
    router.push({ name: 'staff.index' });
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
    fetchStaff();
})
</script>
