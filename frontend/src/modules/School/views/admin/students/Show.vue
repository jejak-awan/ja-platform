<template>
  <div class="space-y-8 p-4 lg:p-8 animate-in fade-in duration-700">
    <div
      v-if="loading"
      class="flex items-center justify-center h-[60vh]"
    >
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary" />
    </div>
    
    <div
      v-else-if="student"
      class="max-w-7xl mx-auto space-y-8 animate-fade-in"
    >
      <!-- Header Section: Clean -->
      <div class="relative rounded-xl overflow-hidden bg-card border border-border/50 shadow-sm">
        <!-- Banner: Simple Gradient -->
        <div class="h-24 bg-gradient-to-r from-primary/20 via-primary/10 to-transparent relative" />

        <div class="px-8 pb-8 -mt-8 relative flex flex-col md:flex-row items-end justify-between gap-6">
          <div class="flex flex-col md:flex-row items-end gap-6 text-left w-full md:w-auto">
            <div class="relative group">
              <div class="relative w-32 h-32 md:w-36 md:h-36 rounded-xl bg-card p-1 border border-border/40 overflow-hidden shadow-sm">
                <Avatar class="w-full h-full rounded-lg bg-muted border-none">
                  <AvatarImage
                    v-if="student.photo"
                    :src="student.photo"
                  />
                  <AvatarFallback class="text-4xl font-black text-primary bg-primary/10">
                    {{ student.full_name?.substring(0, 2).toUpperCase() || 'ST' }}
                  </AvatarFallback>
                </Avatar>
              </div>
            </div>

            <div class="space-y-1 mb-2">
              <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-black tracking-tight text-foreground">
                  {{ student.full_name }}
                </h1>
                <span class="px-2 py-0.5 rounded-lg bg-success/10 text-success border border-success/20 text-[10px] font-black uppercase tracking-widest">
                  {{ student.status || 'Active' }}
                </span>
              </div>
              <div class="flex flex-wrap gap-4 text-muted-foreground text-sm font-medium">
                <span class="flex items-center gap-1.5 cursor-default">
                  <LucideIcon
                    name="Fingerprint"
                    class="w-4 h-4 text-primary"
                  />
                  NISN: {{ student.nisn || '-' }}
                </span>
                <span class="flex items-center gap-1.5 cursor-default">
                  <LucideIcon
                    name="Layers"
                    class="w-4 h-4 text-primary"
                  />
                  {{ student.level?.name || 'No Grade' }}
                </span>
                <span class="flex items-center gap-1.5 cursor-default">
                  <LucideIcon
                    name="MapPin"
                    class="w-4 h-4 text-primary"
                  />
                  {{ student.kecamatan || 'Lokasi Belum Diatur' }}
                </span>
              </div>
            </div>
          </div>

          <div class="flex gap-2 w-full md:w-auto">
            <Button
              variant="outline"
              class="flex-1 md:flex-none rounded-xl border-border/40"
              @click="handlePrint"
            >
              <LucideIcon
                name="Printer"
                class="w-4 h-4 mr-2"
              />
              Cetak Profil
            </Button>
            <Button
              class="flex-1 md:flex-none rounded-xl shadow-sm"
              @click="$router.push({ name: 'students.edit', params: { id: student.id } })"
            >
              <LucideIcon
                name="Pencil"
                class="w-4 h-4 mr-2"
              />
              Edit Profil
            </Button>
          </div>
        </div>
      </div>

      <!-- Content Area -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar Navigation -->
        <div class="lg:col-span-1 space-y-4">
          <div class="bg-card border border-border/50 rounded-xl p-4 sticky top-8 shadow-sm">
            <h3 class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-[0.2em] mb-4 px-4 pt-2">
              Menu Siswa
            </h3>
            <div class="space-y-1">
              <button 
                v-for="tab in tabs" 
                :key="tab.id"
                :class="[
                  'w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 group border',
                  activeTab === tab.id 
                    ? 'bg-primary/10 text-primary border-primary/20' 
                    : 'text-muted-foreground border-transparent hover:bg-muted/50 hover:text-foreground'
                ]"
                @click="activeTab = tab.id"
              >
                <div
                  :class="[
                    'p-2 rounded-lg transition-colors',
                    activeTab === tab.id ? 'bg-primary text-white shadow-sm' : 'bg-muted text-muted-foreground group-hover:bg-muted/80'
                  ]"
                >
                  <LucideIcon
                    :name="(tab.icon as any)"
                    class="w-4 h-4"
                  />
                </div>
                <span class="font-bold text-sm">{{ tab.name }}</span>
              </button>
            </div>
            
            <div class="mt-6 pt-6 border-t border-border/50 px-2 pb-2">
              <div class="p-4 rounded-xl bg-muted/30 border border-border/40 relative overflow-hidden group">
                <div class="z-10 relative">
                  <p class="text-[9px] font-black text-primary uppercase tracking-widest mb-1">
                    Status Keuangan
                  </p>
                  <p class="text-xs font-bold text-foreground">
                    Lunas / SPP Okt
                  </p>
                </div>
                <LucideIcon
                  name="CreditCard"
                  class="absolute -right-2 -bottom-2 w-12 h-12 text-foreground opacity-[0.03]"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content (Tabs) -->
        <div class="lg:col-span-3 min-h-[500px]">
          <transition
            name="fade-slide"
            mode="out-in"
          >
            <!-- Biodata Tab -->
            <div
              v-show="activeTab === 'profile'"
              class="space-y-6 text-left"
            >
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Data Pribadi -->
                <div class="bg-card border border-border/50 rounded-xl p-8 relative overflow-hidden group shadow-sm">
                  <div class="flex items-center gap-3 mb-8">
                    <div class="p-2.5 rounded-xl bg-primary/10 text-primary border border-primary/20">
                      <LucideIcon
                        name="Briefcase"
                        class="w-5 h-5"
                      />
                    </div>
                    <h3 class="text-xl font-black tracking-tight text-foreground">
                      Data Pribadi
                    </h3>
                  </div>
                  
                  <div class="space-y-6">
                    <div
                      v-for="(field, i) in personalFields"
                      :key="i"
                      class="border-b border-border/40 pb-4 last:border-0 last:pb-0"
                    >
                      <p class="text-[9px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1">
                        {{ field.label }}
                      </p>
                      <p class="text-sm font-bold text-foreground">
                        {{ (student as any)[field.key] || '-' }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Data Orang Tua/Wali -->
                <div class="bg-card border border-border/50 rounded-xl p-8 relative overflow-hidden group shadow-sm text-left">
                  <div class="flex items-center gap-3 mb-8">
                    <div class="p-2.5 rounded-xl bg-primary/10 text-primary border border-primary/20">
                      <LucideIcon
                        name="Users"
                        class="w-5 h-5"
                      />
                    </div>
                    <h3 class="text-xl font-black tracking-tight text-foreground">
                      Keluarga & Wali
                    </h3>
                  </div>
                  
                  <div class="space-y-6">
                    <div
                      v-for="(field, i) in parentFields"
                      :key="i"
                      class="border-b border-border/40 pb-4 last:border-0 last:pb-0"
                    >
                      <p class="text-[9px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1">
                        {{ field.label }}
                      </p>
                      <p class="text-sm font-bold text-foreground">
                        {{ (student as any)[field.key] || '-' }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Alamat card wide -->
              <div class="bg-card border border-border/50 rounded-xl p-8 relative overflow-hidden group shadow-sm text-left">
                <div class="flex items-center gap-3 mb-6">
                  <div class="p-2.5 rounded-xl bg-primary/10 text-primary border border-primary/20">
                    <LucideIcon
                      name="Map"
                      class="w-5 h-5"
                    />
                  </div>
                  <h3 class="text-xl font-black tracking-tight text-foreground">
                    Alamat Lengkap
                  </h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                  <div class="md:col-span-1">
                    <p class="text-[9px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1">
                      Alamat Domisili
                    </p>
                    <p class="text-sm font-bold leading-relaxed text-foreground">
                      {{ student.address || '-' }}
                    </p>
                  </div>
                  <div class="grid grid-cols-2 md:grid-cols-3 gap-6 md:col-span-2">
                    <div>
                      <p class="text-[9px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1">
                        Dusun
                      </p>
                      <p class="text-sm font-bold text-foreground">
                        {{ student.dusun || '-' }}
                      </p>
                    </div>
                    <div>
                      <p class="text-[9px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1">
                        Desa/Kel
                      </p>
                      <p class="text-sm font-bold text-foreground">
                        {{ student.desa_kelurahan || '-' }}
                      </p>
                    </div>
                    <div>
                      <p class="text-[9px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1">
                        Kecamatan
                      </p>
                      <p class="text-sm font-bold text-foreground">
                        {{ student.kecamatan || '-' }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Other Tabs Placeholder -->
            <div
              v-show="activeTab !== 'profile'"
              class="bg-card border border-border/50 rounded-xl p-12 text-center flex flex-col items-center justify-center space-y-4 shadow-sm"
            >
              <div class="w-16 h-16 rounded-xl bg-muted flex items-center justify-center border border-border/40">
                <LucideIcon
                  :name="(tabs.find(t => t.id === activeTab)?.icon as any)"
                  class="w-8 h-8 text-muted-foreground/40"
                />
              </div>
              <div>
                <h4 class="text-lg font-black text-foreground uppercase tracking-tight">
                  Data Belum Tersedia
                </h4>
                <p class="text-muted-foreground text-xs font-medium max-w-xs mx-auto">
                  Informasi detail untuk modul ini masih dalam tahap sinkronisasi data.
                </p>
              </div>
              <Button
                variant="outline"
                class="mt-4 rounded-xl"
                @click="activeTab = 'profile'"
              >
                Kembali ke Profil
              </Button>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, toRefs } from 'vue';
import { useRoute } from 'vue-router';
import { useStudentStore } from '@/modules/School/stores/student';
import {
  Avatar, AvatarFallback,
  Button, LucideIcon
} from '@/components/ui';
import api from '@/services/api';

const route = useRoute();
const studentStore = useStudentStore();
const { currentStudent: student, loading } = toRefs(studentStore);
const activeTab = ref('profile');

const tabs = [
  { id: 'profile', name: 'Biodata Lengkap', icon: 'UserCircle' },
  { id: 'academic', name: 'Riwayat Akademik', icon: 'GraduationCap' },
  { id: 'attendance', name: 'Kehadiran', icon: 'CalendarCheck' },
  { id: 'disciplinary', name: 'Catatan Kedisiplinan', icon: 'ShieldAlert' },
  { id: 'payment', name: 'Keuangan', icon: 'CreditCard' },
];

const personalFields = [
  { label: 'Nama Lengkap', key: 'full_name' },
  { label: 'Tempat Lahir', key: 'place_of_birth' },
  { label: 'Tanggal Lahir', key: 'date_of_birth' },
  { label: 'Jenis Kelamin', key: 'gender' },
  { label: 'Agama', key: 'religion' },
  { label: 'Email', key: 'email' },
  { label: 'No. HP', key: 'phone' },
];

const parentFields = [
  { label: 'Nama Ayah', key: 'father_name' },
  { label: 'Pekerjaan Ayah', key: 'father_occupation' },
  { label: 'Nama Ibu', key: 'mother_name' },
  { label: 'Pekerjaan Ibu', key: 'mother_occupation' },
  { label: 'Wali Siswa', key: 'guardian_name' },
];

const fetchData = async () => {
  try {
    await studentStore.fetchStudent(Number(route.params.id));
  } catch (e) {
    console.error(e);
  }
};

const handlePrint = () => {
  if (!student.value) return;
  window.open(`${api.defaults.baseURL}/admin/reports/students/${student.value.id}/pdf`, '_blank');
};

onMounted(fetchData);
</script>

<style scoped lang="postcss">
@reference "../../../../../../css/app.css";

.animate-fade-in {
  animation: fadeIn 0.8s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.99); }
  to { opacity: 1; transform: scale(1); }
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease-out;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(5px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}
</style>
