<?php

namespace Modules\System\Registries;

class DashboardRegistry extends BaseRegistry
{
    /**
     * Register a dashboard widget.
     * 
     * @param string $key
     * @param array{
     *   title: string,
     *   component: string,
     *   width: string,
     *   permissions?: string[],
     *   data_callback: callable|string
     * } $config
     */
    public function register(string $key, mixed $config): void
    {
        parent::register($key, $config);
    }
}
