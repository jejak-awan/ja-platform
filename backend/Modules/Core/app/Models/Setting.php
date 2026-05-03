<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\School\Traits\ScopedByUnit;

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
    use HasFactory, ScopedByUnit;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\Core\Database\Factories\SettingFactory
    {
        return \Modules\Core\Database\Factories\SettingFactory::new();
    }

    protected $fillable = [
        'school_unit_id',
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
        // Prioritize unit-specific records (non-null school_unit_id) over global ones
        $setting = static::where('key', $key)
            ->orderByRaw('school_unit_id IS NULL ASC')
            ->first();

        if (! $setting) {
            return $default;
        }

        return static::castValue($setting->value, (string) $setting->type);
    }

    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): self
    {
        /** @var self $setting */
        $setting = static::updateOrCreate(
            ['key' => $key, 'school_unit_id' => \Illuminate\Support\Facades\Context::get('school_unit_id')],
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
        $settings = static::where('group', $group)
            ->orderByRaw('school_unit_id IS NULL DESC')
            ->get();

        // Use mapWithKeys but because of the ordering, 
        // the global record (processed first or last?) will be overwritten.
        // If we want unit to win, unit should be processed LAST in mapWithKeys.
        // So we should order Global first, then Unit.
        
        // Wait! IS NULL ASC puts NULL (1) last in MySQL?
        // No, in MySQL: NULL IS NULL is 1. 1 IS NULL is 0.
        // So NULL IS NULL ASC -> 0 (non-null), then 1 (null). Correct.
        // mapWithKeys: if same key, later one wins.
        // So if we have Global (last) it will overwrite Unit? NO!
        // We want Unit (0) first, then Global (1) last.
        // Then mapWithKeys will have Global overwrite Unit. BAD.
        
        // REVERSE: ORDER BY school_unit_id IS NULL DESC
        // This puts NULL (1) first, then Unit (0) last.
        // mapWithKeys: Unit (last) will overwrite Global. GOOD.

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
