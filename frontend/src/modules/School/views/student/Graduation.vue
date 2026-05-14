<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h2 class="text-xl font-bold text-foreground">Informasi Kelulusan</h2>
        <p class="text-sm text-muted-foreground">Cek status kelulusan dan download sertifikat resmi Anda di sini.</p>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
        <LucideIcon name="RefreshCcw" class="w-8 h-8 animate-spin text-primary/40" />
    </div>

    <div v-else-if="!graduationData" class="text-center py-12 bg-muted/20 rounded-2xl border border-dashed border-border">
        <LucideIcon name="GraduationCap" class="w-12 h-12 mx-auto mb-4 text-muted-foreground/40" />
        <h3 class="font-bold text-lg">Belum Ada Data</h3>
        <p class="text-muted-foreground text-sm max-w-md mx-auto">Informasi kelulusan Anda belum tersedia. Silakan hubungi pihak sekolah jika menurut Anda ini adalah kesalahan.</p>
    </div>

    <div v-else class="grid lg:grid-cols-3 gap-6">
        <!-- Status Card -->
        <Card class="lg:col-span-1 border border-border/40 shadow-xl rounded-2xl overflow-hidden">
            <div :class="['h-2', statusColor]"></div>
            <CardContent class="p-6 text-center space-y-4">
                <div :class="['w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-2 shadow-inner', statusBg]">
                    <LucideIcon :name="statusIcon" :class="['w-10 h-10', statusTextColor]" />
                </div>
                
                <div>
                    <h3 class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">Status Kelulusan</h3>
                    <p :class="['text-2xl font-bold tracking-tight', statusTextColor]">
                        {{ statusLabel }}
                    </p>
                </div>

                <div class="pt-4 border-t border-border/40">
                    <p class="text-sm text-muted-foreground">Tahun Lulus</p>
                    <p class="text-lg font-bold">{{ graduationData.result?.graduation_year || '-' }}</p>
                </div>

                <Button 
                    v-if="graduationData.result?.status === 'graduated'"
                    class="w-full rounded-xl py-6 bg-primary hover:bg-primary/90 shadow-lg shadow-primary/20"
                    @click="downloadCertificate"
                >
                    <LucideIcon name="Download" class="w-4 h-4 mr-2" />
                    Download Sertifikat (SKL)
                </Button>
            </CardContent>
        </Card>

        <!-- Grades Card -->
        <Card class="lg:col-span-2 border border-border/40 shadow-sm rounded-2xl">
            <CardHeader>
                <CardTitle class="text-lg flex items-center gap-2">
                    <LucideIcon name="ListChecks" class="w-5 h-5 text-primary" />
                    Rincian Nilai
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div v-if="hasGrades" class="grid md:grid-cols-2 gap-4">
                    <div 
                        v-for="(val, key) in graduationData.result?.grades" 
                        :key="key"
                        class="flex justify-between items-center p-3 rounded-xl bg-accent/30 border border-border/20"
                    >
                        <span class="text-sm font-medium">{{ key }}</span>
                        <span class="text-lg font-bold text-primary">{{ val }}</span>
                    </div>
                </div>
                <div v-else class="text-center py-12 text-muted-foreground text-sm italic">
                    Data rincian nilai tidak tersedia.
                </div>

                <div v-if="graduationData.result?.certificate_number" class="mt-8 p-4 bg-primary/5 rounded-xl border border-primary/10">
                    <div class="flex items-start gap-3">
                        <LucideIcon name="ShieldCheck" class="w-5 h-5 text-primary mt-0.5" />
                        <div>
                            <p class="text-[10px] font-bold text-primary uppercase tracking-wide">Nomor Sertifikat</p>
                            <p class="text-sm font-mono">{{ graduationData.result.certificate_number }}</p>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle, Button, LucideIcon } from '@/shared/components/ui';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { parseResponse } from '@/shared/utils/responseParser';

const toast = useToast();
const loading = ref(true);
const graduationData = ref<any>(null);

const fetchGraduation = async () => {
    loading.value = true;
    try {
        const response = await api.get('student/graduation');
        graduationData.value = parseResponse(response).data;
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        loading.value = false;
    }
};

const statusLabel = computed(() => {
    const status = graduationData.value?.result?.status;
    if (status === 'graduated') return 'Lulus';
    if (status === 'not_graduated') return 'Tidak Lulus';
    if (status === 'deferred') return 'Ditangguhkan';
    return 'Sedang Diproses';
});

const statusColor = computed(() => {
    const status = graduationData.value?.result?.status;
    if (status === 'graduated') return 'bg-success';
    if (status === 'not_graduated') return 'bg-destructive';
    if (status === 'deferred') return 'bg-warning';
    return 'bg-primary';
});

const statusBg = computed(() => {
    const status = graduationData.value?.result?.status;
    if (status === 'graduated') return 'bg-success/20';
    if (status === 'not_graduated') return 'bg-destructive/20';
    if (status === 'deferred') return 'bg-warning/20';
    return 'bg-primary/20';
});

const statusTextColor = computed(() => {
    const status = graduationData.value?.result?.status;
    if (status === 'graduated') return 'text-success';
    if (status === 'not_graduated') return 'text-destructive';
    if (status === 'deferred') return 'text-warning';
    return 'text-primary';
});

const statusIcon = computed(() => {
    const status = graduationData.value?.result?.status;
    if (status === 'graduated') return 'CircleCheck';
    if (status === 'not_graduated') return 'XCircle';
    if (status === 'deferred') return 'Clock';
    return 'Loader2';
});

const hasGrades = computed(() => {
    const grades = graduationData.value?.result?.grades;
    return grades && Object.keys(grades).length > 0;
});

const downloadCertificate = () => {
    window.open('/api/v1/student/graduation/certificate', '_blank');
};

onMounted(fetchGraduation);
</script>
