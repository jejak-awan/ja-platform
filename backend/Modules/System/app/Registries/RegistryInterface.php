<?php

namespace Modules\System\Registries;

interface RegistryInterface
{
    /**
     * Register an item.
     *
     * @param string $key
     * @param mixed $value
     */
    public function register(string $key, mixed $value): void;

    /**
     * Get an item by key.
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed;

    /**
     * Get all registered items.
     *
     * @return array
     */
    public function all(): array;
}
