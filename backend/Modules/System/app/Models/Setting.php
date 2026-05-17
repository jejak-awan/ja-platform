<?php

namespace Modules\System\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Context;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property string $group
 * @property string|null $description
 * @property bool $is_public
 * @property int|null $workspace_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Setting extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'sys_settings';


    /** @use HasFactory<\Modules\System\Database\Factories\SettingFactory> */
    use HasFactory, ScopedByWorkspace;


    protected $fillable = [
        'workspace_id',
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\System\Database\Factories\SettingFactory
    {
        return \Modules\System\Database\Factories\SettingFactory::new();
    }

    public static function get(string $key, mixed $default = null, ?int $workspaceId = null): mixed
    {
        $id = $workspaceId ?? Context::get('workspace_id');

        try {
            $query = static::where('key', $key);

            if ($id) {
                $query->where(function($q) use ($id): void {
                    $q->where('workspace_id', $id)
                      ->orWhereNull('workspace_id');
                })->orderByRaw('workspace_id IS NULL ASC');
            } else {
                $query->whereNull('workspace_id');
            }

            $setting = $query->first();
        } catch (\Illuminate\Database\QueryException) {
            // Table might not exist yet (e.g. during route registration in tests)
            return $default;
        }

        if (! $setting) {
            return $default;
        }

        return static::castValue($setting->value, (string) $setting->type);
    }

    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general', ?int $workspaceId = null): self
    {
        return static::updateOrCreate(
            ['key' => $key, 'workspace_id' => $workspaceId],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => $type,
                'group' => $group,
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function getGroup(string $group): array
    {
        $settings = static::where('group', $group)
            ->orderByRaw('workspace_id IS NULL DESC')
            ->get();

        return $settings->mapWithKeys(fn($setting) => [(string) $setting->key => static::castValue($setting->value, (string) $setting->type)])->toArray();
    }

    protected static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'integer' => is_numeric($value) ? (int) $value : 0,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => is_string($value) ? json_decode($value, true) : $value,
            'array' => is_string($value) ? json_decode($value, true) : (array) $value,
            default => $value,
        };
    }
}
