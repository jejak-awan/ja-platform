<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('common.labels.edit') || 'Update' : $t('features.school.extensions.tabs.circulations') }}</DialogTitle>
        <DialogDescription>
          {{ $t('features.school.extensions.subtitle') }}
        </DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4">
        <div
          v-if="!isEdit"
          class="grid gap-2"
        >
          <Label>{{ $t('features.school.extensions.tabs.library') }} <span class="text-destructive">*</span></Label>
          <Select
            v-model="form.library_book_id"
            required
          >
            <SelectTrigger><SelectValue :placeholder="$t('academic.placeholders.selectDay')" /></SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="book in books"
                :key="book.id"
                :value="book.id.toString()"
              >
                {{ book.title }} (Qty: {{ book.quantity }})
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div
          v-if="!isEdit"
          class="grid gap-2"
        >
          <Label>{{ $t('features.school.extensions.labels.borrower') }} <span class="text-destructive">*</span></Label>
          <div class="flex gap-2">
            <Select v-model="form.borrower_type">
              <SelectTrigger class="w-[120px]">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="Modules\School\Models\Student">
                  {{ $t('common.labels.student') }}
                </SelectItem>
                <SelectItem value="Modules\School\Models\Staff">
                  {{ $t('common.navigation.menu.staffIndex') }}
                </SelectItem>
              </SelectContent>
            </Select>
            <Select
              v-model="form.borrower_id"
              class="flex-1"
            >
              <SelectTrigger>
                <SelectValue :placeholder="form.borrower_type.includes('Student') ? $t('academic.placeholders.selectStudentToAdd') : $t('academic.placeholders.selectTeacher')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="item in borrowers"
                  :key="item.id"
                  :value="item.id.toString()"
                >
                  {{ item.full_name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <div
          v-if="!isEdit"
          class="grid grid-cols-2 gap-4"
        >
          <div class="grid gap-2">
            <Label for="borrow_date">{{ $t('common.labels.date') }} <span class="text-destructive">*</span></Label>
            <Input
              id="borrow_date"
              v-model="form.borrow_date"
              type="date"
              required
            />
          </div>
          <div class="grid gap-2">
            <Label for="due_date">{{ $t('features.school.extensions.labels.dueDate') }} <span class="text-destructive">*</span></Label>
            <Input
              id="due_date"
              v-model="form.due_date"
              type="date"
              required
            />
          </div>
        </div>

        <div
          v-if="isEdit"
          class="grid gap-2"
        >
          <Label>{{ $t('common.labels.status') }} <span class="text-destructive">*</span></Label>
          <Select
            v-model="form.status"
            required
          >
            <SelectTrigger><SelectValue :placeholder="$t('features.school.extensions.placeholders.selectStatus')" /></SelectTrigger>
            <SelectContent>
              <SelectItem value="borrowed">
                {{ $t('common.labels.borrowed') || 'Borrowed' }}
              </SelectItem>
              <SelectItem value="returned">
                {{ $t('common.labels.returned') || 'Returned' }}
              </SelectItem>
              <SelectItem value="overdue">
                {{ $t('common.labels.overdue') || 'Overdue' }}
              </SelectItem>
              <SelectItem value="lost">
                {{ $t('common.labels.lost') || 'Lost' }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div
          v-if="isEdit"
          class="grid grid-cols-2 gap-4"
        >
          <div class="grid gap-2">
            <Label for="return_date">{{ $t('common.labels.date') }}</Label>
            <Input
              id="return_date"
              v-model="form.return_date"
              type="date"
            />
          </div>
          <div class="grid gap-2">
            <Label for="fine">{{ $t('features.school.finance.labels.paidAmount') }} (Rp)</Label>
            <Input
              id="fine"
              v-model="form.fine_amount"
              type="number"
              min="0"
            />
          </div>
        </div>
      </div>

      <DialogFooter>
        <Button
          variant="ghost"
          @click="$emit('update:open', false)"
        >
          {{ $t('common.labels.cancel') }}
        </Button>
        <Button
          :disabled="loading"
          @click="handleSubmit"
        >
          <LucideIcon
            v-if="loading"
            name="Loader2"
            class="w-4 h-4 mr-2 animate-spin"
          />
          {{ isEdit ? $t('common.labels.save') : $t('common.labels.save') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Button, Input, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, 
  LucideIcon
} from '@/components/ui';
import { OperationsService } from '@/modules/School/services/OperationsService';
import { parseResponse } from '@/utils/responseParser';

interface LibraryBook {
  id: number | string;
  title: string;
  quantity: number;
}

interface Borrower {
  id: number | string;
  full_name: string;
}

const props = defineProps<{
  open: boolean;
  isEdit: boolean;
  initialData: any;
  loading: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

const books = ref<LibraryBook[]>([]);
const borrowers = ref<Borrower[]>([]);

const form = ref<any>({
  library_book_id: '',
  borrower_type: 'Modules\\School\\Models\\Student',
  borrower_id: '',
  borrow_date: new Date().toISOString().split('T')[0],
  due_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
  return_date: '',
  fine_amount: 0,
  status: 'Borrowed',
});

const fetchBooks = async () => {
    try {
        const response = await OperationsService.getLibraryBooks();
        books.value = parseResponse<LibraryBook>(response).data;
    } catch {
        // Silent fail for background fetch
    }
}

const fetchBorrowers = async () => {
    try {
        const type = form.value.borrower_type.includes('Student') ? 'students' : 'staff';
        const response = await OperationsService.getStudentAffairs(type);
        borrowers.value = parseResponse<Borrower>(response).data;
    } catch {
        // Silent fail for background fetch
    }
}

watch(() => form.value.borrower_type, () => {
    borrowers.value = [];
    form.value.borrower_id = '';
    fetchBorrowers();
});

watch(() => props.open, (newVal) => {
  if (newVal) {
    if (props.initialData) {
      form.value = { 
          ...props.initialData,
          library_book_id: props.initialData.library_book_id?.toString(),
          borrower_id: props.initialData.borrower_id?.toString(),
          return_date: props.initialData.return_date ? new Date(props.initialData.return_date).toISOString().split('T')[0] : '',
      };
    } else {
      form.value = {
        library_book_id: '',
        borrower_type: 'Modules\\School\\Models\\Student',
        borrower_id: '',
        borrow_date: new Date().toISOString().split('T')[0],
        due_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        return_date: '',
        fine_amount: 0,
        status: 'Borrowed',
      };
      if (books.value.length === 0) fetchBooks();
      fetchBorrowers();
    }
  }
});

const handleSubmit = () => {
  emit('submit', form.value);
};

onMounted(() => {
    fetchBooks();
});
</script>
