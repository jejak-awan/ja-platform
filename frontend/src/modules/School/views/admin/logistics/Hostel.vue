<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center bg-card p-4 rounded-xl border border-border/50 text-left">
      <div class="flex gap-4 items-center">
        <div class="space-y-1">
          <Label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{{ $t('modules.school.logistics.hostel.labels.hostelBlock') }}</Label>
          <Select v-model="selectedBlock">
            <SelectTrigger class="w-[200px] h-9 bg-background/50">
              <SelectValue :placeholder="$t('modules.school.academic.placeholders.select') + ' ' + $t('modules.school.logistics.hostel.labels.hostelBlock')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="block in blocks"
                :key="block.id"
                :value="String(block.id)"
              >
                {{ block.name }} ({{ block.gender }})
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>
      <div class="flex gap-2">
        <Button
          variant="outline"
          size="sm"
          class="h-9"
          @click="dialogs.block = true"
        >
          <LucideIcon
            name="Building"
            class="w-4 h-4 mr-2"
          />
          {{ $t('modules.school.logistics.hostel.actions.addBlock') }}
        </Button>
        <Button
          size="sm"
          class="h-9 shadow-lg shadow-primary/20"
          @click="handleAddRoom"
        >
          <LucideIcon
            name="Plus"
            class="w-4 h-4 mr-2"
          />
          {{ $t('modules.school.logistics.hostel.actions.addRoom') }}
        </Button>
      </div>
    </div>

    <!-- Rooms and Beds Grid -->
    <div
      v-if="loading"
      class="flex justify-center p-12"
    >
      <LucideIcon
        name="Loader2"
        class="w-8 h-8 animate-spin text-primary"
      />
    </div>
    <div
      v-else-if="!selectedBlock"
      class="p-12 text-center text-muted-foreground border-2 border-dashed rounded-xl"
    >
      {{ $t('modules.school.logistics.hostel.messages.selectBlockFirst') }}
    </div>
    <div
      v-else
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-left"
    >
      <Card
        v-for="room in rooms"
        :key="room.id"
        class="overflow-hidden border-border/50 hover:shadow-md transition-shadow"
      >
        <CardHeader class="bg-muted/30 pb-4">
          <div class="flex justify-between items-center">
            <CardTitle class="text-lg font-bold">
              {{ $t('modules.school.logistics.hostel.labels.room') }} {{ room.room_number }}
            </CardTitle>
            <Badge
              variant="outline"
              class="text-[10px] uppercase font-bold"
            >
              {{ room.capacity }} {{ $t('modules.school.logistics.hostel.labels.bed') }}
            </Badge>
          </div>
          <CardDescription class="text-xs">
            {{ room.features?.join(', ') || $t('modules.school.logistics.hostel.labels.standardFacility') }}
          </CardDescription>
        </CardHeader>
        <CardContent class="p-4">
          <div class="space-y-3">
            <div
              v-for="bed in roomBeds[room.id]"
              :key="bed.id"
              class="flex justify-between items-center p-2 rounded-lg border border-border/50 text-sm"
            >
              <div class="flex items-center gap-2">
                <LucideIcon
                  name="Bed"
                  class="w-4 h-4"
                  :class="bed.is_available ? 'text-success' : 'text-primary'"
                />
                <span class="font-medium text-xs">{{ bed.bed_number }}</span>
              </div>
              <div
                v-if="bed.is_available"
                class="flex items-center gap-1"
              >
                <Badge
                  variant="outline"
                  class="bg-success/10 text-success border-success/20 text-[10px]"
                >
                  {{ $t('modules.school.logistics.hostel.labels.available') }}
                </Badge>
                <Button
                  size="icon"
                  variant="ghost"
                  class="h-6 w-6 text-primary"
                  @click="handleAllocate(bed)"
                >
                  <LucideIcon
                    name="UserPlus"
                    class="w-3.3 h-3.3"
                  />
                </Button>
              </div>
              <div
                v-else
                class="flex items-center gap-1"
              >
                <span class="text-[11px] font-bold text-primary truncate max-w-[100px]">{{ bed.allocation?.student?.full_name }}</span>
                <Button
                  size="icon"
                  variant="ghost"
                  class="h-6 w-6 text-destructive"
                  @click="handleRelease(bed.allocation?.id)"
                >
                  <LucideIcon
                    name="UserMinus"
                    class="w-3.3 h-3.3"
                  />
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Dialogs -->
    <BlockDialog
      v-model:open="dialogs.block"
      @save="fetchBlocks"
    />
    <RoomDialog
      v-model:open="dialogs.room"
      :block-id="Number(selectedBlock)"
      @save="fetchRooms"
    />
    <AllocationDialog
      v-model:open="dialogs.allocation"
      :bed="selectedBed"
      @save="fetchRooms"
    />
    
    <ConfirmModal ref="confirmModal" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { LogisticsService } from '@/modules/School/services/LogisticsService';
import { 
  Card, CardHeader, CardTitle, CardDescription, CardContent, 
  Button, LucideIcon, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
  Badge, ConfirmModal
} from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { parseResponse } from '@/shared/utils/responseParser';

// Dialog Components (to be created)
import BlockDialog from './components/BlockDialog.vue';
import RoomDialog from './components/RoomDialog.vue';
import AllocationDialog from './components/AllocationDialog.vue';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(false);
const blocks = ref<any[]>([]);
const selectedBlock = ref('');
const rooms = ref<any[]>([]);
const roomBeds = ref<Record<number, any[]>>({});
const selectedBed = ref<any>(null);

const dialogs = ref({
  block: false,
  room: false,
  allocation: false
});

const fetchBlocks = async () => {
    try {
        const response = await LogisticsService.getHostelBlocks();
        blocks.value = parseResponse(response).data;
        if (blocks.value.length > 0 && !selectedBlock.value) {
            selectedBlock.value = String(blocks.value[0].id);
        }
    } catch (e) {
        toast.error.fromResponse(e);
    }
}

const fetchRooms = async () => {
    if (!selectedBlock.value) return;
    loading.value = true;
    try {
        const response = await LogisticsService.getHostelRooms(selectedBlock.value);
        rooms.value = parseResponse(response).data;
        
        // Fetch beds for each room
        for (const room of rooms.value) {
            const bedResponse = await LogisticsService.getHostelBeds(room.id);
            roomBeds.value[room.id] = parseResponse(bedResponse).data;
        }
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        loading.value = false;
    }
}

const handleAddRoom = () => {
    if (!selectedBlock.value) {
        toast.error.action(t('modules.school.academic.messages.select') + ' ' + t('modules.school.logistics.hostel.labels.hostelBlock'));
        return;
    }
    dialogs.value.room = true;
}

const handleAllocate = (bed: any) => {
    selectedBed.value = bed;
    dialogs.value.allocation = true;
}

const handleRelease = async (allocationId: string) => {
    if (await confirm({ 
        title: t('modules.school.logistics.hostel.actions.releaseStudent'), 
        description: t('modules.school.logistics.hostel.messages.releaseConfirm'), 
        variant: 'destructive' 
    })) {
        try {
            await LogisticsService.releaseBed(allocationId);
            toast.success.action(t('modules.school.academic.messages.deleteSuccess'));
            fetchRooms();
        } catch (e) {
            toast.error.fromResponse(e);
        }
    }
}

watch(selectedBlock, fetchRooms);

onMounted(fetchBlocks);
</script>
