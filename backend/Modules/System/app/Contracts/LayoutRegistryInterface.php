<?php

namespace Modules\System\Contracts;

interface LayoutRegistryInterface
{
    /**
     * Register menu locations for a module.
     */
    public function registerMenuLocations(string $module, array $locations): void;

    /**
     * Get allowed menu locations for a module/theme.
     */
    public function getMenuLocations(string $module = 'cms'): array;

    /**
     * Register widget locations for a module.
     */
    public function registerWidgetLocations(string $module, array $locations): void;

    /**
     * Get allowed widget locations.
     */
    public function getWidgetLocations(string $module = 'cms'): array;

    /**
     * Register widget types for a module.
     */
    public function registerWidgetTypes(string $module, array $types): void;

    /**
     * Get allowed widget types.
     */
    public function getWidgetTypes(string $module = 'cms'): array;
}
