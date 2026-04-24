<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property string $group
 * @property string|null $description
 * @property bool $is_public
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Setting extends Model
{
    /** @use HasFactory<\Modules\Core\Database\Factories\SettingFactory> */
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\Core\Database\Factories\SettingFactory
    {
        return \Modules\Core\Database\Factories\SettingFactory::new();
    }

    protected $fillable = [
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

    // For SQLite compatibility with 'key' as reserved keyword
    protected $table = 'settings';

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return static::castValue($setting->value, (string) $setting->type);
    }

    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): self
    {
        /** @var self $setting */
        $setting = static::updateOrCreate(
            ['key' => $key],
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
        /** @var \Illuminate\Database\Eloquent\Collection<int, self> $settings */
        $settings = static::where('group', $group)->get();

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
        }
    }
}
