<?php

namespace Modules\Ai\Services;

use Modules\Ai\Contracts\AiProviderInterface;
use Modules\Ai\Services\Providers\OpenAiService;
use Modules\Ai\Services\Providers\DeepSeekService;
use Modules\Ai\Services\Providers\GeminiService;

class AiProviderFactory
{
    public static function make(?string $provider = null): AiProviderInterface
    {
        // If no provider specified, get default from settings
        if (! $provider) {
            /** @var mixed $defaultProvider */
            $defaultProvider = \Modules\System\Models\Setting::get('ai_default_provider', 'gemini');
            $provider = is_string($defaultProvider) ? $defaultProvider : 'gemini';
        }

        return match ($provider) {
            'openai' => new OpenAiService,
            'deepseek' => new DeepSeekService,
            'gemini' => new GeminiService,
            default => new GeminiService,
        };
    }

    /**
     * Get list of all registered providers
     *
     * @return array<int, array{id: string, name: string, logo: string}>
     */
    public static function getProviders(): array
    {
        return [
            [
                'id' => 'gemini',
                'name' => 'Google Gemini',
                'logo' => 'https://www.gstatic.com/lamda/images/gemini_sparkle_v002_d4735304ff6292a690345.svg',
            ],
            [
                'id' => 'openai',
                'name' => 'OpenAI',
                'logo' => 'https://openai.com/favicon.ico',
            ],
            [
                'id' => 'deepseek',
                'name' => 'DeepSeek',
                'logo' => '',
            ],
        ];
    }
}
