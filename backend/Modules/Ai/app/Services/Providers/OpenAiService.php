<?php

namespace Modules\Ai\Services\Providers;

use Modules\Ai\Contracts\AiProviderInterface;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiService implements AiProviderInterface
{
    protected ?string $apiKey;

    protected string $baseUrl = 'https://api.openai.com/v1';

    public function __construct(?string $apiKey = null)
    {
        /** @var mixed $val */
        $val = \Modules\System\Models\Setting::get('openai_api_key', '');
        $this->apiKey = $apiKey ?? (is_string($val) ? $val : '');
    }

    public function getName(): string
    {
        return 'OpenAI';
    }

    public function generateText(string $prompt, string $context = '', string $model = ''): string
    {
        if (empty($this->apiKey)) {
            throw new \Exception('OpenAI API Key is not configured.');
        }

        /** @var mixed $val */
        $val = \Modules\System\Models\Setting::get('openai_model', '');
        $model = $model ?: (is_string($val) && $val !== '' ? $val : 'gpt-4o-mini');

        try {
            $messages = [
                ['role' => 'system', 'content' => 'You are a helpful content editor assistant.'],
                ['role' => 'user', 'content' => $context ? "Context:\n$context\n\nInstruction: $prompt" : $prompt],
            ];

            $response = Http::withToken($this->apiKey)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => 0.7,
                ]);

            if ($response->failed()) {
                $this->handleError($response);
            }

            /** @var array{choices: array<int, array{message: array{content: string}}>} $data */
            $data = $response->json();

            return $data['choices'][0]['message']['content'] ?? '';

        } catch (\Exception $e) {
            Log::error('OpenAI Generation Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get available models
     *
     * @return array<int, array{id: string, name: string}>
     */
    public function getModels(): array
    {
        if (empty($this->apiKey)) {
            return [];
        }

        try {
            $response = Http::withToken($this->apiKey)->get("{$this->baseUrl}/models");

            if ($response->failed()) {
                return [];
            }

            /** @var array{data: array<int, array{id: string}>} $data */
            $data = $response->json();
            /** @var array<int, array{id: string, name: string}> $models */
            $models = [];

            foreach ($data['data'] as $m) {
                $models[] = [
                    'id' => strval($m['id']),
                    'name' => strval($m['id']),
                ];
            }

            // Sort models alphabetically
            usort($models, fn ($a, $b) => strcmp($a['id'], $b['id']));

            return $models;

        } catch (\Exception $e) {
            Log::error('OpenAI GetModels Exception', ['message' => $e->getMessage()]);

            return [];
        }
    }

    public function testConnection(): bool
    {
        if (empty($this->apiKey)) {
            throw new \Exception('API Key is missing.');
        }

        $response = Http::withToken($this->apiKey)->get("{$this->baseUrl}/models");

        if ($response->failed()) {
            $this->handleError($response);
        }

        return true;
    }

    /**
     * Handle API errors
     *
     * @param  \Illuminate\Http\Client\Response  $response
     *
     * @throws \Exception
     */
    protected function handleError($response): never
    {
        /** @var mixed $errorData */
        $errorData = $response->json('error.message', 'Unknown error');
        $errorMsg = is_string($errorData) ? $errorData : 'Unknown error';

        if ($response->status() === 401) {
            throw new \Exception('Invalid OpenAI API Key.');
        }

        if ($response->status() === 429) {
            throw new \Exception('OpenAI Rate Limit / Quota Exceeded.');
        }

        throw new \Exception('OpenAI API Error: '.$errorMsg);
    }
}
