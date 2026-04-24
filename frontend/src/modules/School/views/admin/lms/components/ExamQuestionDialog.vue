<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[900px] h-[85vh] flex flex-col p-0">
      <DialogHeader class="p-6 pb-0 flex flex-row items-center justify-between space-y-0">
        <div>
          <DialogTitle>{{ $t('features.school.lms.actions.manageQuestions') }}: {{ exam?.title }}</DialogTitle>
          <DialogDescription>Select and organize questions for this assessment.</DialogDescription>
        </div>
        <div class="flex gap-2">
          <Button
            variant="outline"
            size="sm"
            @click="showPicker = true"
          >
            <LucideIcon
              name="Search"
              class="w-4 h-4 mr-2"
            />
            Ambil dari Bank Soal
          </Button>
          <Button
            size="sm"
            @click="handleSave"
          >
            <LucideIcon
              name="Save"
              class="w-4 h-4 mr-2"
            />
            {{ $t('common.labels.save') }}
          </Button>
        </div>
      </DialogHeader>

      <div class="p-6 flex-1 flex flex-col min-h-0 space-y-4">
        <!-- Selected Questions Table -->
        <div class="border rounded-xl flex-1 overflow-hidden flex flex-col bg-muted/10">
          <div class="overflow-auto flex-1">
            <table class="w-full text-sm">
              <thead class="bg-background sticky top-0 border-b">
                <tr>
                  <th class="p-3 text-left font-bold w-12">
                    #
                  </th>
                  <th class="p-3 text-left font-bold">
                    Pertanyaan
                  </th>
                  <th class="p-3 text-left font-bold w-32">
                    Tipe
                  </th>
                  <th class="p-3 text-center font-bold w-20">
                    Poin
                  </th>
                  <th class="p-3 text-right font-bold w-16" />
                </tr>
              </thead>
              <tbody class="divide-y">
                <tr
                  v-for="(q, idx) in selectedQuestions"
                  :key="q.id"
                  class="bg-background/50 hover:bg-background transition-colors"
                >
                  <td class="p-3 text-muted-foreground font-mono text-xs">
                    {{ idx + 1 }}
                  </td>
                  <td class="p-3">
                    <div class="line-clamp-2 font-medium">
                      {{ q.content }}
                    </div>
                    <div class="text-[10px] text-muted-foreground mt-0.5">
                      Bank: {{ q.question_bank?.name }}
                    </div>
                  </td>
                  <td class="p-3">
                    <Badge
                      variant="outline"
                      class="text-[10px] font-bold uppercase"
                    >
                      {{ q.type }}
                    </Badge>
                  </td>
                  <td class="p-3 text-center">
                    <Input
                      v-model="q.pivot_points"
                      type="number"
                      class="h-8 w-16 mx-auto text-center font-bold"
                    />
                  </td>
                  <td class="p-3 text-right">
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-8 w-8 text-destructive hover:bg-destructive/5"
                      @click="removeQuestion(idx)"
                    >
                      <LucideIcon
                        name="X"
                        class="w-4 h-4"
                      />
                    </Button>
                  </td>
                </tr>
                <tr
                  v-if="!selectedQuestions.length"
                  class="text-center"
                >
                  <td
                    colspan="5"
                    class="p-20 text-muted-foreground italic"
                  >
                    Belum ada soal terpilih. Klik "Ambil dari Bank Soal" untuk memulai.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Picker Side Panel / Dialog overlay -->
      <div
        v-if="showPicker"
        class="absolute inset-0 bg-background/95 z-50 flex flex-col"
      >
        <div class="p-6 border-b flex justify-between items-center bg-muted/30">
          <h3 class="text-lg font-bold">
            Pilih dari Bank Soal
          </h3>
          <Button
            variant="ghost"
            size="icon"
            @click="showPicker = false"
          >
            <LucideIcon
              name="X"
              class="w-5 h-5"
            />
          </Button>
        </div>
        <div class="p-6 flex flex-col flex-1 min-h-0 space-y-4">
          <div class="flex gap-4">
            <Select
              v-model="filterBankId"
              class="w-64"
            >
              <SelectTrigger><SelectValue placeholder="Pilih Bank Soal" /></SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="b in banks"
                  :key="b.id"
                  :value="String(b.id)"
                >
                  {{ b.name }}
                </SelectItem>
              </SelectContent>
            </Select>
            <Input
              v-model="searchQuery"
              placeholder="Cari pertanyaan..."
              class="flex-1"
            />
          </div>

          <div class="border rounded-xl flex-1 overflow-auto">
            <table class="w-full text-sm">
              <thead class="bg-muted/50 sticky top-0 border-b">
                <tr>
                  <th class="p-3 text-left font-bold w-12" />
                  <th class="p-3 text-left font-bold">
                    Pertanyaan
                  </th>
                  <th class="p-3 text-left font-bold w-24">
                    Level
                  </th>
                  <th class="p-3 text-right font-bold w-24">
                    Aksi
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y">
                <tr
                  v-for="q in availableQuestions"
                  :key="q.id"
                  class="hover:bg-muted/10"
                >
                  <td class="p-3">
                    <LucideIcon
                      :name="q.type === 'multiple_choice' ? 'List' : 'Type'"
                      class="w-4 h-4 text-muted-foreground"
                    />
                  </td>
                  <td class="p-3">
                    <div class="line-clamp-2">
                      {{ q.content }}
                    </div>
                  </td>
                  <td class="p-3 capitalize">
                    <span :class="getLevelClass(q.level)">{{ q.level }}</span>
                  </td>
                  <td class="p-3 text-right">
                    <Button 
                      v-if="!isChosen(q.id)" 
                      size="sm" 
                      variant="outline" 
                      class="h-8 border-primary/20 hover:bg-primary/5 text-primary"
                      @click="chooseQuestion(q)"
                    >
                      Tambahkan
                    </Button>
                    <Badge
                      v-else
                      variant="outline"
                      class="bg-success/10 text-success border-success/20"
                    >
                      Terpilih
                    </Badge>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription,
  Button, LucideIcon, Badge, Input, Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/components/ui';
