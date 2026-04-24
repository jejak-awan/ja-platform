<template>
  <div class="space-y-4">
    <SettingGroup
      v-for="group in generalSettingsGrouped"
      :key="group.id"
      :title="group.title"
      :description="group.description"
      :icon="(group.icon as any)"
      :color="group.color"
      :default-expanded="group.defaultExpanded"
    >
      <template
        v-for="setting in group.settings"
        :key="setting.id"
      >
        <SettingField
          v-if="setting.key !== 'content.autosave_interval_seconds'"
          v-show="isMaintenanceSettingVisible(setting.key)"
          :model-value="(formData[setting.key] as any)"
          :field-key="setting.key"
          :label="$t('features.settings.labels.' + setting.key)"
          :description="$t('features.settings.descriptions.' + setting.key)"
          :type="setting.type"
          :enabled-text="$t('features.settings.enabled')"
          :disabled-text="$t('features.settings.disabled')"
          :error="errors?.[setting.key]"
          @update:model-value="(value) => updateField(setting.key, value)"
        />

        <div
          v-else
          class="space-y-2"
        >
          <label class="block text-sm font-medium text-foreground">
            {{ $t('features.settings.labels.content.autosave_interval_seconds') }}
          </label>
          <p class="text-xs text-muted-foreground">
            {{ $t('features.settings.descriptions.content.autosave_interval_seconds') }}
          </p>

          <Select
            :model-value="autosavePresetValue"
            @update:model-value="handleAutosavePresetChange"
          >
            <SelectTrigger>
              <SelectValue :placeholder="$t('common.actions.select')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="15">
                15 detik (aktif)
              </SelectItem>
              <SelectItem value="30">
                30 detik (seimbang)
              </SelectItem>
              <SelectItem value="60">
                60 detik (ringan server)
              </SelectItem>
              <SelectItem value="custom">
                Custom
              </SelectItem>
            </SelectContent>
          </Select>

          <Input
            v-if="autosavePresetValue === 'custom'"
            :model-value="String(autosaveIntervalSeconds)"
            type="number"
            min="5"
            max="300"
            step="1"
            placeholder="5-300"
            @input="handleAutosaveCustomInput"
          />
        </div>
      </template>
    </SettingGroup>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import SettingGroup from '@/modules/Core/components/settings/SettingGroup.vue'
import SettingField from '@/modules/Core/components/settings/SettingField.vue'
import { Input, Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui'

interface Setting {
    id: number | string;
    key: string;
    value: unknown;
    type: string;
    group: string;
}

interface Props {
    settings: Setting[];
    formData: Record<string, unknown>;
    errors?: Record<string, string[]>;
}

const { t } = useI18n()

const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'update:formData', value: Record<string, unknown>): void;
}>()

const updateField = (key: string, value: unknown) => {
    emit('update:formData', { ...props.formData, [key]: value })
}

const normalizeAutosaveInterval = (value: unknown): number => {
    const parsed = Number(value)
    if (!Number.isFinite(parsed)) return 30
    return Math.min(300, Math.max(5, Math.round(parsed)))
}

const autosaveIntervalSeconds = computed(() => normalizeAutosaveInterval(props.formData['content.autosave_interval_seconds']))

const autosavePresetValue = computed(() => {
    const v = autosaveIntervalSeconds.value
    return [15, 30, 60].includes(v) ? String(v) : 'custom'
})

const handleAutosavePresetChange = (value: string) => {
    if (value === 'custom') {
        return
    }
    updateField('content.autosave_interval_seconds', Number(value))
}

const handleAutosaveCustomInput = (event: Event) => {
    const value = Number((event.target as HTMLInputElement).value)
    updateField('content.autosave_interval_seconds', normalizeAutosaveInterval(value))
}

// SVG Icon Components
const GlobeIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 2.25c-2.998 0-5.74 1.1-7.843 2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" /></svg>`
}



interface SettingGroupData {
    id: string;
    title: string;
    description: string;
    icon: unknown;
    color: 'primary' | 'blue' | 'emerald' | 'amber' | 'red' | 'purple' | 'indigo' | 'orange' | 'pink';
    keys: string[];
    settings: Setting[];
    defaultExpanded: boolean;
}

const isMaintenanceSettingVisible = (key: string) => {
    // Basic switches for master maintenance mode
    const maintenanceSubSettings = ['maintenance_title', 'maintenance_message', 'maintenance_countdown_enabled', 'maintenance_end_time'];
    
    if (maintenanceSubSettings.includes(key)) {
        if (!props.formData.maintenance_mode) return false;
        
        // Additional check for countdown end time
        if (key === 'maintenance_end_time' && !props.formData.maintenance_countdown_enabled) {
            return false;
        }
    }
    
    return true;
}

const generalSettingsGrouped = computed(() => {
    const generalSettings = props.settings.filter(s => s && s.group === 'general')
    
    const groups: SettingGroupData[] = [
        {
            id: 'site',
            title: t('features.settings.groups.siteInfo.title'),
            description: t('features.settings.groups.siteInfo.description'),
            icon: GlobeIcon,
            color: 'blue',
            keys: ['site_name', 'site_logo', 'site_favicon', 'site_description', 'site_url', 'admin_email'],
            settings: [],
            defaultExpanded: true,
        },
        {
            id: 'editor',
            title: t('features.settings.groups.editor.title'),
            description: t('features.settings.groups.editor.description'),
            icon: GlobeIcon,
            color: 'indigo',
            keys: ['content.autosave_enabled', 'content.autosave_interval_seconds'],
            settings: [],
            defaultExpanded: false,
        },
    ]

    groups.forEach(group => {
        group.settings = generalSettings.filter(s => group.keys.includes(s.key))
        
        // Ensure settings are in logical order
        if (group.id === 'site') {
            const order = ['site_name', 'site_logo', 'site_favicon', 'site_description', 'site_url', 'admin_email'];
            group.settings.sort((a, b) => order.indexOf(a.key) - order.indexOf(b.key));
        }
        if (group.id === 'editor') {
            const order = ['content.autosave_enabled', 'content.autosave_interval_seconds'];
            group.settings.sort((a, b) => order.indexOf(a.key) - order.indexOf(b.key));
        }
    })

    return groups.filter(group => group.settings.length > 0)
})
</script>
