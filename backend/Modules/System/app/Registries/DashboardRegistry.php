<?php

declare(strict_types=1);

namespace Modules\System\Registries;

class DashboardRegistry extends BaseRegistry
{
    /**
     * Register a dashboard widget.
     *
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
