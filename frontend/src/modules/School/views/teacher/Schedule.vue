<template>
  <div class="space-y-6 animate-in fade-in duration-700">
    <div class="flex flex-col gap-1">
      <h1 class="text-3xl font-bold tracking-tight">
        Jadwal Mengajar
      </h1>
      <p class="text-muted-foreground">
        Kelola jadwal kelas dan isi jurnal harian Anda.
      </p>
    </div>

    <!-- Day Navigation -->
    <div class="flex flex-wrap gap-2 mb-6">
      <Button 
        v-for="d in days"
        :key="d"
        :variant="selectedDay === d ? 'default' : 'outline'"
        class="rounded-full px-6"
        @click="selectedDay = d"
      >
        {{ d }}
      </Button>
    </div>

    <Card class="border-none bg-white/60 dark:bg-slate-900/60 backdrop-blur-md shadow-2xl shadow-indigo-500/5">
      <CardContent class="p-6">
        <div
          v-if="loading"
          class="space-y-4"
        >
          <SkeletonLoader
            v-for="i in 5"
            :key="i"
            class="h-20 w-full"
          />
        </div>
        <div
          v-else-if="filteredSchedules.length > 0"
          class="space-y-4"
        >
          <div 
            v-for="item in filteredSchedules" 
            :key="item.id" 
            class="flex flex-col md:flex-row md:items-center gap-4 p-5 border rounded-2xl hover:bg-white dark:hover:bg-slate-800 transition-all shadow-sm hover:shadow-md"
          >
            <!-- Time Badge -->
            <div class="bg-primary/10 px-4 py-3 rounded-xl text-primary font-bold min-w-[120px] text-center flex flex-col justify-center">
              <span class="text-lg">{{ item.start_time?.substring(0, 5) }}</span>
              <span class="text-xs opacity-70">s/d {{ item.end_time?.substring(0, 5) }}</span>
            </div>
              
            <!-- Info -->
            <div class="flex-1 space-y-1">
              <h3 class="font-bold text-lg">
                {{ item.subject?.name }}
              </h3>
              <div class="flex flex-wrap gap-2 text-sm text-muted-foreground">
                <span class="flex items-center gap-1"><LucideIcon
                  name="Users"
                  class="w-3 h-3"
                /> Kelas {{ item.study_group?.name }}</span>
                <span class="flex items-center gap-1"><LucideIcon
                  name="MapPin"
                  class="w-3 h-3"
                /> Ruang {{ item.room?.name || 'Belum di set' }}</span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2 w-full md:w-auto mt-4 md:mt-0">
              <Button
                class="w-full md:w-auto"
                @click="openJournalDialog(item)"
              >
                <LucideIcon
                  name="Edit3"
                  class="w-4 h-4 mr-2"
                />
                Isi Jurnal
              </Button>
              <!-- Bisa ditambah tombol untuk presensi per kelas di sini jika scope-nya diperlukan -->
            </div>
          </div>
        </div>
        <div
          v-else
          class="text-center py-20 px-4 text-muted-foreground bg-muted/20 rounded-2xl border border-dashed"
        >
          <LucideIcon
            name="Bed"
            class="w-16 h-16 mx-auto mb-4 opacity-10"
          />
          <p class="text-lg font-medium">
            Bebas Tugas
          </p>
          <p class="text-sm">
            Tidak ada jadwal mengajar pada hari {{ selectedDay }}.
          </p>
        </div>
      </CardContent>
    </Card>

    <!-- Journal Dialog -->
    <Dialog v-model:open="dialogJournal">
      <DialogContent class="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>Isi Jurnal Kelas</DialogTitle>
          <DialogDescription>
            Input catatan atau topik bahasan untuk {{ activeSchedule?.subject?.name }} Kelas {{ activeSchedule?.study_group?.name }}.
          </DialogDescription>
        </DialogHeader>
        <form
          class="space-y-4 py-4"
          @submit.prevent="submitJournal"
        >
          <div class="space-y-2">
            <Label>Tanggal Pertemuan</Label>
            <Input
              v-model="journalForm.date"
              type="date"
              required
            />
          </div>
          <div class="space-y-2">
            <Label>Topik Pembahasan</Label>
            <Input
              v-model="journalForm.topic"
              placeholder="Misal: Bab 2 Aljabar"
              required
            />
          </div>
          <div class="space-y-2">
            <Label>Catatan Guru (Opsional)</Label>
            <Textarea
              v-model="journalForm.notes"
              placeholder="Catatan kelas, evaluasi, atau keterangan siswa..."
              rows="3"
            />
          </div>
          <div class="flex justify-end pt-4">
            <Button
              type="button"
              variant="outline"
              class="mr-2"
              @click="dialogJournal = false"
            >
              Batal
            </Button>
            <Button
              type="submit"
              :loading="submitLoading"
            >
              Simpan Jurnal
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import {
  Card, CardContent, Button, LucideIcon, SkeletonLoader,
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription,
  Input, Label, Textarea
} from '@/shared/components/ui';
import api from '@/engine/api/client';
import { parseResponse } from '@/shared/utils/responseParser';
import { useToast } from '@/shared/composables/useToast';
import { useSchoolStore } from '@/modules/School/stores/school';
import { useUnitStore } from '@/modules/School/stores/unit';

const toast = useToast();
const schoolStore = useSchoolStore();
const unitStore = useUnitStore();

const loading = ref(true);
const submitLoading = ref(false);
const schedules = ref<any[]>([]);

const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
const todayObj = new Date().getDay();
const selectedDay = ref(days[todayObj === 0 ? 6 : todayObj - 1]);

const dialogJournal = ref(false);
const activeSchedule = ref<any>(null);
const journalForm = ref({
   date: new Date().toISOString().split('T')[0],
   topic: '',
   notes: ''
});

const filteredSchedules = computed(() => {
  return schedules.value.filter(s => s.day === selectedDay.value);
});

const fetchSchedules = async () => {
  try {
    const response = await api.get('/school/admin/teacher/schedules');
    schedules.value = parseResponse(response).data || [];
  } catch (e: any) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};

const openJournalDialog = (item: any) => {
   activeSchedule.value = item;
   journalForm.value = {
      date: new Date().toISOString().split('T')[0],
      topic: '',
      notes: ''
   };
   dialogJournal.value = true;
};

const submitJournal = async () => {
   submitLoading.value = true;
   try {
      await api.post('/school/admin/academic/journals', {
         school_id: schoolStore.currentSchool?.id,
         workspace_id: unitStore.activeUnitId,
         schedule_id: activeSchedule.value.id,
         date: journalForm.value.date,
         topic: journalForm.value.topic,
         notes: journalForm.value.notes
      });
      toast.success.action('Jurnal Tersimpan');
      dialogJournal.value = false;
   } catch(e: any) {
      toast.error.fromResponse(e);
   } finally {
      submitLoading.value = false;
   }
};

onMounted(() => {
  fetchSchedules();
});
</script>
