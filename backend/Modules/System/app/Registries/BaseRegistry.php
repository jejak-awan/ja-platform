<?php

namespace Modules\System\Registries;

abstract class BaseRegistry implements RegistryInterface
{
    protected array $items = [];

    public function register(string $key, mixed $value): void
    {
        $this->items[$key] = $value;
    }

    public function get(string $key): mixed
    {
        return $this->items[$key] ?? null;
    }

    public function all(): array
    {
        return $this->items;
    }
}
