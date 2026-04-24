<template>
  <div class="student-360-view min-h-screen bg-[#050b14] text-white p-4 lg:p-8 pb-12">
    <div
      v-if="loading"
      class="flex items-center justify-center h-[60vh]"
    >
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-cyan-500" />
    </div>
    
    <div
      v-else-if="student"
      class="max-w-7xl mx-auto space-y-8 animate-fade-in"
    >
      <!-- Header Section -->
      <div class="relative rounded-[32px] overflow-hidden bg-[#0d1b2e]/60 backdrop-blur-2xl border border-slate-800 shadow-2xl">
        <!-- Banner Decoration -->
        <div class="h-32 bg-gradient-to-r from-cyan-900/40 via-blue-900/40 to-indigo-900/40 relative">
          <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-20" />
          <div class="absolute inset-0 bg-gradient-to-t from-[#0d1b2e] to-transparent" />
        </div>

        <div class="px-8 pb-8 -mt-12 relative flex flex-col md:flex-row items-end justify-between gap-6">
          <div class="flex flex-col md:flex-row items-end gap-6 text-left w-full md:w-auto">
            <div class="relative group">
              <div class="absolute -inset-1 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-[24px] blur opacity-25 group-hover:opacity-100 transition duration-1000" />
              <div class="relative w-32 h-32 md:w-40 md:h-40 rounded-[22px] bg-[#050b14] p-1 border border-slate-700/50 overflow-hidden">
                <Avatar class="w-full h-full rounded-[20px] bg-slate-900 border-none">
                  <AvatarImage
                    v-if="student.photo"
                    :src="student.photo"
                  />
                  <AvatarFallback class="text-4xl font-black text-cyan-400 bg-[#0a1524]">
                    {{ student.full_name?.substring(0, 2).toUpperCase() || 'ST' }}
                  </AvatarFallback>
                </Avatar>
              </div>
            </div>

            <div class="space-y-1 mb-2">
              <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-4xl font-extrabold tracking-tight text-white drop-shadow-sm">
                  {{ student.full_name }}
                </h1>
                <span class="px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-bold uppercase tracking-widest">
                  {{ student.status || 'Active' }}
                </span>
              </div>
              <div class="flex flex-wrap gap-4 text-slate-400 font-medium">
                <span class="flex items-center gap-1.5 hover:text-cyan-400 transition-colors cursor-default">
                  <LucideIcon
                    name="Fingerprint"
                    class="w-4 h-4 text-cyan-500"
                  />
                  NISN: {{ student.nisn || '-' }}
                </span>
                <span class="flex items-center gap-1.5 hover:text-cyan-400 transition-colors cursor-default">
                  <LucideIcon
                    name="Layers"
                    class="w-4 h-4 text-cyan-500"
                  />
                  {{ student.level?.name || 'No Grade' }}
                </span>
                <span class="flex items-center gap-1.5 hover:text-cyan-400 transition-colors cursor-default">
                  <LucideIcon
                    name="MapPin"
                    class="w-4 h-4 text-cyan-500"
                  />
                  {{ student.kecamatan || 'Lokasi Belum Diatur' }}
                </span>
              </div>
            </div>
          </div>

          <div class="flex gap-2 w-full md:w-auto">
            <Button
              variant="outline"
              class="flex-1 md:flex-none bg-slate-900/50 border-slate-700 text-slate-300 hover:bg-slate-800 hover:border-cyan-500/50 transition-all duration-300"
              @click="handlePrint"
            >
              <LucideIcon
                name="Printer"
                class="w-4 h-4 mr-2"
              />
              Cetak Profil
            </Button>
            <Button
              class="flex-1 md:flex-none bg-cyan-600 hover:bg-cyan-500 text-white shadow-lg shadow-cyan-900/20"
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
          <div class="bg-[#0a1524]/60 backdrop-blur-xl border border-slate-800 rounded-3xl p-4 sticky top-8">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-[2px] mb-4 px-4 pt-2">
              Menu Siswa
            </h3>
            <div class="space-y-1">
              <button 
                v-for="tab in tabs" 
                :key="tab.id"
                :class="[
                  'w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition-all duration-300 group',
                  activeTab === tab.id 
                    ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' 
                    : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'
                ]"
                @click="activeTab = tab.id"
              >
                <div
                  :class="[
                    'p-2 rounded-xl transition-colors',
                    activeTab === tab.id ? 'bg-cyan-500 text-white shadow-lg shadow-cyan-500/20' : 'bg-slate-800 text-slate-500 group-hover:bg-slate-700 group-hover:text-slate-300'
                  ]"
                >
                  <LucideIcon
                    :name="(tab.icon as any)"
                    class="w-4 h-4"
                  />
                </div>
                <span class="font-semibold text-sm">{{ tab.name }}</span>
                <LucideIcon
                  v-if="activeTab === tab.id"
                  name="ChevronRight"
                  class="w-4 h-4 ml-auto"
                />
              </button>
            </div>
            
            <div class="mt-6 pt-6 border-t border-slate-800 px-4 pb-2">
              <div class="p-4 rounded-2xl bg-gradient-to-br from-[#0d2137] to-[#050b14] border border-slate-700/50 relative overflow-hidden group">
                <div class="z-10 relative">
                  <p class="text-[10px] font-bold text-cyan-500 uppercase mb-1">
                    Status Keuangan
                  </p>
                  <p class="text-xs text-slate-300">
                    Lunas / SPP Okt
                  </p>
                </div>
                <LucideIcon
                  name="CreditCard"
                  class="absolute -right-2 -bottom-2 w-12 h-12 text-white opacity-[0.03] group-hover:scale-110 transition-transform duration-500"
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
                <div class="bg-[#0a1524]/60 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 relative overflow-hidden group">
                  <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                    <LucideIcon
                      name="BadgeInfo"
                      class="w-20 h-20 text-cyan-400"
                    />
                  </div>
                  <div class="flex items-center gap-3 mb-8">
                    <div class="p-2.5 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                      <LucideIcon
                        name="Briefcase"
                        class="w-5 h-5"
                      />
                    </div>
                    <h3 class="text-xl font-bold tracking-tight">
                      Data Pribadi
                    </h3>
                  </div>
                  
                  <div class="space-y-6">
                    <div
                      v-for="(field, i) in personalFields"
                      :key="i"
                      class="border-b border-slate-800/50 pb-4 last:border-0 last:pb-0"
                    >
                      <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">
                        {{ field.label }}
                      </p>
                      <p class="text-sm font-medium text-slate-200">
                        {{ (student as any)[field.key] || '-' }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Data Orang Tua/Wali -->
                <div class="bg-[#0a1524]/60 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 relative overflow-hidden group text-left">
                  <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                    <LucideIcon
                      name="Home"
                      class="w-20 h-20 text-blue-400"
                    />
                  </div>
                  <div class="flex items-center gap-3 mb-8">
                    <div class="p-2.5 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20">
                      <LucideIcon
                        name="Users"
                        class="w-5 h-5"
                      />
                    </div>
                    <h3 class="text-xl font-bold tracking-tight">
                      Keluarga & Wali
                    </h3>
                  </div>
                  
                  <div class="space-y-6">
                    <div
                      v-for="(field, i) in parentFields"
                      :key="i"
                      class="border-b border-slate-800/50 pb-4 last:border-0 last:pb-0"
                    >
                      <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">
                        {{ field.label }}
                      </p>
                      <p class="text-sm font-medium text-slate-200">
                        {{ (student as any)[field.key] || '-' }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Alamat card wide -->
              <div class="bg-[#0a1524]/60 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 relative overflow-hidden group text-left">
                <div class="flex items-center gap-3 mb-6">
                  <div class="p-2.5 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                    <LucideIcon
                      name="Map"
                      class="w-5 h-5"
                    />
                  </div>
                  <h3 class="text-xl font-bold tracking-tight">
                    Alamat Lengkap
                  </h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                  <div class="md:col-span-1">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">
                      Alamat Domisili
                    </p>
                    <p class="text-sm leading-relaxed text-slate-200">
                      {{ student.address || '-' }}
                    </p>
                  </div>
                  <div class="grid grid-cols-2 md:grid-cols-3 gap-6 md:col-span-2">
                    <div>
                      <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">
                        Dusun
                      </p>
                      <p class="text-sm text-slate-200">
                        {{ student.dusun || '-' }}
                      </p>
                    </div>
                    <div>
                      <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">
                        Desa/Kel
                      </p>
                      <p class="text-sm text-slate-200">
                        {{ student.desa_kelurahan || '-' }}
                      </p>
                    </div>
                    <div>
                      <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">
                        Kecamatan
                      </p>
                      <p class="text-sm text-slate-200">
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
              class="bg-[#0a1524]/60 backdrop-blur-xl border border-slate-800 rounded-3xl p-12 text-center flex flex-col items-center justify-center space-y-4"
            >
              <div class="w-20 h-20 rounded-full bg-slate-900 flex items-center justify-center border border-slate-800">
                <LucideIcon
                  :name="(tabs.find(t => t.id === activeTab)?.icon as any)"
                  class="w-10 h-10 text-slate-600"
                />
              </div>
              <div>
                <h4 class="text-xl font-bold text-slate-300">
                  Data Belum Tersedia
                </h4>
                <p class="text-slate-500 text-sm max-w-xs mx-auto">
                  Informasi detail untuk modul ini masih dalam tahap sinkronisasi data.
                </p>
              </div>
              <Button
                variant="outline"
                class="mt-4 border-slate-700 bg-transparent text-slate-400 hover:text-white"
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

.student-360-view {
  font-family: 'Inter', sans-serif;
}

.animate-fade-in {
  animation: fadeIn 0.8s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.98); }
  to { opacity: 1; transform: scale(1); }
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.4s ease-out;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateX(10px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(-10px);
}

/* Custom Scrollbar for side list if needed */
::-webkit-scrollbar {
  width: 4px;
}
::-webkit-scrollbar-thumb {
  background: #1e293b;
  border-radius: 10px;
}
</style>