import { LmsService } from '@/modules/School/services/LmsService';
import { parseResponse } from '@/utils/responseParser';
import { useToast } from '@/composables/useToast';

const props = defineProps<{
  open: boolean;
  exam: any;
}>();

const emit = defineEmits(['update:open']);

const toast = useToast();

const loading = ref(false);
const showPicker = ref(false);
const selectedQuestions = ref<any[]>([]);
const banks = ref<any[]>([]);
const allBankQuestions = ref<any[]>([]);
const filterBankId = ref<string>('');
const searchQuery = ref('');

const fetchData = async () => {
    if (!props.exam?.id) return;
    loading.value = true;
    try {
        const [examQuestionsRes, banksRes] = await Promise.all([
          LmsService.getExamQuestions(props.exam.id),
          LmsService.getQuestionBanks()
        ]);
        
        selectedQuestions.value = (parseResponse(examQuestionsRes).data || []).map((q: any) => ({
          ...q,
          pivot_points: q.pivot?.points || 1.00
        }));
        banks.value = parseResponse(banksRes).data || [];
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        loading.value = false;
    }
}

watch(() => props.open, (isOpen) => {
    if (isOpen) fetchData();
});

watch(filterBankId, async (val) => {
    if (val) {
        try {
            const res = await LmsService.getQuestions(Number(val));
            allBankQuestions.value = parseResponse(res).data || [];
        } catch (e) {
            console.error(e);
        }
    }
});

const availableQuestions = computed(() => {
    if (!searchQuery.value) return allBankQuestions.value;
    return allBankQuestions.value.filter(q => q.content.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

const isChosen = (id: number) => selectedQuestions.value.some(sq => sq.id === id);

const chooseQuestion = (q: any) => {
    if (isChosen(q.id)) return;
    selectedQuestions.value.push({
        ...q,
        pivot_points: 1.00
    });
};

const removeQuestion = (idx: number) => {
    selectedQuestions.value.splice(idx, 1);
};

const handleSave = async () => {
    try {
        const payload = selectedQuestions.value.map((q, idx) => ({
            question_id: q.id,
            sort_order: idx + 1,
            points: q.pivot_points
        }));
        await LmsService.syncExamQuestions(props.exam.id, payload);
        toast.success.action('Berhasil menyimpan daftar soal.');
        emit('update:open', false);
    } catch (e) {
        toast.error.fromResponse(e);
    }
};

const getLevelClass = (level: string) => {
  switch (level) {
    case 'easy': return 'text-success font-medium';
    case 'medium': return 'text-warning font-medium';
    case 'hard': return 'text-destructive font-medium';
    default: return '';
  }
}

onMounted(() => {
    if (props.open) fetchData();
});
</script>
