<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? 'Edit Catatan' : 'Tambah Catatan' }} {{ type === 'violation' ? 'Pelanggaran' : type === 'achievement' ? 'Prestasi' : 'Konseling' }}</DialogTitle>
      </DialogHeader>
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label>Siswa <span class="text-destructive">*</span></Label>
          <Select
            v-model="form.student_id"
            required
          >
            <SelectTrigger><SelectValue placeholder="Pilih Siswa" /></SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="s in students"
                :key="s.id"
                :value="String(s.id)"
              >
                {{ s.full_name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        
        <template v-if="type === 'violation'">
          <div class="space-y-2">
            <Label for="category">Kategori Pelanggaran <span class="text-destructive">*</span></Label>
            <Input
              id="category"
              v-model="form.category"
              placeholder="Contoh: Kedisiplinan"
              required
            />
          </div>
          <div class="space-y-2">
            <Label for="points">Poin Pelanggaran <span class="text-destructive">*</span></Label>
            <Input
              id="points"
              v-model="form.points"
              type="number"
              required
            />
          </div>
          <div class="space-y-2">
            <Label for="description">Deskripsi Pelanggaran <span class="text-destructive">*</span></Label>
            <Textarea
              id="description"
              v-model="form.description"
              required
            />
          </div>
        </template>

        <template v-else-if="type === 'counseling'">
          <div class="space-y-2">
            <Label>Jenis Konseling <span class="text-destructive">*</span></Label>
            <Select
              v-model="form.type"
              required
            >
              <SelectTrigger><SelectValue placeholder="Pilih Jenis" /></SelectTrigger>
              <SelectContent>
                <SelectItem value="Individual">
                  Individu
                </SelectItem>
                <SelectItem value="Group">
                  Kelompok
                </SelectItem>
                <SelectItem value="Social">
                  Sosial
                </SelectItem>
                <SelectItem value="Career">
                  Karir
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label for="problem">Masalah / Keluhan <span class="text-destructive">*</span></Label>
            <Textarea
              id="problem"
              v-model="form.problem"
              required
            />
          </div>
          <div class="space-y-2">
            <Label for="solution">Solusi / Tindak Lanjut</Label>
            <Textarea
              id="solution"
              v-model="form.solution"
            />
          </div>
          <div class="space-y-2">
            <Label>Status Kasus <span class="text-destructive">*</span></Label>
            <Select
              v-model="form.status"
              required
            >
              <SelectTrigger><SelectValue placeholder="Pilih Status" /></SelectTrigger>
              <SelectContent>
                <SelectItem value="Resolved">
                  Selesai
                </SelectItem>
                <SelectItem value="Monitoring">
                  Pemantauan
                </SelectItem>
                <SelectItem value="Pending">
                  Tertunda
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </template>

        <template v-else>
          <div class="space-y-2">
            <Label for="title">Nama Prestasi <span class="text-destructive">*</span></Label>
            <Input
              id="title"
              v-model="form.title"
              placeholder="Contoh: Juara 1 OSN Matematika"
              required
            />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="level">Tingkat</Label>
              <Input
                id="level"
                v-model="form.level"
                placeholder="Contoh: Nasional"
              />
            </div>
            <div class="space-y-2">
              <Label for="type">Jenis</Label>
              <Input
                id="type"
                v-model="form.type"
                placeholder="Contoh: Akademik"
              />
            </div>
          </div>
        </template>

        <div class="space-y-2">
          <Label for="date">Tanggal Kejadian / Konseling <span class="text-destructive">*</span></Label>
          <Input
            id="date"
            v-model="form.date"
            type="date"
            required
          />
        </div>

        <DialogFooter>
          <Button
            type="submit"
            :disabled="loading"
          >
            <LucideIcon
              v-if="loading"
              name="Loader2"
              class="w-4 h-4 mr-2 animate-spin"
            />
            Simpan
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
  Button, Label, Input, Textarea, LucideIcon, Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/shared/components/ui';
import api from '@/engine/api/client';
import { parseResponse } from '@/shared/utils/responseParser';
import type { Student } from '@/modules/School/types';

interface AffairForm {
    id?: number;
    student_id: string;
    type?: string;
    category?: string;
    points?: number | string;
    description?: string;
    problem?: string;
    solution?: string;
    status?: string;
    title?: string;
    level?: string;
    date: string;
    staff_id?: number;
}

const props = defineProps<{
  open: boolean;
  type: 'violation' | 'achievement' | 'counseling';
  isEdit?: boolean;
  initialData?: Partial<AffairForm>;
  loading?: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

const students = ref<Student[]>([]);
const form = ref<AffairForm>({ student_id: '', date: new Date().toISOString().split('T')[0] || '' });

const resetForm = () => {
    const today = new Date().toISOString().split('T')[0] || '';
    if (props.type === 'violation') {
        form.value = { student_id: '', category: '', points: 0, date: today, description: '' };
    } else if (props.type === 'counseling') {
        form.value = { student_id: '', type: 'Individual', problem: '', solution: '', status: 'Resolved', date: today, staff_id: 1 };
    } else {
        form.value = { student_id: '', title: '', level: '', type: '', date: today };
    }
}

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        if (props.initialData) {
            const data = { ...props.initialData };
            if (data.student_id) data.student_id = String(data.student_id);
            form.value = { ...form.value, ...data } as AffairForm;
        }
        else resetForm();
    }
});

const fetchMetadata = async () => {
    try {
        const response = await api.get('/admin/students?per_page=100');
        const { data } = parseResponse<Student>(response);
        students.value = data;
    } catch (e) {
        console.error(e);
    }
}

const handleSubmit = () => {
  emit('submit', form.value);
};

onMounted(fetchMetadata);
</script>
