<template>
  <div class="min-h-screen bg-muted/30 flex flex-col items-center justify-center p-6">
    <div class="max-w-md w-full">
      <div class="text-center mb-8">
        <div class="bg-primary/10 w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <LucideIcon
            name="ShieldCheck"
            class="w-8 h-8 text-primary"
          />
        </div>
        <h1 class="text-2xl font-bold">
          Verifikasi Dokumen
        </h1>
        <p class="text-sm text-muted-foreground">
          Sistem Validasi Keaslian Dokumen Institusi
        </p>
      </div>

      <Card
        v-if="loading"
        class="text-center p-12"
      >
        <LucideIcon
          name="Loader2"
          class="w-8 h-8 animate-spin text-primary mx-auto mb-4"
        />
        <p class="text-sm font-medium">
          Memvalidasi hash dokumen...
        </p>
      </Card>

      <div
        v-else-if="error"
        class="space-y-4"
      >
        <Card class="border-destructive/30 bg-destructive/5">
          <CardContent class="p-8 text-center">
            <div class="bg-destructive/10 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4">
              <LucideIcon
                name="AlertCircle"
                class="w-6 h-6 text-destructive"
              />
            </div>
            <h3 class="text-lg font-bold text-destructive mb-1">
              Dokumen Tidak Valid
            </h3>
            <p class="text-sm text-destructive/80">
              {{ error }}
            </p>
          </CardContent>
        </Card>
        <Button
          variant="outline"
          class="w-full"
          @click="$router.push('/')"
        >
          Kembali ke Beranda
        </Button>
      </div>

      <div
        v-else-if="data"
        class="space-y-6 animate-in slide-in-from-bottom duration-500"
      >
        <Card class="border-success/30 bg-success/5 overflow-hidden">
          <div class="bg-success px-4 py-2 text-white text-center text-xs font-bold uppercase tracking-widest">
            Authentic & Verified
          </div>
          <CardContent class="p-8">
            <div class="flex items-center gap-4 mb-6 pb-6 border-b">
              <div class="bg-success/20 p-3 rounded-xl">
                <LucideIcon
                  :name="getIcon(data.type)"
                  class="w-6 h-6 text-success"
                />
              </div>
              <div>
                <h3 class="font-bold text-lg">
                  {{ getTitle(data.type) }}
                </h3>
                <p class="text-xs text-muted-foreground italic">
                  Verified at: {{ data.verified_at }}
                </p>
              </div>
            </div>

            <div class="space-y-4">
              <div
                v-for="(val, key) in data.details"
                :key="key"
                class="flex justify-between items-start"
              >
                <span class="text-sm text-muted-foreground">{{ key }}</span>
                <span class="text-sm font-bold text-right">{{ val }}</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <p class="text-center text-xs text-muted-foreground px-4">
          Dokumen ini di-generate secara otomatis oleh sistem administrasi institusi dan telah terekam secara permanen di database pusat.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { Card, CardContent, LucideIcon, Button } from '@/components/ui';
import api from '@/services/api';
import { parseResponse } from '@/utils/responseParser';

const route = useRoute();
const loading = ref(true);
const data = ref<any>(null);
const error = ref<string | null>(null);

const getIcon = (type: string) => {
    switch (type) {
        case 'id_card': return 'IdCard';
        case 'skl': return 'FileBadge';
        case 'receipt': return 'Receipt';
        default: return 'FileText';
    }
}

const getTitle = (type: string) => {
    switch (type) {
        case 'id_card': return 'Kartu Identitas Pelajar';
        case 'skl': return 'Surat Keterangan Lulus';
        case 'receipt': return 'Kwitansi Pembayaran';
        default: return 'Dokumen Resmi';
    }
}

const verifyDocument = async () => {
    loading.value = true;
    try {
        const hash = route.params.hash;
        const response = await api.get(`/public/verify/${hash}`);
        data.value = parseResponse(response).data;
    } catch (e: any) {
        error.value = e.response?.data?.message || 'Gagal memverifikasi dokumen.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    verifyDocument();
});
</script>
