<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-8 text-left">
      <div>
        <h1 class="text-2xl font-bold text-foreground">
          {{ $t('features.school.logistics.labels.logisticsEcosystem') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ $t('features.school.logistics.subtitle') }}
        </p>
      </div>
    </div>

    <Tabs
      v-model="activeTab"
      class="w-full"
    >
      <TabsList class="mb-6 border-b bg-transparent p-0 h-auto gap-6 flex justify-start">
        <TabsTrigger 
          value="hostel" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all font-bold"
        >
          <LucideIcon
            name="Home"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.logistics.labels.hostelManagement') }}
        </TabsTrigger>
        <TabsTrigger 
          value="transport" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all font-bold"
        >
          <LucideIcon
            name="Bus"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.logistics.labels.transport') }}
        </TabsTrigger>
        <TabsTrigger 
          value="inventory" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all font-bold"
        >
          <LucideIcon
            name="Box"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.logistics.labels.inventory') }}
        </TabsTrigger>
        <TabsTrigger 
          value="career" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all font-bold"
        >
          <LucideIcon
            name="Briefcase"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.logistics.labels.careerCenter') }}
        </TabsTrigger>
      </TabsList>

      <div class="mt-4">
        <TabsContent value="hostel">
          <Hostel />
        </TabsContent>
        <TabsContent value="transport">
          <Transport />
        </TabsContent>
        <TabsContent value="inventory">
          <Inventory />
        </TabsContent>
        <TabsContent value="career">
          <JobPortal />
        </TabsContent>
      </div>
    </Tabs>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Tabs, TabsList, TabsTrigger, TabsContent, LucideIcon } from '@/components/ui';
import Hostel from './Hostel.vue';
import Transport from './Transport.vue';
import Inventory from './Inventory.vue';
import JobPortal from './JobPortal.vue';

const activeTab = ref('hostel');
</script>
