<template>
  <div class="space-y-6">
    <div class="flex justify-between items-start">
      <div>
        <h1 class="text-2xl font-bold text-foreground">
          {{ $t('features.redis.title') }}
        </h1>
        <p class="text-muted-foreground">
          {{ $t('features.redis.description') }}
        </p>
      </div>
    </div>

    <!-- Shadcn Tabs -->
    <Tabs
      v-model="activeTab"
      class="w-full"
    >
      <div class="mb-10">
        <TabsList class="bg-transparent p-0 h-auto gap-0">
          <TabsTrigger
            value="statistics"
            class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
          >
            <BarChart3 class="w-4 h-4 mr-2" />
            {{ $t('features.redis.tabs.statistics') }}
          </TabsTrigger>
          <TabsTrigger
            value="settings"
            class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
          >
            <Settings class="w-4 h-4 mr-2" />
            {{ $t('features.redis.tabs.settings') }}
          </TabsTrigger>
          <TabsTrigger
            value="cache"
            class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
          >
            <Database class="w-4 h-4 mr-2" />
            {{ $t('features.redis.tabs.cache') }}
          </TabsTrigger>
        </TabsList>
      </div>

      <!-- Statistics Tab -->
      <TabsContent
        value="statistics"
        class="px-6 space-y-6"
      >
        <Card
          v-if="stats.total_keys === 0 && (stats.hits > 0 || stats.misses > 0 || stats.total_commands > 0)"
          class="border-blue-500/30 bg-blue-500/5"
        >
          <CardContent class="pt-4 pb-4">
            <p class="text-sm text-blue-700 dark:text-blue-300">
              Cache keys saat ini sudah kosong. Angka Hit/Miss dan Total Commands di tab statistik adalah metrik kumulatif Redis (sejak service start), sehingga tidak ikut reset saat clear cache.
            </p>
          </CardContent>
        </Card>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <Card class="border-none bg-muted/20 shadow-none">
            <CardContent class="pt-6">
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-blue-500/10 text-blue-500">
                  <Database class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm text-muted-foreground font-medium">
                    {{ $t('features.redis.statistics.grid.version') }}
                  </p>
                  <p class="text-2xl font-bold">
                    {{ stats.version || '-' }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="border-none bg-muted/20 shadow-none">
            <CardContent class="pt-6">
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-emerald-500/10 text-emerald-500">
                  <HardDrive class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm text-muted-foreground font-medium">
                    {{ $t('features.redis.statistics.grid.memory') }}
                  </p>
                  <p class="text-2xl font-bold">
                    {{ stats.used_memory || '-' }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="border-none bg-muted/20 shadow-none">
            <CardContent class="pt-6">
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-amber-500/10 text-amber-500">
                  <Zap class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm text-muted-foreground font-medium">
                    {{ $t('features.redis.statistics.grid.keys') }}
                  </p>
                  <p class="text-2xl font-bold">
                    {{ stats.total_keys || 0 }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="border-none bg-muted/20 shadow-none">
            <CardContent class="pt-6">
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-orange-500/10 text-orange-500">
                  <Clock class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm text-muted-foreground font-medium">
                    {{ $t('features.redis.statistics.grid.uptime') }}
                  </p>
                  <p class="text-2xl font-bold">
                    {{ stats.uptime_days || '-' }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="border-none bg-muted/20 shadow-none">
            <CardContent class="pt-6">
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-purple-500/10 text-purple-500">
                  <Users class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm text-muted-foreground font-medium">
                    {{ $t('features.redis.statistics.grid.clients') }}
                  </p>
                  <p class="text-2xl font-bold">
                    {{ stats.connected_clients || 0 }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="border-none bg-muted/20 shadow-none">
            <CardContent class="pt-6">
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-indigo-500/10 text-indigo-500">
                  <Target class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm text-muted-foreground font-medium">
                    {{ $t('features.redis.statistics.grid.hitRate') }}
                  </p>
                  <p class="text-2xl font-bold">
                    {{ stats.hit_rate || '0%' }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="border-none bg-muted/20 shadow-none">
            <CardContent class="pt-6">
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-pink-500/10 text-pink-500">
                  <Activity class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm text-muted-foreground font-medium">
                    {{ $t('features.redis.statistics.grid.ops') }}
                  </p>
                  <p class="text-2xl font-bold">
                    {{ stats.operations_per_sec || 0 }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="border-none bg-muted/20 shadow-none">
            <CardContent class="pt-6">
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-slate-500/10 text-slate-500">
                  <BarChart3 class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm text-muted-foreground font-medium">
                    {{ $t('features.redis.statistics.grid.commands') }}
                  </p>
                  <p class="text-2xl font-bold">
                    {{ formatNumber(stats.total_commands) || 0 }}
                  </p>
                  <p class="text-xs text-muted-foreground mt-1">
                    cumulative
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <Card>
          <CardHeader class="px-6 py-4 border-b border-border/50 bg-muted/20">
            <CardTitle class="text-lg flex items-center gap-2">
              <Activity class="w-5 h-5 text-primary" />
              {{ $t('features.redis.statistics.hitMiss.title') }}
            </CardTitle>
            <p class="text-xs text-muted-foreground">
              Server Lifetime Metrics
            </p>
          </CardHeader>
          <CardContent class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="flex justify-between items-center p-4 bg-muted/30 rounded-lg border border-border/50 shadow-sm">
                <span class="font-medium text-foreground">{{ $t('features.redis.statistics.hitMiss.hits') }}</span>
                <div class="text-right">
                  <span class="text-xl font-bold text-emerald-600">{{ formatNumber(stats.hits) || 0 }}</span>
                  <p class="text-[11px] text-muted-foreground">
                    cumulative
                  </p>
                </div>
              </div>
              <div class="flex justify-between items-center p-4 bg-muted/30 rounded-lg border border-border/50 shadow-sm">
                <span class="font-medium text-foreground">{{ $t('features.redis.statistics.hitMiss.misses') }}</span>
                <div class="text-right">
                  <span class="text-xl font-bold text-destructive">{{ formatNumber(stats.misses) || 0 }}</span>
                  <p class="text-[11px] text-muted-foreground">
                    cumulative
                  </p>
                </div>
              </div>
              <div class="flex justify-between items-center p-4 bg-muted/30 rounded-lg border border-border/50 shadow-sm">
                <span class="font-medium text-foreground">{{ $t('features.redis.statistics.hitMiss.hitRate') }}</span>
                <span class="text-xl font-bold text-blue-600">{{ stats.hit_rate || '0%' }}</span>
              </div>
            </div>

            <div class="mt-6">
              <h4 class="text-sm font-semibold text-foreground mb-3">
                Since Last Clear (Local Snapshot)
              </h4>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex justify-between items-center p-4 bg-muted/30 rounded-lg border border-border/50 shadow-sm">
                  <span class="font-medium text-foreground">Hits</span>
                  <span class="text-xl font-bold text-emerald-600">{{ formatNumber(statsSinceLastClear.hits) }}</span>
                </div>
                <div class="flex justify-between items-center p-4 bg-muted/30 rounded-lg border border-border/50 shadow-sm">
                  <span class="font-medium text-foreground">Misses</span>
                  <span class="text-xl font-bold text-destructive">{{ formatNumber(statsSinceLastClear.misses) }}</span>
                </div>
                <div class="flex justify-between items-center p-4 bg-muted/30 rounded-lg border border-border/50 shadow-sm">
                  <span class="font-medium text-foreground">Hit Rate</span>
                  <span class="text-xl font-bold text-blue-600">{{ statsSinceLastClear.hitRate }}</span>
                </div>
              </div>
              <p class="text-xs text-muted-foreground mt-2">
                Snapshot diperbarui otomatis setelah aksi clear cache dari halaman ini.
              </p>
            </div>
          </CardContent>
          <div class="px-6 py-4 border-t border-border/50 flex items-center justify-between">
            <Button 
              variant="outline"
              size="sm"
              :disabled="loadingStats" 
              @click="loadStats" 
            >
              <RefreshCw
                v-if="loadingStats"
                class="w-4 h-4 mr-2"
              />
              {{ loadingStats ? $t('features.redis.messages.loading') : $t('features.redis.statistics.refresh') }}
            </Button>
            <span class="text-xs text-muted-foreground">{{ $t('features.redis.statistics.autoRefresh') }}</span>
          </div>
        </Card>
      </TabsContent>

      <!-- Settings Tab -->
      <TabsContent
        value="settings"
        class="px-6 space-y-6"
      >
        <!-- Global Driver Warning -->
        <Card
          v-if="!isRedisDriverActive"
          class="border-amber-500/40 bg-amber-500/5"
        >
          <CardContent class="pt-6">
            <div class="flex items-start gap-4">
              <div class="p-2 rounded-full bg-amber-500/20 text-amber-600">
                <AlertTriangle class="w-6 h-6" />
              </div>
              <div class="space-y-1">
                <h4 class="font-bold text-amber-800 dark:text-amber-200 text-sm">
                  Redis Disabled
                </h4>
                <p class="text-sm text-amber-700 dark:text-amber-300 leading-relaxed">
                  Redis is currently disabled in system settings. To enable it, go to 
                  <router-link
                    to="/dash/settings?tab=performance"
                    class="underline font-semibold hover:text-amber-900 inline-flex items-center gap-1"
                  >
                    Settings <ArrowRight class="w-3 h-3" /> Performance
                  </router-link>
                  and select "Redis" or "Redis + Failover" as the Cache Driver.
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <form
          :inert="!isRedisDriverActive"
          @submit.prevent="saveSettings"
        >
          <Accordion
            type="multiple"
            class="w-full space-y-4"
            :default-value="['Connection', 'Session & Queue']"
          >
            <AccordionItem
              v-for="group in groupedSettings"
              :key="group.name"
              :value="group.name"
              class="border border-border rounded-lg bg-card px-2"
            >
              <AccordionTrigger class="px-4 hover:no-underline">
                <div class="flex items-center gap-3 text-left">
                  <div class="p-2 rounded-lg bg-primary/10 text-primary">
                    <component
                      :is="getGroupIcon(group.name)"
                      class="w-5 h-5"
                    />
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-foreground">
                      {{ group.name }}
                    </h3>
                    <p class="text-sm text-muted-foreground font-normal">
                      {{ getGroupDescription(group.name) }}
                    </p>
                  </div>
                </div>
              </AccordionTrigger>
                
              <AccordionContent class="px-4 pb-4 pt-2 border-t border-border/50 mt-2">
                <!-- Connection Group Extras -->
                <div
                  v-if="group.name === 'Connection'"
                  class="mb-6 space-y-4"
                >
                  <!-- Connection Actions -->
                  <div class="flex items-center justify-between bg-muted/30 p-4 rounded-lg border border-border/50">
                    <div class="text-sm text-muted-foreground">
                      {{ $t('features.redis.settings.testDescription', 'Test your Redis connection settings.') }}
                    </div>
                    <Button 
                      size="sm"
                      type="button"
                      :disabled="testing" 
                      @click="testConnection"
                    >
                      <Loader2
                        v-if="testing"
                        class="w-4 h-4 mr-2"
                      />
                      <Zap
                        v-else
                        class="w-4 h-4 mr-2"
                      />
                      {{ testing ? $t('features.redis.settings.testing') : $t('features.redis.settings.test') }}
                    </Button>
                  </div>

                  <!-- Connection Status -->
                  <div
                    v-if="connectionStatus"
                    :class="cn('rounded-lg p-4 text-sm border border-border/50 flex items-center gap-3', connectionStatus.type === 'success' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600' : 'bg-destructive/10 border-destructive/20 text-destructive')"
                  >
                    <div :class="cn('p-1 rounded-full', connectionStatus.type === 'success' ? 'bg-emerald-500/20' : 'bg-destructive/20')">
                      <Zap
                        v-if="connectionStatus.type === 'success'"
                        class="w-4 h-4"
                      />
                      <AlertTriangle
                        v-else
                        class="w-4 h-4"
                      />
                    </div>
                    <span>{{ connectionStatus.message }}</span>
                  </div>
                </div>

                <!-- Settings Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                  <div
                    v-for="setting in group.items"
                    :key="setting.key"
                    class="space-y-2"
                  >
                    <Label
                      :for="setting.key"
                      class="text-sm font-semibold text-foreground"
                    >
                      {{ formatLabel(setting.key) }}
                    </Label>
                    <p
                      v-if="setting.description"
                      class="text-xs text-muted-foreground"
                    >
                      {{ setting.description }}
                    </p>

                    <div class="relative">
                      <Input
                        v-if="setting.type === 'string' || setting.type === 'integer'"
                        :id="setting.key"
                        v-model="(settingsForm[setting.key] as any)"
                        :type="setting.type === 'integer' ? 'number' : (setting.is_encrypted ? (showPassword ? 'text' : 'password') : 'text')"
                        :class="[
                          { 'border-destructive focus-visible:ring-destructive': errors[setting.key] },
                          setting.is_encrypted ? 'pr-10' : ''
                        ]"
                      />
                      <button
                        v-if="setting.is_encrypted"
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground focus:outline-none"
                        @click="showPassword = !showPassword"
                      >
                        <Eye v-if="!showPassword" class="w-4 h-4" />
                        <EyeOff v-else class="w-4 h-4" />
                      </button>
                    </div>
                    <p
                      v-if="errors && errors[setting.key]"
                      class="text-sm text-destructive mt-1"
                    >
                      {{ Array.isArray(errors[setting.key]) ? (errors[setting.key] as string[])[0] : errors[setting.key] }}
                    </p>

                    <Select
                      v-else-if="setting.type === 'boolean'"
                      :model-value="['true', '1', true, 1].includes(settingsForm[setting.key] as any) ? 'true' : 'false'"
                      @update:model-value="(val) => settingsForm[setting.key] = val === 'true'"
                    >
                      <SelectTrigger class="w-full">
                        <SelectValue :placeholder="$t('features.redis.settings.select')" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="true">
                          {{ $t('features.redis.settings.enabled') }}
                        </SelectItem>
                        <SelectItem value="false">
                          {{ $t('features.redis.settings.disabled') }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
              </AccordionContent>
            </AccordionItem>
          </Accordion>

          <!-- Action Buttons / Footer -->
          <div class="mt-6 flex justify-end items-center gap-3">
            <Button
              variant="ghost"
              type="button"
              class="text-muted-foreground hover:text-foreground"
              @click="loadSettings"
            >
              {{ $t('features.redis.settings.cancel') }}
            </Button>
            <Button 
              type="submit" 
              :disabled="saving || !isDirty"
              class="min-w-[140px]"
            >
              <Loader2
                v-if="saving"
                class="w-4 h-4 mr-2"
              />
              <Save
                v-else
                class="w-4 h-4 mr-2"
              />
              {{ saving ? $t('features.redis.settings.saving') : $t('features.redis.settings.save') }}
            </Button>
          </div>
        </form>
      </TabsContent>

      <!-- Cache Tab -->
      <TabsContent
        value="cache"
        class="px-6 space-y-6"
      >
        <!-- 1. Cache Stats Cards (Top) -->
        <div
          v-if="cacheStats"
          class="grid grid-cols-1 md:grid-cols-3 gap-4"
        >
          <Card class="bg-muted/20 border-none shadow-none">
            <CardContent class="p-6 flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground">
                  {{ $t('features.redis.cache.stats.keys') }}
                </p>
                <h4 class="text-2xl font-bold mt-2">
                  {{ formatNumber(cacheStats.total_keys) }}
                </h4>
              </div>
              <div class="p-3 bg-primary/10 text-primary rounded-lg">
                <Database class="w-6 h-6" />
              </div>
            </CardContent>
          </Card>

          <Card class="bg-muted/20 border-none shadow-none">
            <CardContent class="p-6 flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground">
                  {{ $t('features.redis.cache.stats.size') }}
                </p>
                <h4 class="text-2xl font-bold mt-2 text-blue-600">
                  {{ cacheStats.cache_size || '-' }}
                </h4>
              </div>
              <div class="p-3 bg-blue-500/10 text-blue-500 rounded-lg">
                <HardDrive class="w-6 h-6" />
              </div>
            </CardContent>
          </Card>

          <Card class="bg-muted/20 border-none shadow-none">
            <CardContent class="p-6 flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground">
                  {{ $t('features.redis.cache.stats.expired') }}
                </p>
                <h4 class="text-2xl font-bold mt-2">
                  {{ formatNumber(cacheStats.expired_keys) }}
                </h4>
              </div>
              <div class="p-3 bg-orange-500/10 text-orange-500 rounded-lg">
                <Clock class="w-6 h-6" />
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- 2. Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Top Keys Table (Wide - Left) -->
          <Card class="lg:col-span-2 flex flex-col h-full ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            <CardHeader class="px-6 py-4 border-b border-border/50 bg-muted/20">
              <CardTitle class="text-lg flex items-center gap-2">
                <BarChart3 class="w-5 h-5 text-primary" />
                {{ $t('features.redis.cache.stats.topKeys') }}
              </CardTitle>
            </CardHeader>
            <CardContent class="p-0 flex-1">
              <div
                v-if="cacheStats && cacheStats.top_keys?.length"
                class="overflow-x-auto"
              >
                <Table>
                  <TableHeader>
                    <TableRow class="bg-muted/50 hover:bg-muted/50">
                      <TableHead class="w-full">
                        {{ $t('features.redis.cache.stats.table.key') }}
                      </TableHead>
                      <TableHead class="whitespace-nowrap">
                        {{ $t('features.redis.cache.stats.table.size') }}
                      </TableHead>
                      <TableHead class="whitespace-nowrap text-right">
                        {{ $t('features.redis.cache.stats.table.ttl') }}
                      </TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow
                      v-for="(key, index) in cacheStats.top_keys"
                      :key="index"
                      class="hover:bg-muted/50"
                    >
                      <TableCell class="font-mono text-xs break-all py-3">
                        {{ key.key }}
                      </TableCell>
                      <TableCell class="text-sm font-medium text-blue-600 whitespace-nowrap py-3">
                        {{ key.size }}
                      </TableCell>
                      <TableCell class="text-sm text-muted-foreground whitespace-nowrap text-right py-3">
                        {{ key.ttl }}
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
              <div
                v-else
                class="p-8 text-center text-muted-foreground flex flex-col items-center gap-3"
              >
                <Database class="w-10 h-10 opacity-20" />
                <p>{{ $t('common.placeholders.noData') }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- Quick Actions (Narrow - Right) -->
          <Card class="h-full flex flex-col">
            <CardHeader class="px-6 py-4 border-b border-border/50 bg-muted/20">
              <CardTitle class="text-lg flex items-center gap-2">
                <Zap class="w-5 h-5 text-amber-500" />
                {{ $t('common.actions.title') }}
              </CardTitle>
            </CardHeader>
            <CardContent class="p-6">
              <div class="grid grid-cols-2 gap-3">
                <!-- Warm Cache -->
                <button
                  :disabled="warming"
                  class="flex flex-col items-center justify-center p-3 rounded-lg border border-border hover:border-primary/50 hover:bg-primary/5 group relative overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed"
                  @click="warmCache"
                >
                  <div class="w-10 h-10 rounded-full flex items-center justify-center bg-primary/10 text-primary group-hover:scale-110">
                    <Loader2
                      v-if="warming"
                      class="w-5 h-5"
                    />
                    <Flame
                      v-else
                      class="w-5 h-5"
                    />
                  </div>
                  <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
                    {{ $t('features.redis.cache.actions.warm.button') }}
                  </span>
                </button>

                <!-- Flush All -->
                <button
                  :disabled="flushing"
                  class="flex flex-col items-center justify-center p-3 rounded-lg border border-border hover:border-destructive/50 hover:bg-destructive/5 group disabled:opacity-50 disabled:cursor-not-allowed"
                  @click="flushCache('all')"
                >
                  <div class="w-10 h-10 rounded-full flex items-center justify-center bg-destructive/10 text-destructive group-hover:scale-110">
                    <Trash2 class="w-5 h-5" />
                  </div>
                  <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
                    {{ $t('features.redis.cache.actions.all.button') }}
                  </span>
                </button>

                <!-- Flush App Cache -->
                <button
                  :disabled="flushing"
                  class="flex flex-col items-center justify-center p-3 rounded-lg border border-border hover:border-orange-500/50 hover:bg-orange-500/5 group disabled:opacity-50 disabled:cursor-not-allowed"
                  @click="flushCache('cache')"
                >
                  <div class="w-10 h-10 rounded-full flex items-center justify-center bg-orange-500/10 text-orange-500 group-hover:scale-110">
                    <HardDrive class="w-5 h-5" />
                  </div>
                  <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
                    {{ $t('features.redis.cache.actions.cache.button') }}
                  </span>
                </button>

                <!-- Flush Config -->
                <button
                  :disabled="flushing"
                  class="flex flex-col items-center justify-center p-3 rounded-lg border border-border hover:border-indigo-500/50 hover:bg-indigo-500/5 group disabled:opacity-50 disabled:cursor-not-allowed"
                  @click="flushCache('config')"
                >
                  <div class="w-10 h-10 rounded-full flex items-center justify-center bg-indigo-500/10 text-indigo-500 group-hover:scale-110">
                    <Settings class="w-5 h-5" />
                  </div>
                  <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
                    {{ $t('features.redis.cache.actions.config.button') }}
                  </span>
                </button>

                <!-- Flush Route -->
                <button
                  :disabled="flushing"
                  class="flex flex-col items-center justify-center p-3 rounded-lg border border-border hover:border-emerald-500/50 hover:bg-emerald-500/5 group disabled:opacity-50 disabled:cursor-not-allowed"
                  @click="flushCache('route')"
                >
                  <div class="w-10 h-10 rounded-full flex items-center justify-center bg-emerald-500/10 text-emerald-500 group-hover:scale-110">
                    <Route class="w-5 h-5" />
                  </div>
                  <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
                    {{ $t('features.redis.cache.actions.route.button') }}
                  </span>
                </button>

                <!-- Flush View -->
                <button
                  :disabled="flushing"
                  class="flex flex-col items-center justify-center p-3 rounded-lg border border-border hover:border-blue-500/50 hover:bg-blue-500/5 group disabled:opacity-50 disabled:cursor-not-allowed"
                  @click="flushCache('view')"
                >
                  <div class="w-10 h-10 rounded-full flex items-center justify-center bg-blue-500/10 text-blue-500 group-hover:scale-110">
                    <Eye class="w-5 h-5" />
                  </div>
                  <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
                    {{ $t('features.redis.cache.actions.view.button') }}
                  </span>
                </button>
              </div>
            </CardContent>
          </Card>
        </div>
      </TabsContent>
    </Tabs>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed, onMounted, onUnmounted, type Component } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/modules/System/stores/auth'

import axios from 'axios'
import api from '@/engine/api/client'
import { useToast } from '@/shared/composables/useToast'
import { useConfirm } from '@/shared/composables/useConfirm'
import { cn } from '@/shared/utils/lib-utils'
import {
    Tabs,
    TabsList,
    TabsTrigger,
    TabsContent,
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    Button,
    Input,
    Label,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    Table,
    TableHeader,
    TableBody,
    TableHead,
    TableRow,
    TableCell
} from '@/shared/components/ui';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import Database from 'lucide-vue-next/dist/esm/icons/database.js';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import Save from 'lucide-vue-next/dist/esm/icons/save.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import HardDrive from 'lucide-vue-next/dist/esm/icons/hard-drive.js';
import Users from 'lucide-vue-next/dist/esm/icons/users.js';
import Target from 'lucide-vue-next/dist/esm/icons/target.js';
import BarChart3 from 'lucide-vue-next/dist/esm/icons/chart-bar-stacked.js';
import Flame from 'lucide-vue-next/dist/esm/icons/flame.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import Route from 'lucide-vue-next/dist/esm/icons/route.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import EyeOff from 'lucide-vue-next/dist/esm/icons/eye-off.js';
import ArrowRight from 'lucide-vue-next/dist/esm/icons/arrow-right.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import AlertTriangle from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import { SECURITY_ROUTES } from '@/config/security';

interface RedisStat {
  version: string;
  used_memory: string;
  total_keys: number;
  uptime_days: string;
  connected_clients: number;
  hit_rate: string;
  operations_per_sec: number;
  total_commands: number;
  hits: number;
  misses: number;
}

interface RedisStatsSnapshot {
  hits: number;
  misses: number;
  total_commands: number;
  captured_at: string;
}

interface CacheKey {
  key: string;
  size: string;
  ttl: string;
}

interface CacheStats {
  total_keys: number;
  cache_size: string;
  expired_keys: number;
  top_keys: CacheKey[];
}

interface SettingItem {
  key: string;
  value: unknown;
  type: string;
  description?: string;
  is_encrypted?: boolean;
}

interface SettingGroup {
  name: string;
  items: SettingItem[];
}

interface ConnectionStatus {
  type: 'success' | 'error';
  message: string;
}

type GroupedSettingsPayload = Record<string, SettingItem[] | Record<string, SettingItem> | null | undefined>

const isSettingItem = (item: unknown): item is SettingItem => {
  return !!item && typeof item === 'object' && typeof (item as SettingItem).key === 'string'
}

const { t } = useI18n()
const authStore = useAuthStore()

const { confirm } = useConfirm()
const toast = useToast()
const activeTab = ref('statistics')
const showPassword = ref(false)

// Settings
const settings = ref<Record<string, SettingItem[]>>({})
const settingsForm = ref<Record<string, unknown>>({});
const initialSettingsForm = ref<Record<string, unknown>>({}); // Track initial state
const errors = ref<Record<string, string | string[]>>({})
const cacheDriver = ref<string | null>(null) // Global cache driver status
const isRedisDriverActive = computed(() => {
    const driver = (cacheDriver.value || '').toLowerCase();
    if (!driver) {
      // Do not hard-block the form when status endpoint is unavailable.
      return true;
    }

    return driver === 'redis'
        || driver === 'redis_failover'
        || driver === 'failover'
        || driver.startsWith('redis');
});

const isDirty = computed(() => {
    return JSON.stringify(settingsForm.value) !== JSON.stringify(initialSettingsForm.value);
});

const groupedSettings = computed<SettingGroup[]>(() => {
  const groups: Record<string, SettingGroup> = {}

  Object.entries(settings.value || {}).forEach(([groupName, rawItems]) => {
    const items: SettingItem[] = (Array.isArray(rawItems)
      ? rawItems
      : (rawItems && typeof rawItems === 'object' ? Object.values(rawItems) : [])
    ).filter(isSettingItem)
    if (items.length === 0) return

    // Filter out only redis_enabled (which was duplicative).
    const filteredItems: SettingItem[] = items.filter((item) => item.key !== 'redis_enabled')
     if (filteredItems.length === 0) return

    // Merge session and queue into one group
    if (groupName === 'session' || groupName === 'queue') {
      if (!groups['session_queue']) {
        groups['session_queue'] = {
          name: 'Session & Queue',
          items: []
        }
      }
      groups['session_queue'].items.push(...filteredItems)
    } else {
      groups[groupName] = {
        name: formatGroupName(groupName),
        items: filteredItems
      }
    }
  })
  
  return Object.values(groups)
})

const saving = ref(false)
const testing = ref(false)
const connectionStatus = ref<ConnectionStatus | null>(null)

// Statistics
const EMPTY_STATS: RedisStat = {
  version: '-',
  used_memory: '-',
  total_keys: 0,
  uptime_days: '-',
  connected_clients: 0,
  hit_rate: '0%',
  operations_per_sec: 0,
  total_commands: 0,
  hits: 0,
  misses: 0,
}

const REDIS_STATS_SNAPSHOT_KEY = 'redis:stats:snapshot:v1'

const EMPTY_CACHE_STATS: CacheStats = {
  total_keys: 0,
  cache_size: '-',
  expired_keys: 0,
  top_keys: [],
}

const stats = ref<RedisStat>({ ...EMPTY_STATS })
const statsSnapshot = ref<RedisStatsSnapshot | null>(null)
const loadingStats = ref(false)
const statsInterval = ref<ReturnType<typeof setInterval> | null>(null)

// Cache
const cacheStats = ref<CacheStats>({ ...EMPTY_CACHE_STATS })
const flushing = ref(false)
const warming = ref(false)

// Methods
const loadSettings = async () : Promise<void> => {
  try {
    const response = await api.get('/manage/redis/settings')
    const payload = response?.data
    const normalized: Record<string, SettingItem[]> = {}

    if (payload && typeof payload === 'object') {
      Object.entries(payload as GroupedSettingsPayload).forEach(([groupName, rawItems]) => {
        const items = Array.isArray(rawItems)
          ? rawItems
          : (rawItems && typeof rawItems === 'object' ? Object.values(rawItems) : [])

        normalized[groupName] = items.filter((item): item is SettingItem => {
          return !!item && typeof item === 'object' && typeof item.key === 'string'
        })
      })
    }

    settings.value = normalized

    // Reset form snapshot before rebuilding to avoid stale keys.
    settingsForm.value = {}
    // Flatten settings for form
    Object.values(settings.value).forEach((items) => {
      items.forEach(item => {
        settingsForm.value[item.key] = item.value
      })
    })
    initialSettingsForm.value = JSON.parse(JSON.stringify(settingsForm.value));
  } catch (error: unknown) {
    logger.error('Failed to load Redis settings:', error)
  }
}

const saveSettings = async () : Promise<void> => {
  saving.value = true
  errors.value = {}
  try {
    const settingsArray = Object.entries(settingsForm.value).map(([key, value]) => ({
      key,
      value
    }))

    await api.put('/manage/redis/settings', {
      settings: settingsArray
    })

    toast.success.save();

    connectionStatus.value = {
      type: 'success',
      message: t('features.redis.messages.saveSuccess')
    }

    setTimeout(() => {
      connectionStatus.value = null
    }, 3000)
    } catch (error: unknown) {
        if (axios.isAxiosError(error)) {
            if (error.response?.status === 422) {
                errors.value = (error.response.data as { errors?: Record<string, string | string[]> })?.errors || {}
            } else {
                toast.error.fromResponse(error)
                connectionStatus.value = {
                    type: 'error',
                    message: (error.response?.data as { message?: string })?.message || t('features.redis.messages.saveFailed')
                }
            }
        } else {
            logger.error('Failed to save settings:', error);
        }
    } finally {
    saving.value = false
  }
}

const testConnection = async () : Promise<void> => {
  testing.value = true
  connectionStatus.value = null

  try {
    // Send current form data to test connection on-the-fly
    const response = await api.post('/manage/redis/test-connection', {
      host: settingsForm.value.redis_host,
      port: settingsForm.value.redis_port,
      password: settingsForm.value.redis_password,
      database: settingsForm.value.redis_database
    })
    const payload = response?.data as { message?: string; response_time?: string } | undefined
    connectionStatus.value = {
      type: 'success',
      message: `✅ ${payload?.message || t('features.redis.messages.testSuccess')} (${payload?.response_time || '-'})`
    }
    // Auto clear after 3 seconds
    setTimeout(() => {
      connectionStatus.value = null;
    }, 3000);

  } catch (error: unknown) {
    let msg = t('features.redis.messages.testFailed');
    let hint = '';
    
    if (axios.isAxiosError(error)) {
        const data = error.response?.data as { message?: string; hint?: string } | undefined;
        msg = data?.message || msg;
        hint = data?.hint || '';
    }
    
    connectionStatus.value = {
      type: 'error',
      message: `❌ ${msg}${hint ? ' - ' + hint : ''}`
    }
  } finally {
    testing.value = false
  }
}

const loadStats = async () : Promise<void> => {
  loadingStats.value = true
  try {
    const response = await api.get('/manage/redis/info')
    const payload = response?.data as Partial<RedisStat> | undefined
    stats.value = { ...EMPTY_STATS, ...(payload || {}) }
  } catch (error: unknown) {
    logger.error('Failed to load Redis stats:', error)
    stats.value = { ...EMPTY_STATS }
  } finally {
    loadingStats.value = false
  }
}

const readStatsSnapshot = (): RedisStatsSnapshot | null => {
  const raw = localStorage.getItem(REDIS_STATS_SNAPSHOT_KEY)
  if (!raw) return null

  try {
    const parsed = JSON.parse(raw) as Partial<RedisStatsSnapshot>
    if (typeof parsed.hits !== 'number' || typeof parsed.misses !== 'number' || typeof parsed.total_commands !== 'number') {
      return null
    }

    return {
      hits: parsed.hits,
      misses: parsed.misses,
      total_commands: parsed.total_commands,
      captured_at: typeof parsed.captured_at === 'string' ? parsed.captured_at : new Date().toISOString(),
    }
  } catch {
    return null
  }
}

const persistStatsSnapshot = (snapshot: RedisStatsSnapshot): void => {
  statsSnapshot.value = snapshot
  localStorage.setItem(REDIS_STATS_SNAPSHOT_KEY, JSON.stringify(snapshot))
}

const captureStatsSnapshot = (): void => {
  persistStatsSnapshot({
    hits: stats.value.hits || 0,
    misses: stats.value.misses || 0,
    total_commands: stats.value.total_commands || 0,
    captured_at: new Date().toISOString(),
  })
}

const statsSinceLastClear = computed(() => {
  const snapshot = statsSnapshot.value
  if (!snapshot) {
    return {
      hits: 0,
      misses: 0,
      totalCommands: 0,
      hitRate: '0%',
    }
  }

  const hits = Math.max((stats.value.hits || 0) - snapshot.hits, 0)
  const misses = Math.max((stats.value.misses || 0) - snapshot.misses, 0)
  const totalCommands = Math.max((stats.value.total_commands || 0) - snapshot.total_commands, 0)
  const total = hits + misses
  const hitRate = total > 0 ? `${Math.round((hits / total) * 10000) / 100}%` : '0%'

  return {
    hits,
    misses,
    totalCommands,
    hitRate,
  }
})

const loadCacheStats = async () : Promise<void> => {
  try {
    const response = await api.get('/manage/redis/cache-stats')
    const payload = response?.data as Partial<CacheStats> | undefined
    cacheStats.value = { ...EMPTY_CACHE_STATS, ...(payload || {}) }
  } catch (error: unknown) {
    logger.error('Failed to load cache stats:', error)
    cacheStats.value = { ...EMPTY_CACHE_STATS }
  }
}

const resolveCacheDriver = (value: unknown): string | null => {
  if (typeof value !== 'string') return null
  const normalized = value.trim().toLowerCase()
  return normalized.length > 0 ? normalized : null
}

const getCacheDriverFromSettings = async (): Promise<string | null> => {
  try {
    const response = await api.get('/manage/settings')
    const rows = response?.data
    if (!Array.isArray(rows)) return null

    const row = rows.find((item: unknown) => {
      const obj = item as { key?: unknown; group?: unknown }
      return obj?.key === 'cache_driver' && obj?.group === 'performance'
    }) as { value?: unknown } | undefined

    return resolveCacheDriver(row?.value)
  } catch {
    return null
  }
}

const getCacheStatus = async () : Promise<void> => {
    try {
        const response = await api.get('/manage/system/cache-status')
        const data = response?.data
        const driver =
          resolveCacheDriver((data as { driver?: unknown } | undefined)?.driver)
          ?? await getCacheDriverFromSettings()
        cacheDriver.value = driver
    } catch (error: unknown) {
        logger.error('Failed to get global cache status:', error)
        if (!cacheDriver.value) {
          cacheDriver.value = await getCacheDriverFromSettings()
        }
    }
}

const flushCache = async (type: string) : Promise<void> => {
  const isDestructive = type === 'all'
  
  const confirmed = await confirm({
    title: t('features.redis.messages.flushTitle', 'Flush Cache'),
    message: isDestructive 
      ? t('features.redis.messages.flushConfirmLogout', 'Warning: Clearing all cache will also clear user sessions. You will be logged out immediately. Are you sure you want to proceed?')
      : t('features.redis.messages.flushConfirm', { type }),
    variant: 'danger',
    confirmText: t('features.redis.messages.flushAction', 'Flush'),
  })

  if (!confirmed) {
    return
  }

  // Stop polling if we are about to destroy session
  if (isDestructive && statsInterval.value) {
    clearInterval(statsInterval.value)
    statsInterval.value = null
  }

  flushing.value = true
  try {
    await api.post('/manage/redis/flush-cache', { type })
    toast.success.action(t('features.redis.messages.flushSuccess', { type }))
    
    if (isDestructive) {
      // Force logout handling
      authStore.clearAuth()
      window.location.href = `${SECURITY_ROUTES.login}?message=session_cleared`
      return
    }

    await Promise.all([loadCacheStats(), loadStats()])
    captureStatsSnapshot()
  } catch (error: unknown) {
    // If it's a 401, we know what happened
    if (axios.isAxiosError(error)) {
        if (error.response?.status === 401 && isDestructive) {
            authStore.clearAuth()
            window.location.href = `${SECURITY_ROUTES.login}?message=session_cleared`
            return
        }
    }
    toast.error.fromResponse(error)
  } finally {
    flushing.value = false
  }
}

const warmCache = async () : Promise<void> => {
  const confirmed = await confirm({
    title: t('features.redis.messages.warmTitle', 'Warm Cache'),
    message: t('features.redis.messages.warmConfirmLogout', 'Warning: Warming the cache requires clearing it first, which will clear user sessions. You will be logged out immediately. Are you sure you want to proceed?'),
    variant: 'danger',
    confirmText: t('features.redis.messages.warmAction', 'Warm Up'),
  })

  if (!confirmed) {
    return
  }
  
  // Stop polling
  if (statsInterval.value) {
    clearInterval(statsInterval.value)
    statsInterval.value = null
  }

  warming.value = true
  try {
    await api.post('/manage/redis/warm-cache')
    toast.success.action(t('features.redis.messages.warmSuccess'))
    
    // Force logout handling - warm cache typically clears everything first
    authStore.clearAuth()
    window.location.href = `${SECURITY_ROUTES.login}?message=session_cleared`
    
  } catch (error: unknown) {
     if (axios.isAxiosError(error)) {
         if (error.response?.status === 401) {
             authStore.clearAuth()
             window.location.href = `${SECURITY_ROUTES.login}?message=session_cleared`
             return
         }
     }
    toast.error.fromResponse(error)
  } finally {
    warming.value = false
  }
}

// Helpers
const formatLabel = (key: string) => {
  return key.split('_').map(word => 
    word.charAt(0).toUpperCase() + word.slice(1)
  ).join(' ')
}

const formatGroupName = (group: string) => {
  return group.charAt(0).toUpperCase() + group.slice(1)
}

const getGroupIcon = (groupName: string): Component => {
  const icons: Record<string, Component> = {
    'Connection': Database,
    'Cache': Zap,
    'Session & Queue': Clock
  }
  return (icons[groupName] || Database) as Component
}

const getGroupDescription = (groupName: string) => {
  const descriptions: Record<string, string> = {
    'Connection': 'Configure Redis server connection details',
    'Cache': 'Manage application cache settings',
    'Session & Queue': 'Configure session and queue driver settings'
  }
  return descriptions[groupName] || ''
}

const formatNumber = (num?: number) => {
  if (!num) return 0
  return new Intl.NumberFormat().format(num)
}

// Lifecycle
onMounted(() => {
  statsSnapshot.value = readStatsSnapshot()
  loadSettings()
  loadStats()
  loadCacheStats()
  getCacheStatus()

  // Auto-refresh stats every 30 seconds
  statsInterval.value = setInterval(() => {
    if (activeTab.value === 'statistics') {
      loadStats()
    }
  }, 30000)
})

onUnmounted(() => {
  if (statsInterval.value) {
    clearInterval(statsInterval.value)
  }
})
</script>

<style scoped>
/* Custom transitions for cards */
.group:hover .group-hover\:scale-110 {
  transform: scale(1.1);
}
</style>
