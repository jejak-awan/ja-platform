<?php

namespace Modules\Core\Services\Ai;

interface AiProviderInterface
{
    /**
     * Generate text from a prompt.
     */
    public function generateText(string $prompt, string $context = '', string $model = ''): string;

    /**
     * Get a list of available models from the provider.
     *
     * @return array<int, array{id: string, name: string}>
     */
    public function getModels(): array;

    /**
     * Test the connection to the provider.
     * Returns true on success, throws exception on failure.
     */
    public function testConnection(): bool;

    /**
     * Get the descriptive name of the provider.
     */
    public function getName(): string;
}
