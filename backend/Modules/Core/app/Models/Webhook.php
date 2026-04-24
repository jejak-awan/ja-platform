<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Modules\Core\Traits\CoreLogsActivity;

/**
 * @property int $id
 * @property string $name
 * @property string $event
 * @property string $url
 * @property string $method
 * @property array<string, string>|null $headers
 * @property array<string, mixed>|null $payload_template
 * @property bool $is_active
 * @property string|null $secret
 * @property \Illuminate\Support\Carbon|null $last_triggered_at
 * @property int|null $last_response_status
 * @property int $failure_count
 * @property int $retry_count
 * @property int $max_retries
 */
class Webhook extends Model
{
    /** @use HasFactory<\Modules\Core\Database\Factories\WebhookFactory> */
    use CoreLogsActivity, HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\Core\Database\Factories\WebhookFactory
    {
        return \Modules\Core\Database\Factories\WebhookFactory::new();
    }

    protected $fillable = [
        'name',
        'url',
        'event',
        'method',
        'headers',
        'payload_template',
        'is_active',
        'timeout',
        'retry_count',
        'max_retries',
        'last_triggered_at',
        'success_count',
        'failure_count',
    ];

    protected $casts = [
        'headers' => 'array',
        'payload_template' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    /**
     * @param  array<string, mixed>  $data
     */
    public function trigger(array $data): bool
    {
        if (! $this->is_active) {
            return false;
        }

        try {
            $payload = $this->buildPayload($data);

            /** @var "get"|"post"|"put"|"patch"|"delete" $method */
            $method = strtolower((string) $this->method);

            $response = Http::timeout((int) ($this->timeout ?? 30))
                ->withHeaders($this->headers ?? [])
                ->{$method}((string) $this->url, $payload);

            if ($response->successful()) {
                $this->increment('success_count');
                $this->update(['last_triggered_at' => now()]);

                return true;
            } else {
                $this->increment('failure_count');

                // Retry if not exceeded max retries
                if ((int) $this->retry_count < (int) $this->max_retries) {
                    $this->increment('retry_count');
                }

                \Log::error('Webhook failed with status: '.$response->status(), [
                    'webhook_id' => $this->id,
                    'url' => $this->url,
                ]);

                return false;
            }
        } catch (\Exception $e) {
            $this->increment('failure_count');

            // Retry if not exceeded max retries
            if ((int) $this->retry_count < (int) $this->max_retries) {
                $this->increment('retry_count');
            }

            \Log::error('Webhook failed: '.$e->getMessage(), [
                'webhook_id' => $this->id,
                'url' => $this->url,
            ]);

            return false;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function buildPayload(array $data): array
    {
        if (is_array($this->payload_template) && ! empty($this->payload_template)) {
            // Use template to build payload
            $payload = [];
            foreach ($this->payload_template as $key => $template) {
                $payload[$key] = $this->resolveTemplate($template, $data);
            }

            return $payload;
        }

        // Default payload
        return [
            'event' => $this->event,
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveTemplate(mixed $template, array $data): mixed
    {
        // Simple template resolution
        // Could be enhanced with more complex logic
        if (is_string($template)) {
            $json = json_encode($data);

            return str_replace('{data}', is_string($json) ? $json : '{}', $template);
        }

        return $template;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function triggerForEvent(string $event, array $data): void
    {
        $webhooks = self::where('event', $event)
            ->where('is_active', true)
            ->get();

        foreach ($webhooks as $webhook) {
            $webhook->trigger($data);
        }
    }
}
