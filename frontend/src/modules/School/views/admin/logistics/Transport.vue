<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center bg-card p-4 rounded-xl border border-border/50 text-left">
      <div class="flex gap-2">
        <Button
          variant="outline"
          size="sm"
          class="h-9"
          @click="dialogs.vehicle = true"
        >
          <LucideIcon
            name="Bus"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.logistics.transport.actions.busFleet') }}
        </Button>
        <Button
          variant="outline"
          size="sm"
          class="h-9"
          @click="dialogs.route = true"
        >
          <LucideIcon
            name="Map"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.logistics.transport.actions.routeData') }}
        </Button>
      </div>
      <Button
        size="sm"
        class="h-9 shadow-lg shadow-primary/20"
        @click="dialogs.register = true"
      >
        <LucideIcon
          name="UserPlus"
          class="w-4 h-4 mr-2"
        />
        {{ $t('features.school.logistics.transport.actions.registerTransport') }}
      </Button>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
      <Card
        v-for="vehicle in vehicles"
        :key="vehicle.id"
        class="border-border/50"
      >
        <CardHeader class="pb-2">
          <div class="flex justify-between items-start">
            <div>
              <CardTitle class="text-sm font-semibold">
                {{ vehicle.plate_number }}
              </CardTitle>
              <CardDescription class="text-[10px]">
                {{ vehicle.model }}
              </CardDescription>
            </div>
            <Badge
              :variant="vehicle.status === 'active' ? 'outline' : 'destructive'"
              class="text-[10px]"
            >
              {{ vehicle.status }}
            </Badge>
          </div>
        </CardHeader>
        <CardContent>
          <div class="flex flex-col gap-2">
            <div class="flex justify-between items-center text-xs">
              <span class="text-muted-foreground">{{ $t('features.school.logistics.transport.labels.driver') }}:</span>
              <span class="font-medium">{{ vehicle.driver_name || '-' }}</span>
            </div>
            <div class="space-y-1">
              <div class="flex justify-between text-[10px] mb-1">
                <span>{{ $t('features.school.logistics.transport.labels.seatCapacity') }}</span>
                <span>{{ vehicle.registrations_count }} / {{ vehicle.capacity }}</span>
              </div>
              <div class="w-full bg-muted rounded-full h-1.5 overflow-hidden">
                <div
                  class="bg-primary h-full transition-all duration-500"
                  :style="{ width: `${(vehicle.registrations_count / vehicle.capacity) * 100}%` }"
                />
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Registrations Table -->
    <Card class="border-border/50 text-left">
      <CardHeader>
        <CardTitle class="text-lg">
          {{ $t('features.school.logistics.transport.labels.studentList') }}
        </CardTitle>
        <CardDescription>{{ $t('features.school.logistics.transport.labels.studentListDesc') }}</CardDescription>
      </CardHeader>
      <CardContent class="p-0">
        <DataTable
          :table="table"
          :loading="loading"
        />
      </CardContent>
    </Card>

    <!-- Dialogs -->
    <VehicleDialog
      v-model:open="dialogs.vehicle"
      @save="fetchVehicles"
    />
    <RouteDialog
      v-model:open="dialogs.route"
      @save="fetchRoutes"
    />
    <TransportRegistrationDialog
      v-model:open="dialogs.register"
      :vehicles="vehicles"
      :routes="routes"
      @save="fetchRegistrations"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import { useI18n } from 'vue-i18n';
import { LogisticsService } from '@/modules/School/services/LogisticsService';
import {
  Card, CardHeader, CardTitle, CardDescription, CardContent,
  Button, LucideIcon, Badge, DataTable
} from '@/components/ui';
import { createColumnHelper, useVueTable, getCoreRowModel } from '@tanstack/vue-table';
import { useToast } from '@/composables/useToast';
import { parseResponse } from '@/utils/responseParser';

// Components
import VehicleDialog from './components/VehicleDialog.vue';
import RouteDialog from './components/RouteDialog.vue';
import TransportRegistrationDialog from './components/TransportRegistrationDialog.vue';

const { t } = useI18n();
const toast = useToast();
const loading = ref(false);
const vehicles = ref<any[]>([]);
const routes = ref<any[]>([]);
const registrations = ref<any[]>([]);

const dialogs = ref({
   vehicle: false,
   route: false,
   register: false
});

const columnHelper = createColumnHelper<any>();
const columns = [
   columnHelper.accessor('student.full_name', { header: t('common.labels.user') }),
   columnHelper.accessor('route.name', { header: t('features.school.logistics.transport.actions.routeData') }),
   columnHelper.accessor('pickup_point', { header: t('features.school.logistics.transport.labels.pickupPoint') }),
   columnHelper.accessor('vehicle.plate_number', { header: t('features.school.logistics.transport.labels.vehicle') }),
   columnHelper.accessor('status', {
      header: t('common.labels.status'),
      cell: info => h(Badge, { variant: info.getValue() === 'active' ? 'outline' : 'secondary', class: 'text-[10px] font-semibold' }, info.getValue())
   })
];

const table = useVueTable({
   get data() { return registrations.value },
   get columns() { return columns },
   getCoreRowModel: getCoreRowModel()
});

const fetchVehicles = async () => {
   try {
      const response = await LogisticsService.getTransportVehicles();
      vehicles.value = parseResponse(response).data;
   } catch (e) {
      toast.error.fromResponse(e);
   }
}

const fetchRoutes = async () => {
   try {
      const response = await LogisticsService.getTransportRoutes();
      routes.value = parseResponse(response).data;
   } catch (e) {
      toast.error.fromResponse(e);
   }
}

const fetchRegistrations = async () => {
   loading.value = true;
   try {
      const response = await LogisticsService.getTransportRegistrations();
      registrations.value = parseResponse(response).data;
   } catch (e) {
      toast.error.fromResponse(e);
   } finally {
      loading.value = false;
   }
}

onMounted(() => {
   fetchVehicles();
   fetchRoutes();
   fetchRegistrations();
});
</script>
