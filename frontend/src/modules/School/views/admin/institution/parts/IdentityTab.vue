<template>
  <div>
    <CardContent class="p-6">
      <IdentitySummary
        :form="form"
        :is-restricted="isRestricted"
        :levels="levels"
        @edit-foundation="showFoundationDialog = true"
      />
    </CardContent>

    <FoundationDialog
      v-model:show="showFoundationDialog"
      :form="form"
      @save="handleSaveFoundation"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { CardContent } from '@/shared/components/ui';
import IdentitySummary from '../components/identity/IdentitySummary.vue';
import FoundationDialog from '../components/dialogs/FoundationDialog.vue';

defineProps<{
  form: any;
  isRestricted: boolean;
  levels: any[];
}>();

const emit = defineEmits<{
  (e: 'save-school', form: any): void;
}>();

const showFoundationDialog = ref(false);

const handleSaveFoundation = (updatedForm: any) => {
  emit('save-school', updatedForm);
  showFoundationDialog.value = false;
};
</script>
