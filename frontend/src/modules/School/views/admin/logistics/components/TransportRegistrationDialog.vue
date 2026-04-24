<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Pendaftaran Antar Jemput</DialogTitle>
        <DialogDescription>Daftarkan siswa ke layanan transportasi.</DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4">
        <div class="grid gap-2">
          <Label for="student">Cari Siswa</Label>
          <div class="relative">
            <Input
              v-model="search"
              placeholder="Nama atau NIS..."
              @input="fetchStudents"
            />
            <div
              v-if="searching"
              class="absolute right-3 top-2.5"
            >
              <LucideIcon
                name="Loader2"
                class="w-4 h-4 animate-spin text-muted-foreground"
              />
            </div>
          </div>
          <div
            v-if="students.length > 0"
            class="mt-2 max-h-[150px] overflow-y-auto border rounded-md divide-y bg-muted/20"
          >
            <div 
              v-for="s in students" 
              :key="s.id" 
              class="p-2 hover:bg-primary/10 cursor-pointer text-xs flex justify-between items-center"
              :class="{ 'bg-primary/5': form.student_id === s.id }"
              @click="form.student_id = s.id"
            >
              <span>{{ s.full_name }}</span>
              <span class="text-[10px] text-muted-foreground">{{ s.nis }}</span>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-2">
          <div class="grid gap-2">
            <Label>Kendaraan</Label>
            <Select v-model="form.vehicle_id">
              <SelectTrigger><SelectValue placeholder="Pilih Armada" /></SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="v in vehicles"
                  :key="v.id"
                  :value="String(v.id)"
                >
                  {{ v.plate_number }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="grid gap-2">
            <Label>Rute</Label>
            <Select v-model="form.route_id">
              <SelectTrigger><SelectValue placeholder="Pilih Rute" /></SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="r in routes"
                  :key="r.id"
                  :value="String(r.id)"
                >
                  {{ r.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <div class="grid gap-2">
          <Label for="pickup">Titik Jemput</Label>
          <Input
            id="pickup"
            v-model="form.pickup_point"
            placeholder="Alamat atau Halte jemput"
          />
        </div>

        <div class="grid gap-2">
          <Label for="start">Mulai Layanan</Label>
          <Input
            id="start"
            v-model="form.start_date"
            type="date"
          />
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
          :disabled="!form.student_id"
          @click="handleSubmit"
        >
          Konfirmasi Pendaftaran
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Button, Input, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, LucideIcon
} from '@/components/ui';
import { LogisticsService } from '@/modules/School/services/LogisticsService';
import { StudentService } from '@/modules/School/services/StudentService';
import { useToast } from '@/composables/useToast';
import { parseResponse } from '@/utils/responseParser';

defineProps<{ 
   open: boolean;
   vehicles: any[];
   routes: any[];
}>();

const emit = defineEmits(['update:open', 'save']);
const toast = useToast();
const loading = ref(false);
const searching = ref(false);
const search = ref('');
const students = ref<any[]>([]);

const form = ref({
   student_id: null as number | null,
   vehicle_id: '',
   route_id: '',
   pickup_point: '',
   start_date: new Date().toISOString().split('T')[0]
});

const fetchStudents = async () => {
   if (search.value.length < 2) return;
   searching.value = true;
   try {
      const response = await StudentService.searchStudents(search.value);
      students.value = parseResponse(response).data;
   } catch {
      // Ignored
   } finally {
      searching.value = false;
   }
}

const handleSubmit = async () => {
   if (!form.value.student_id || !form.value.vehicle_id || !form.value.route_id) {
      return toast.error.action('Lengkapi semua data pendaftaran');
   }

   loading.value = true;
   try {
      await LogisticsService.registerTransport(form.value);
      toast.success.action('Siswa berhasil terdaftar transport');
      emit('save');
      emit('update:open', false);
      form.value = { student_id: null, vehicle_id: '', route_id: '', pickup_point: '', start_date: new Date().toISOString().split('T')[0] };
   } catch (_e) {
      toast.error.fromResponse(_e);
   } finally {
      loading.value = false;
   }
};
</script>
