<?php

namespace Modules\System\Registries;

class HookRegistry extends BaseRegistry
{
    /**
     * Register a hook listener.
     *
     * @param callable|string $callback
     */
    public function register(string $hookName, mixed $callback, int $priority = 10): void
    {
        if (!isset($this->items[$hookName])) {
            $this->items[$hookName] = [];
        }
        
        $this->items[$hookName][] = [
            'callback' => $callback,
            'priority' => $priority
        ];
        
        // Sort by priority
        usort($this->items[$hookName], fn(array $a, array $b): int => $a['priority'] <=> $b['priority']);
    }

    public function run(string $hookName, ...$params): array
    {
        $results = [];
        foreach ($this->get($hookName) ?? [] as $hook) {
            $results[] = app()->call($hook['callback'], $params);
        }
        return $results;
    }
}
