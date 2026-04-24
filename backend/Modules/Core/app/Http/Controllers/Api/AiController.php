<?php

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\Request;

class AiController extends BaseApiController
{
    /**
     * Get list of available AI providers
     */
    public function getProviders(): \Illuminate\Http\JsonResponse
    {
        return $this->success(\Modules\Core\Services\Ai\AiProviderFactory::getProviders());
    }

    /**
     * Get available models for a provider
     */
    public function getModels(Request $request, string $provider): \Illuminate\Http\JsonResponse
    {
        try {
            // Instantiate service manually with the provided key (for setup) or null to use saved key
            $apiKeyRaw = $request->input('api_key');
            $apiKey = is_string($apiKeyRaw) ? $apiKeyRaw : null;

            // Factory doesn't accept key in 'make', so we instantiate manually based on provider
            $service = match ($provider) {
                'openai' => new \Modules\Core\Services\Ai\OpenAiService($apiKey),
                'deepseek' => new \Modules\Core\Services\Ai\DeepSeekService($apiKey),
                'gemini' => new \Modules\Core\Services\Ai\GeminiService($apiKey),
                default => throw new \Exception('Unknown provider'),
            };

            $models = $service->getModels();

            return $this->success($models);
        } catch (\Exception $e) {
            return $this->error('Failed to fetch models: '.$e->getMessage(), 500);
        }
    }

    /**
     * Test connection to a provider
     */
    public function testConnection(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'provider' => 'required|string',
            'api_key' => 'required|string',
        ]);

        try {
            $providerRaw = $request->input('provider');
            $provider = is_string($providerRaw) ? $providerRaw : '';
            $apiKeyRaw = $request->input('api_key');
            $apiKey = is_string($apiKeyRaw) ? $apiKeyRaw : null;

            $service = match ($provider) {
                'openai' => new \Modules\Core\Services\Ai\OpenAiService($apiKey),
                'deepseek' => new \Modules\Core\Services\Ai\DeepSeekService($apiKey),
                'gemini' => new \Modules\Core\Services\Ai\GeminiService($apiKey),
                default => throw new \Exception('Unknown provider'),
            };

            $service->testConnection();

            return $this->success(null, 'Connection successful!');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Generate content using AI
     */
    public function generate(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'context' => 'nullable|string|max:5000',
            'provider' => 'nullable|string',
            'model' => 'nullable|string',
        ]);

        try {
            $providerNameRaw = $request->input('provider');
            $providerName = is_string($providerNameRaw) ? $providerNameRaw : null;
            $modelRaw = $request->input('model', '');
            $model = is_string($modelRaw) ? $modelRaw : '';
            $promptRaw = $request->input('prompt', '');
            $prompt = is_string($promptRaw) ? $promptRaw : '';
            $contextRaw = $request->input('context', '');
            $context = is_string($contextRaw) ? $contextRaw : '';

            // Use Factory to get the active service
            $service = \Modules\Core\Services\Ai\AiProviderFactory::make($providerName);

            $result = $service->generateText(
                $prompt,
                $context,
                $model
            );

            return $this->success([
                'content' => $result,
                'provider' => $service->getName(),
            ]);

        } catch (\Exception $e) {
            $message = $e->getMessage();
            $status = 500;

            if (str_contains(strtolower($message), 'quota') || str_contains(strtolower($message), 'insufficient balance')) {
                $status = 429;
                $message = 'AI Quota/Balance Exceeded. Please check your billing.';
            } elseif (str_contains(strtolower($message), 'not found') || str_contains(strtolower($message), 'supported')) {
                $status = 404;
            } elseif (str_contains(strtolower($message), 'api key') || str_contains(strtolower($message), 'unauthorized')) {
                $status = 401;
            }

            return $this->error($message, $status, [], 'AI_ERROR', ['original_error' => $e->getMessage()]);
        }
    }
}
