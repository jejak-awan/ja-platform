<template>
  <div class="p-6 max-w-7xl mx-auto">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">Kursus Saya</h1>
      <p class="text-slate-500 dark:text-slate-400">Lanjutkan perjalanan belajarmu dari tempat terakhir.</p>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 2" :key="i" class="h-64 bg-slate-100 dark:bg-slate-800 animate-pulse rounded-2xl"></div>
    </div>

    <div v-else-if="myCourses.length === 0" class="text-center py-20 bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800">
      <h3 class="text-xl font-medium text-slate-900 dark:text-white">Kamu belum terdaftar di kursus apapun</h3>
      <p class="text-slate-500 mt-2">Jelajahi katalog kami untuk mulai belajar!</p>
      <router-link 
        :to="{ name: 'student-lms-catalog' }"
        class="mt-6 inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition-all"
      >
        Buka Katalog
      </router-link>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <div 
        v-for="course in myCourses" 
        :key="course.id"
        class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden"
      >
        <div class="p-6">
          <div class="flex justify-between items-start mb-4">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white leading-tight">
              {{ course.title }}
            </h3>
          </div>
          
          <div class="mb-6">
            <div class="flex justify-between text-sm text-slate-500 mb-2">
              <span>Progres Belajar</span>
              <span class="font-bold text-indigo-600">0%</span>
            </div>
            <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
              <div class="h-full bg-indigo-500 rounded-full" style="width: 0%"></div>
            </div>
          </div>

          <router-link 
            :to="{ name: 'student-lms-learn', params: { id: course.id }}"
            class="w-full flex items-center justify-center px-5 py-3 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl text-sm font-bold hover:opacity-90 transition-all"
          >
            Lanjutkan Belajar
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useLmsStore } from '../../../stores/lms';

const lmsStore = useLmsStore();
const { myCourses, loading } = storeToRefs(lmsStore);

onMounted(() => {
  lmsStore.fetchMyCourses();
});
</script>
