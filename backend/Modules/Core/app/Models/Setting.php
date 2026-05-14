<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Context;
use Modules\Core\Traits\ScopedByWorkspace;

/**
 * @property int $id
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
    protected $table = 'core_settings';


    /** @use HasFactory<\Modules\Core\Database\Factories\SettingFactory> */
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
    protected static function newFactory(): \Modules\Core\Database\Factories\SettingFactory
    {
        return \Modules\Core\Database\Factories\SettingFactory::new();
    }

    public static function get(string $key, mixed $default = null, ?int $workspaceId = null): mixed
    {
        $id = $workspaceId ?? Context::get('workspace_id');

        try {
            $query = static::where('key', $key);

            if ($id) {
                $query->where(function($q) use ($id) {
                    $q->where('workspace_id', $id)
                      ->orWhereNull('workspace_id');
                })->orderByRaw('workspace_id IS NULL ASC');
            } else {
                $query->whereNull('workspace_id');
            }

            $setting = $query->first();
        } catch (\Illuminate\Database\QueryException $e) {
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
        $setting = static::updateOrCreate(
            ['key' => $key, 'workspace_id' => $workspaceId],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => $type,
                'group' => $group,
            ]
        );

        return $setting;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getGroup(string $group): array
    {
        $settings = static::where('group', $group)
            ->orderByRaw('workspace_id IS NULL DESC')
            ->get();

        return $settings->mapWithKeys(function ($setting) {
            return [(string) $setting->key => static::castValue($setting->value, (string) $setting->type)];
        })->toArray();
    }

    protected static function castValue(mixed $value, string $type): mixed
    {
        switch ($type) {
            case 'integer':
                return is_numeric($value) ? (int) $value : 0;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'text':
            case 'string':
            default:
                return $value;
            case 'array':
                return is_string($value) ? json_decode($value, true) : (array) $value;
        }
    }
}
