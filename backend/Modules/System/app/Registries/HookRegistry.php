<?php

declare(strict_types=1);

namespace Modules\System\Registries;

class HookRegistry extends BaseRegistry
{
    /**
     * Register a hook listener.
     *
     * @param  (callable(): mixed)|string  $callback
     */
    public function register(string $hookName, mixed $callback, int $priority = 10): void
    {
        if (! isset($this->items[$hookName])) {
            $this->items[$hookName] = [];
        }

        if (is_array($this->items[$hookName])) {
            $this->items[$hookName][] = [
                'callback' => $callback,
                'priority' => $priority,
            ];

            // Sort by priority
            usort($this->items[$hookName], fn (array $a, array $b): int => ((int) $a['priority']) <=> ((int) $b['priority']));
        }
    }

    /**
     * Alias for register.
     *
     * @param  (callable(): mixed)|string  $callback
     */
    public function listen(string $hookName, mixed $callback, int $priority = 10): void
    {
        $this->register($hookName, $callback, $priority);
    }

    /**
     * Run all registered listeners for a specific action hook (fire-and-forget).
     *
     * @param  mixed  ...$params
     */
    public function action(string $actionName, ...$params): void
    {
        /** @var array<int, array{callback: (callable(): mixed)|string, priority: int}>|null $hooks */
        $hooks = $this->get($actionName);

        foreach ($hooks ?? [] as $hook) {
            $this->invoke($hook['callback'], array_values($params));
        }
    }

    /**
     * Run all registered listeners for a specific filter hook, passing the value sequentially.
     *
     * @param  mixed  $value
     * @param  mixed  ...$params
     * @return mixed
     */
    public function filter(string $filterName, mixed $value, ...$params): mixed
    {
        /** @var array<int, array{callback: (callable(): mixed)|string, priority: int}>|null $hooks */
        $hooks = $this->get($filterName);

        foreach ($hooks ?? [] as $hook) {
            $result = $this->invoke($hook['callback'], array_values(array_merge([$value], $params)));
            if (! ($result instanceof HookCrashToken)) {
                $value = $result;
            }
        }

        return $value;
    }

    /**
     * Run all registered listeners returning their output values.
     *
     * @param  mixed  ...$params
     * @return array<int, mixed>
     */
    public function run(string $hookName, ...$params): array
    {
        $results = [];
        /** @var array<int, array{callback: (callable(): mixed)|string, priority: int}>|null $hooks */
        $hooks = $this->get($hookName);

        foreach ($hooks ?? [] as $hook) {
            $result = $this->invoke($hook['callback'], array_values($params));
            if (! ($result instanceof HookCrashToken)) {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Safely invoke hook callback, utilizing standard callable execution or Laravel container DI resolution.
     *
     * @param  (callable(): mixed)|string  $callback
     * @param  array<int, mixed>  $params
     * @return mixed
     */
    protected function invoke(mixed $callback, array $params): mixed
    {
        try {
            if (is_callable($callback)) {
                return call_user_func_array($callback, $params);
            }

            if (is_string($callback)) {
                // Laravel's Container::call resolves parameter names dynamically,
                // but PHPStan wants array<string, mixed> if non-indexed.
                // We cast to keep PHPStan level 9 satisfied.
                /** @var array<string, mixed> $assocParams */
                $assocParams = $params;

                return app()->call($callback, $assocParams);
            }

            return null;
        } catch (\Throwable $e) {
            // IPC Error Isolation: Log the crash and isolate it to prevent system-wide segmentation fault
            \Illuminate\Support\Facades\Log::error(sprintf(
                "Kernel IPC Error [Segmentation Fault]: Hook callback crashed. Message: %s. File: %s:%d. Context: %s",
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                is_string($callback) ? $callback : (is_array($callback) ? (string) json_encode($callback) : 'Closure')
            ));

            return new HookCrashToken($e);
        }
    }
}
