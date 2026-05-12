<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[700px]">
      <DialogHeader>
        <DialogTitle>Check-In Pengunjung Baru</DialogTitle>
        <DialogDescription>Masukkan detail pengunjung untuk verifikasi dan keamanan.</DialogDescription>
      </DialogHeader>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-4">
        <!-- Form Fields -->
        <div class="space-y-4">
          <Field label="Nama Lengkap">
            <Input
              v-model="form.name"
              placeholder="Nama pengunjung..."
            />
          </Field>
          <Field label="Telepon/WhatsApp">
            <Input
              v-model="form.phone"
              placeholder="08..."
            />
          </Field>
          <Field label="Asal Institusi">
            <Input
              v-model="form.institution"
              placeholder="Opsional"
            />
          </Field>
          <Field label="Keperluan">
            <Input
              v-model="form.purpose"
              placeholder="Tujuan berkunjung..."
            />
          </Field>
          <Field label="Bertemu Dengan">
            <Input
              v-model="form.target_person"
              placeholder="Nama staf/guru..."
            />
          </Field>
          <Field label="Catatan">
            <Textarea
              v-model="form.notes"
              placeholder="Catatan tambahan..."
            />
          </Field>
        </div>

        <!-- Camera Section -->
        <div class="space-y-4">
          <div class="border rounded-lg bg-black aspect-video relative overflow-hidden group">
            <video
              v-show="cameraActive"
              ref="videoRef"
              autoplay
              playsinline
              class="w-full h-full object-cover"
            />
            <div
              v-if="!cameraActive && !form.photo"
              class="absolute inset-0 flex flex-col items-center justify-center text-muted-foreground"
            >
              <LucideIcon
                name="Camera"
                class="w-12 h-12 mb-2 opacity-20"
              />
              <p class="text-xs">
                Kamera tidak aktif
              </p>
            </div>
            <img
              v-if="form.photo && !cameraActive"
              :src="photoPreview"
              class="w-full h-full object-cover"
            >
            
            <div
              v-if="cameraActive"
              class="absolute bottom-4 left-0 right-0 flex justify-center"
            >
              <Button
                size="sm"
                type="button"
                class="rounded-full w-12 h-12 p-0 border-4 border-white"
                @click="capturePhoto"
              >
                <div class="w-8 h-8 rounded-full bg-destructive" />
              </Button>
            </div>
          </div>

          <div class="flex gap-2">
            <Button
              v-if="!cameraActive"
              variant="outline"
              class="flex-1"
              @click="startCamera"
            >
              <LucideIcon
                name="Camera"
                class="w-4 h-4 mr-2"
              />
              Aktifkan Kamera
            </Button>
            <Button
              v-else
              variant="destructive"
              class="flex-1"
              @click="stopCamera"
            >
              Matikan Kamera
            </Button>
            <Button
              v-if="form.photo && !cameraActive"
              variant="secondary"
              @click="form.photo = null"
            >
              Ulang Foto
            </Button>
          </div>

          <div class="space-y-2">
            <Label class="text-xs">Foto KTP/SIM (Opsional)</Label>
            <Input
              type="file"
              accept="image/*"
              @change="(e: Event) => {
                  const target = e.target as HTMLInputElement;
                  form.id_card_photo = target.files?.[0] || null;
              }"
            />
          </div>
        </div>
      </div>

      <DialogFooter>
        <Button
          variant="outline"
          @click="$emit('update:open', false)"
        >
          Batal
        </Button>
        <Button
          :loading="loading"
          :disabled="!form.name || !form.purpose"
          @click="submit"
        >
          Check-In Sekarang
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Button, Input, Textarea, Field, LucideIcon, Label
} from '@/shared/components/ui';

defineProps<{
  open: boolean;
  loading: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

const form = reactive({
  name: '',
  phone: '',
  institution: '',
  purpose: '',
  target_person: '',
  notes: '',
  photo: null as Blob | null,
  id_card_photo: null as File | null
});

const videoRef = ref<HTMLVideoElement | null>(null);
const cameraActive = ref(false);
const stream = ref<MediaStream | null>(null);

const photoPreview = computed(() => {
  return form.photo ? URL.createObjectURL(form.photo) : '';
});

const startCamera = async () => {
  try {
    stream.value = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
    if (videoRef.value) {
      videoRef.value.srcObject = stream.value;
      cameraActive.value = true;
    }
  } catch (err) {
    console.error("Error accessing camera:", err);
    alert("Gagal mengakses kamera. Pastikan izin diberikan.");
  }
};

const stopCamera = () => {
  if (stream.value) {
    stream.value.getTracks().forEach(track => track.stop());
    stream.value = null;
  }
  cameraActive.value = false;
};

const capturePhoto = () => {
  if (!videoRef.value) return;
  const canvas = document.createElement('canvas');
  canvas.width = videoRef.value.videoWidth;
  canvas.height = videoRef.value.videoHeight;
  const ctx = canvas.getContext('2d');
  if (ctx) {
    ctx.drawImage(videoRef.value, 0, 0);
    canvas.toBlob((blob) => {
      form.photo = blob;
      stopCamera();
    }, 'image/jpeg', 0.8);
  }
};

const submit = () => {
    const formData = new FormData();
    formData.append('name', form.name);
    formData.append('phone', form.phone);
    formData.append('institution', form.institution);
    formData.append('purpose', form.purpose);
    formData.append('target_person', form.target_person);
    formData.append('notes', form.notes);
    if (form.photo) {
        formData.append('photo', form.photo, 'visitor.jpg');
    }
    if (form.id_card_photo) {
        formData.append('id_card_photo', form.id_card_photo);
    }
    emit('submit', formData);
};
</script>
