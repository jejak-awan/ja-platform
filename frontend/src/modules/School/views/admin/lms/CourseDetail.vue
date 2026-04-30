<template>
  <div class="p-6 max-w-5xl mx-auto">
    <div class="mb-8 flex items-center justify-between">
      <div class="flex items-center gap-4">
        <router-link :to="{ name: 'admin-lms-courses' }" class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl hover:bg-slate-50 transition-all">
          <svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        </router-link>
        <div v-if="course">
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ course.title }}</h1>
          <p class="text-slate-500 text-sm">Editor Kurikulum & Materi</p>
        </div>
      </div>
      <div class="flex gap-3">
        <button @click="showAddLessonModal = true" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold shadow-lg shadow-indigo-500/20 flex items-center">
          <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
          Tambah Modul
        </button>
      </div>
    </div>

    <div v-if="loading" class="space-y-4">
      <div v-for="i in 3" :key="i" class="h-20 bg-slate-100 dark:bg-slate-800 animate-pulse rounded-2xl"></div>
    </div>

    <div v-else-if="course" class="space-y-6">
      <div v-for="(lesson, lIdx) in course.lessons" :key="lesson.id" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex justify-between items-center">
          <div class="flex items-center gap-4">
            <div class="w-8 h-8 bg-indigo-600 text-white rounded-lg flex items-center justify-center font-bold text-sm">
              {{ Number(lIdx) + 1 }}
            </div>
            <h3 class="font-bold text-slate-900 dark:text-white">{{ lesson.title }}</h3>
          </div>
          <button @click="openAddTopicModal(lesson.id)" class="text-indigo-600 hover:text-indigo-700 text-xs font-bold uppercase tracking-widest flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
            Tambah Materi
          </button>
        </div>
        
        <div class="p-4 space-y-2">
          <div v-if="lesson.topics.length === 0" class="py-10 text-center text-slate-400 text-sm italic">
            Belum ada materi di modul ini.
          </div>
          <div 
            v-for="topic in lesson.topics" 
            :key="topic.id"
            class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-transparent hover:border-indigo-500/30 transition-all group"
          >
            <div class="flex items-center gap-4">
              <div class="w-8 h-8 bg-white dark:bg-slate-800 rounded-lg flex items-center justify-center border border-slate-200 dark:border-slate-700">
                <svg v-if="topic.topicable_type.includes('RichText')" class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
                <svg v-else-if="topic.topicable_type.includes('Video')" class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <svg v-else class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
              </div>
              <div>
                <div class="text-sm font-bold text-slate-900 dark:text-white">{{ topic.title }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                  {{ topic.topicable_type.split('\\').pop() }}
                </div>
              </div>
            </div>
            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
              <button class="p-2 text-slate-400 hover:text-indigo-600 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <!-- Add Lesson Modal -->
    <div v-if="showAddLessonModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-3xl p-8 shadow-2xl border border-slate-200 dark:border-slate-800">
        <h2 class="text-2xl font-bold mb-6 text-slate-900 dark:text-white">Tambah Modul Baru</h2>
        <form @submit.prevent="createLesson" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Judul Modul</label>
            <input v-model="lessonForm.title" type="text" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all text-slate-900 dark:text-white" required placeholder="Contoh: Pengenalan Dasar">
          </div>
          <div class="flex gap-4 mt-8">
            <button type="button" @click="showAddLessonModal = false" class="flex-1 px-4 py-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-xl font-bold hover:opacity-80 transition-all">Batal</button>
            <button type="submit" :disabled="submitting" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/20 flex items-center justify-center">
              <span v-if="submitting" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></span>
              Simpan Modul
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Add Topic Modal -->
    <div v-if="showAddTopicModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-3xl p-8 shadow-2xl border border-slate-200 dark:border-slate-800 overflow-y-auto max-h-[90vh]">
        <h2 class="text-2xl font-bold mb-6 text-slate-900 dark:text-white">Tambah Materi</h2>
        <form @submit.prevent="createTopic" class="space-y-6">
          <div>
            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Judul Materi</label>
            <input v-model="topicForm.title" type="text" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all text-slate-900 dark:text-white" required placeholder="Contoh: Apa itu HTML?">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Tipe Materi</label>
            <div class="grid grid-cols-3 gap-3">
              <button 
                v-for="type in ['richtext', 'video', 'pdf']" 
                :key="type"
                type="button"
                @click="topicForm.type = type"
                class="px-4 py-3 rounded-xl border-2 text-xs font-bold uppercase tracking-wider transition-all"
                :class="topicForm.type === type 
                  ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-500/20' 
                  : 'bg-white dark:bg-slate-800 border-slate-100 dark:border-slate-700 text-slate-400'"
              >
                {{ type }}
              </button>
            </div>
          </div>
          
          <div v-if="topicForm.type === 'richtext'">
            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Konten HTML</label>
            <textarea v-model="topicForm.content.value" rows="6" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all text-slate-900 dark:text-white" placeholder="Masukkan konten HTML di sini..."></textarea>
          </div>

          <div v-if="topicForm.type === 'video'">
            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">URL Video (YouTube/Direct)</label>
            <input v-model="topicForm.content.value" type="text" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all text-slate-900 dark:text-white" placeholder="https://youtube.com/...">
          </div>

          <div class="flex gap-4 mt-8 pt-4 border-t border-slate-100 dark:border-slate-800">
            <button type="button" @click="showAddTopicModal = false" class="flex-1 px-4 py-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-xl font-bold hover:opacity-80 transition-all">Batal</button>
            <button type="submit" :disabled="submitting" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/20 flex items-center justify-center">
              <span v-if="submitting" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></span>
              Simpan Materi
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import LmsService from '../../../services/LmsService';

const route = useRoute();
const courseId = Number(route.params.id);

const course = ref<any>(null);
const loading = ref(false);
const submitting = ref(false);

const showAddLessonModal = ref(false);
const showAddTopicModal = ref(false);
const activeLessonId = ref<number | null>(null);

const lessonForm = ref({ title: '', order: 0 });
const topicForm = ref({
  title: '',
  type: 'richtext',
  content: { value: '' },
  order: 0
});

const fetchData = async () => {
  loading.value = true;
  try {
    const res = await LmsService.getCourseDetails(courseId);
    course.value = res.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const createLesson = async () => {
  submitting.value = true;
  try {
    await LmsService.createLesson(courseId, lessonForm.value);
    await fetchData();
    showAddLessonModal.value = false;
    lessonForm.value = { title: '', order: 0 };
  } catch (err) {
    console.error(err);
  } finally {
    submitting.value = false;
  }
};

const openAddTopicModal = (lessonId: number) => {
  activeLessonId.value = lessonId;
  showAddTopicModal.value = true;
};

const createTopic = async () => {
  if (!activeLessonId.value) return;
  submitting.value = true;
  try {
    await LmsService.createTopic(activeLessonId.value, topicForm.value);
    await fetchData();
    showAddTopicModal.value = false;
    topicForm.value = { title: '', type: 'richtext', content: { value: '' }, order: 0 };
  } catch (err) {
    console.error(err);
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>
