<template>
  <Card class="border-border shadow-sm">
    <CardHeader class="pb-3">
      <div class="flex items-center justify-between">
        <CardTitle class="text-base">
          {{ selectedItem ? t('features.menus.form.editItemTitle') : t('features.menus.form.settings') }}
        </CardTitle>
        <div class="flex items-center gap-1">
          <Button 
            variant="ghost" 
            size="icon" 
            class="h-8 w-8"
            :title="t('common.actions.collapse')"
            @click="$emit('collapse')"
          >
            <PanelRightClose class="w-4 h-4" />
          </Button>
          <Button 
            v-if="selectedItem"
            variant="ghost" 
            size="icon" 
            class="h-8 w-8"
            :title="t('common.actions.deselect')"
            @click="menuContext.clearSelection()"
          >
            <X class="w-4 h-4" />
          </Button>
        </div>
      </div>
    </CardHeader>
    <CardContent>
      <!-- No Selection -->
      <div
        v-if="!selectedItem"
        class="flex flex-col items-center justify-center py-8 text-center"
      >
        <MousePointer class="w-8 h-8 text-muted-foreground mb-3" />
        <p class="text-sm text-muted-foreground">
          {{ t('features.menus.messages.selectItemToEdit') }}
        </p>
      </div>

      <!-- Properties Form -->
      <div
        v-else
        class="space-y-4"
      >
        <!-- Type Badge -->
        <div class="flex items-center gap-2 pb-2 border-b border-border">
          <component
            :is="iconComponent"
            class="w-4 h-4"
            :class="iconColorClass"
          />
          <Badge variant="outline">
            {{ typeLabel }}
          </Badge>
        </div>

        <!-- Dynamic Fields -->
        <div
          v-for="setting in settingsSchema"
          :key="setting.key"
          class="space-y-1.5"
        >
          <!-- Skip grouped fields, render them in groups -->
          <template v-if="!setting.group">
            <ItemPropertyField 
              :setting="setting"
              :value="selectedItem[setting.key]"
              @update="updateField(setting.key, $event)"
            />
          </template>
        </div>
                
        <!-- Parent Selector -->
        <div class="space-y-1.5 pt-2 border-t border-border">
          <Label class="text-xs text-muted-foreground">{{ t('features.menus.form.parentItem') }}</Label>
          <Select
            :model-value="currentParentId"
            @update:model-value="handleParentChange"
          >
            <SelectTrigger class="h-8 text-sm">
              <SelectValue :placeholder="t('features.menus.form.placeholders.selectParent') || 'Select Parent'" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="root">
                {{ t('features.menus.form.rootItem') }}
              </SelectItem>
              <SelectItem 
                v-for="p in validParents" 
                :key="p.id || p._temp_id" 
                :value="(p.id || p._temp_id)!.toString()"
              >
                <span :style="{ paddingLeft: (p._depth * 12) + 'px' }">
                  {{ p.title || 'Untitled' }}
                </span>
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Grouped Fields -->
        <Accordion
          v-if="groupedSettings.length > 0"
          type="multiple"
          class="w-full"
        >
          <AccordionItem
            v-for="group in groupedSettings"
            :key="group.name"
            :value="group.name"
          >
            <AccordionTrigger class="py-2 text-sm">
              {{ formatGroupName(group.name) }}
            </AccordionTrigger>
            <AccordionContent class="pt-2 pb-4 space-y-4">
              <ItemPropertyField 
                v-for="setting in group.settings" 
                :key="setting.key"
                :setting="setting"
                :value="selectedItem[setting.key]"
                @update="updateField(setting.key, $event)"
              />
            </AccordionContent>
          </AccordionItem>
        </Accordion>

        <!-- Quick Actions -->
        <div class="flex gap-2 pt-4 border-t border-border">
          <Button 
            size="sm" 
            variant="outline" 
            class="flex-1"
            @click="handleDuplicate"
          >
            <Copy class="w-3.5 h-3.5 mr-2" />
            {{ t('common.actions.duplicate') }}
          </Button>
          <Button 
            size="sm" 
            variant="destructive" 
            class="flex-1"
            @click="handleDelete"
          >
            <Trash2 class="w-3.5 h-3.5 mr-2" />
            {{ t('common.actions.delete') }}
          </Button>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useMenuContext } from '@/composables/useMenu';
import { menuItemRegistry } from '../registry';
import type { MenuItem, MenuItemSetting, PropertyValue } from '@/types/cms/menu';

