<?php

namespace Modules\System\Registries;

use Modules\System\Contracts\LayoutRegistryInterface;

class LayoutRegistry implements LayoutRegistryInterface
{
    protected array $menuLocations = [];

    protected array $widgetLocations = [];

    protected array $widgetTypes = [];

    public function registerMenuLocations(string $module, array $locations): void
    {
        $this->menuLocations[$module] = array_merge($this->menuLocations[$module] ?? [], $locations);
    }

    public function getMenuLocations(string $module = 'cms'): array
    {
        return $this->menuLocations[$module] ?? [];
    }

    public function registerWidgetLocations(string $module, array $locations): void
    {
        $this->widgetLocations[$module] = array_merge($this->widgetLocations[$module] ?? [], $locations);
    }

    public function getWidgetLocations(string $module = 'cms'): array
    {
        return $this->widgetLocations[$module] ?? [];
    }

    public function registerWidgetTypes(string $module, array $types): void
    {
        $this->widgetTypes[$module] = array_merge($this->widgetTypes[$module] ?? [], $types);
    }

    public function getWidgetTypes(string $module = 'cms'): array
    {
        return $this->widgetTypes[$module] ?? [
            'html' => 'Custom HTML',
            'content_list' => 'Content List',
            'menu' => 'Navigation Menu',
            'form' => 'Custom Form',
        ];
    }
}
