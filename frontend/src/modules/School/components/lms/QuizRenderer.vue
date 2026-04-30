<template>
  <div class="max-w-3xl mx-auto py-8">
    <div v-if="!attempt" class="text-center bg-white dark:bg-slate-900 p-12 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800">
      <div class="w-20 h-20 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
      </div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Siap untuk kuis?</h2>
      <p class="text-slate-500 mb-8">{{ quiz.value || 'Ikuti kuis ini untuk menguji pemahamanmu tentang materi ini.' }}</p>
      
      <div class="grid grid-cols-2 gap-4 max-w-sm mx-auto mb-8">
        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl">
          <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Pass Score</div>
          <div class="text-xl font-bold text-slate-900 dark:text-white">{{ quiz.pass_score }}%</div>
        </div>
        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl">
          <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Pertanyaan</div>
          <div class="text-xl font-bold text-slate-900 dark:text-white">{{ quiz.questions.length }}</div>
        </div>
      </div>

      <button 
        @click="startAttempt" 
        :disabled="loading"
        class="px-10 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-bold shadow-xl shadow-indigo-500/20 transition-all active:scale-95"
      >
        <span v-if="loading" class="inline-block w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></span>
        Mulai Kuis Sekarang
      </button>
    </div>

    <div v-else-if="!finished" class="space-y-8">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Pertanyaan {{ currentQuestionIndex + 1 }} dari {{ quiz.questions.length }}</h2>
        <div class="px-4 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded-lg text-sm font-bold">
          Score: {{ currentQuestion.score }} pts
        </div>
      </div>

      <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800">
        <p class="text-lg font-medium text-slate-900 dark:text-white mb-8">{{ currentQuestion.value }}</p>
        
        <div class="space-y-3">
          <button 
            v-for="option in currentQuestion.options" 
            :key="option.id"
            @click="selectOption(option.id)"
            class="w-full p-5 rounded-2xl border-2 text-left transition-all flex items-center"
            :class="answers[currentQuestion.id] === option.id 
              ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-600 text-indigo-600' 
              : 'bg-white dark:bg-slate-800 border-slate-100 dark:border-slate-700 hover:border-indigo-200 text-slate-600 dark:text-slate-400'"
          >
            <div class="w-6 h-6 rounded-full border-2 border-current mr-4 flex items-center justify-center">
              <div v-if="answers[currentQuestion.id] === option.id" class="w-3 h-3 bg-indigo-600 rounded-full"></div>
            </div>
            {{ option.value }}
          </button>
        </div>
      </div>

      <div class="flex justify-between mt-8">
        <button 
          @click="prevQuestion" 
          :disabled="currentQuestionIndex === 0"
          class="px-6 py-3 text-slate-500 font-bold hover:text-slate-700 disabled:opacity-20"
        >
          Sebelumnya
        </button>
        
        <button 
          v-if="currentQuestionIndex < quiz.questions.length - 1"
          @click="nextQuestion"
          class="px-8 py-3 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl font-bold"
        >
          Selanjutnya
        </button>
        
        <button 
          v-else
          @click="submitQuiz"
          :disabled="submitting"
          class="px-10 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-lg shadow-emerald-500/20"
        >
          Kirim Jawaban
        </button>
      </div>
    </div>

    <div v-else class="text-center bg-white dark:bg-slate-900 p-12 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800">
      <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6" :class="passed ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'">
        <svg v-if="passed" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        <svg v-else class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
      </div>
      
      <h2 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">{{ passed ? 'Lulus!' : 'Belum Lulus' }}</h2>
      <p class="text-slate-500 mb-8">Skor kamu: <span class="font-bold text-indigo-600 text-2xl">{{ score }}%</span></p>
      
      <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl mb-8 max-w-sm mx-auto">
        <p class="text-sm text-slate-500">{{ passed ? 'Selamat! Kamu telah menyelesaikan materi ini.' : 'Jangan menyerah! Pelajari kembali materinya dan coba lagi.' }}</p>
      </div>

      <button @click="$emit('finished')" class="px-8 py-3 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl font-bold">
        Lanjut ke Materi Lain
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import LmsService from '../../services/LmsService';

const props = defineProps<{
  quiz: any;
}>();

const emit = defineEmits(['finished', 'completed']);

const attempt = ref<any>(null);
const currentQuestionIndex = ref(0);
const answers = ref<Record<number, any>>({});
const loading = ref(false);
const submitting = ref(false);
const finished = ref(false);
const score = ref(0);

const currentQuestion = computed(() => props.quiz.questions[currentQuestionIndex.value]);
const passed = computed(() => score.value >= props.quiz.pass_score);

const startAttempt = async () => {
  loading.value = true;
  try {
    const res = await LmsService.startQuiz(props.quiz.id);
    attempt.value = res.data.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const selectOption = (optionId: number) => {
  answers.value[currentQuestion.value.id] = optionId;
};

const nextQuestion = () => {
  if (currentQuestionIndex.value < props.quiz.questions.length - 1) {
    currentQuestionIndex.value++;
  }
};

const prevQuestion = () => {
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--;
  }
};

const submitQuiz = async () => {
  submitting.value = true;
  try {
    const res = await LmsService.submitQuiz(attempt.value.id, answers.value);
    score.value = res.data.data.score;
    finished.value = true;
    if (score.value >= props.quiz.pass_score) {
      emit('completed');
    }
  } catch (err) {
    console.error(err);
  } finally {
    submitting.value = false;
  }
};
</script>
