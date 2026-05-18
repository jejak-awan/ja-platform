<?php

namespace Modules\System\Registries;

class HookRegistry extends BaseRegistry
{
    /**
     * Register a hook listener.
     *
     * @param  callable|string  $callback
     */
    public function register(string $hookName, mixed $callback, int $priority = 10): void
    {
        if (! isset($this->items[$hookName])) {
            $this->items[$hookName] = [];
        }

        $this->items[$hookName][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        // Sort by priority
        usort($this->items[$hookName], fn (array $a, array $b): int => $a['priority'] <=> $b['priority']);
    }

    /**
     * Alias for register.
     */
    public function listen(string $hookName, mixed $callback, int $priority = 10): void
    {
        $this->register($hookName, $callback, $priority);
    }

    /**
     * Run all registered listeners for a specific action hook (fire-and-forget).
     */
    public function action(string $actionName, ...$params): void
    {
        foreach ($this->get($actionName) ?? [] as $hook) {
            $this->invoke($hook['callback'], $params);
        }
    }

    /**
     * Run all registered listeners for a specific filter hook, passing the value sequentially.
     */
    public function filter(string $filterName, mixed $value, ...$params): mixed
    {
        foreach ($this->get($filterName) ?? [] as $hook) {
            $value = $this->invoke($hook['callback'], array_merge([$value], $params));
        }

        return $value;
    }

    public function run(string $hookName, ...$params): array
    {
        $results = [];
        foreach ($this->get($hookName) ?? [] as $hook) {
            $results[] = $this->invoke($hook['callback'], $params);
        }

        return $results;
    }

    /**
     * Safely invoke hook callback, utilizing standard callable execution or Laravel container DI resolution.
     */
    protected function invoke(mixed $callback, array $params): mixed
    {
        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }

        return app()->call($callback, $params);
    }
}
