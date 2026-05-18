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
        Tambah PTK Baru
      </h1>
      <p class="text-sm text-muted-foreground">
        Pendidik dan Tenaga Kependidikan (Guru & Staff).
      </p>
    </div>

    <StaffForm
      :loading="saving"
      @submit="handleSubmit"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/engine/api/client';
import { LucideIcon } from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import StaffForm from './components/StaffForm.vue';

const router = useRouter();
const toast = useToast();
const saving = ref(false);

const handleSubmit = async (formData: any) => {
  saving.value = true;
  try {
    await api.post('/manage/school/staff', formData);
    toast.success.action('Staff berhasil ditambahkan');
    router.push({ name: 'staff.index' });
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    saving.value = false;
  }
};
</script>
