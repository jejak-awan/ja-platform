<template>
  <div class="p-6">
    <div class="mb-8 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Manajemen Kursus</h1>
        <p class="text-slate-500 text-sm">Kelola kurikulum dan materi pembelajaran sekolah.</p>
      </div>
      <button 
        @click="showCreateModal = true"
        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/20 transition-all flex items-center"
      >
        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
        Tambah Kursus Baru
      </button>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 dark:bg-slate-800/50">
            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">Judul Kursus</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">Level</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">Status</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">Modul</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr v-if="loading" v-for="i in 3" :key="i">
            <td colspan="5" class="px-6 py-4 animate-pulse bg-slate-50/50 dark:bg-slate-800/20 h-16"></td>
          </tr>
          <tr v-else v-for="course in courses" :key="course.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
            <td class="px-6 py-4">
              <div class="font-bold text-slate-900 dark:text-white">{{ course.title }}</div>
              <div class="text-xs text-slate-500 mt-1">{{ course.slug }}</div>
            </td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                {{ course.level }}
              </span>
            </td>
            <td class="px-6 py-4">
              <span 
                class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider"
                :class="course.status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700'"
              >
                {{ course.status }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-slate-500 font-medium">
              {{ course.lessons_count || 0 }} Modul
            </td>
            <td class="px-6 py-4">
              <router-link 
                :to="{ name: 'admin-lms-course-detail', params: { id: course.id }}"
                class="text-indigo-600 hover:text-indigo-700 text-sm font-bold flex items-center"
              >
                Kelola Kurikulum
                <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Simple Create Modal placeholder -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-3xl p-8 shadow-2xl border border-slate-200 dark:border-slate-800">
        <h2 class="text-2xl font-bold mb-6 text-slate-900 dark:text-white">Buat Kursus Baru</h2>
        <form @submit.prevent="createCourse" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Judul Kursus</label>
            <input v-model="form.title" type="text" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all text-slate-900 dark:text-white" required placeholder="Contoh: Web Dev Mastery">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Level</label>
              <select v-model="form.level" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all text-slate-900 dark:text-white">
                <option value="beginner">Beginner</option>
                <option value="intermediate">Intermediate</option>
                <option value="advanced">Advanced</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Status</label>
              <select v-model="form.status" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all text-slate-900 dark:text-white">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
              </select>
            </div>
          </div>
          <div class="flex gap-4 mt-8">
            <button type="button" @click="showCreateModal = false" class="flex-1 px-4 py-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-xl font-bold hover:opacity-80 transition-all">Batal</button>
            <button type="submit" :disabled="submitting" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/20 flex items-center justify-center">
              <span v-if="submitting" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></span>
              Simpan Kursus
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useLmsStore } from '../../../stores/lms';
import LmsService from '../../../services/LmsService';

const lmsStore = useLmsStore();
const { courses, loading } = storeToRefs(lmsStore);

const showCreateModal = ref(false);
const submitting = ref(false);
const form = ref({
  title: '',
  level: 'beginner',
  status: 'draft',
});

const createCourse = async () => {
  submitting.value = true;
  try {
    await LmsService.createCourse(form.value);
    await lmsStore.fetchAdminCourses();
    showCreateModal.value = false;
    form.value = { title: '', level: 'beginner', status: 'draft' };
  } catch (err) {
    console.error(err);
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  lmsStore.fetchAdminCourses();
});
</script>