// UI Components
import {
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    Badge,
    Button,
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
    Label
} from '@/components/ui';
import ItemPropertyField from './ItemPropertyField.vue';

import X from 'lucide-vue-next/dist/esm/icons/x.js';
import MousePointer from 'lucide-vue-next/dist/esm/icons/mouse-pointer.js';
import Copy from 'lucide-vue-next/dist/esm/icons/copy.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import File from 'lucide-vue-next/dist/esm/icons/file.js';
import Tag from 'lucide-vue-next/dist/esm/icons/tag.js';
import LinkIcon from 'lucide-vue-next/dist/esm/icons/link.js';
import Columns from 'lucide-vue-next/dist/esm/icons/columns-2.js';
import PanelRightClose from 'lucide-vue-next/dist/esm/icons/panel-right-close.js';

defineEmits<{
    (e: 'collapse'): void;
}>();

const { t } = useI18n();
const menuContext = useMenuContext();

const selectedItem = computed(() => menuContext.selectedItem.value);

interface ParentOption extends MenuItem {
    _depth: number;
}

// Flatten items with depth for select options
const validParents = computed(() => {
    if (!selectedItem.value) return [];
    
    const currentId = selectedItem.value.id || selectedItem.value._temp_id;
    const result: ParentOption[] = [];
    
    // Recursive flatten
    const traverse = (nodes: MenuItem[], depth: number) => {
        nodes.forEach(node => {
            const nodeId = node.id || node._temp_id;
            
            // Exclude self and its subtree
            if (nodeId === currentId) {
                return; 
            }
            
            result.push({ ...node, _depth: depth });
            
            if (node.children && node.children.length > 0) {
                traverse(node.children, depth + 1);
            }
        });
    };
    
    traverse(menuContext.items.value, 0);
    return result;
});

const currentParentId = computed(() => {
    if (!selectedItem.value) return 'root';
    const parent = menuContext.findParent(selectedItem.value.id || selectedItem.value._temp_id!);
    return parent ? (parent.id || parent._temp_id!).toString() : 'root';
});

const handleParentChange = (val: string) => {
    if (!selectedItem.value) return;
    const itemId = selectedItem.value.id || selectedItem.value._temp_id!;
    const newParentId = val === 'root' ? null : val;
    menuContext.moveItem(itemId, newParentId);
};

const typeDefinition = computed(() => {
    if (!selectedItem.value) return null;
    return menuItemRegistry.get(selectedItem.value.type || '');
});

const typeLabel = computed(() => {
    if (!selectedItem.value) return 'Unknown';
    const key = `features.menus.form.types.${selectedItem.value.type}`;
    const translated = t(key);
    if (translated !== key) return translated;
    return typeDefinition.value?.label || selectedItem.value.type || 'Unknown';
});

const iconComponent = computed(() => {
    switch (selectedItem.value?.type) {
        case 'page': return FileText;
        case 'post': return File;
        case 'category': return Tag;
        case 'column_group': return Columns;
        default: return LinkIcon;
    }
});

const iconColorClass = computed(() => {
    const color = typeDefinition.value?.color || 'gray';
    return `text-${color}-500`;
});

const settingsSchema = computed(() => {
    return typeDefinition.value?.settings || [];
});

const groupedSettings = computed(() => {
    const groups: Record<string, MenuItemSetting[]> = {};
    settingsSchema.value.forEach((setting: MenuItemSetting) => {
        const groupName = setting.group;
        if (groupName) {
            if (!groups[groupName]) {
                groups[groupName] = [];
            }
            groups[groupName].push(setting);
        }
    });
    return Object.entries(groups).map(([name, settings]) => ({ name, settings }));
});

const formatGroupName = (name: string) => {
    const key = `features.menus.form.groups.${name}`;
    const translated = t(key);
    if (translated !== key) return translated;
    return name.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
};

const updateField = (key: string, value: PropertyValue) => {
    if (!selectedItem.value) return;
    const itemId = selectedItem.value.id || selectedItem.value._temp_id!;
    menuContext.updateItem(itemId, { [key]: value });
};

const handleDuplicate = () => {
    if (!selectedItem.value) return;
    const itemId = selectedItem.value.id || selectedItem.value._temp_id!;
    menuContext.duplicateItem(itemId);
};

const handleDelete = () => {
    if (!selectedItem.value) return;
    const itemId = selectedItem.value.id || selectedItem.value._temp_id!;
    menuContext.deleteItem(itemId);
};
</script>
