<?php

declare(strict_types=1);

namespace Modules\Core\Support;

use Illuminate\Support\Facades\DB;

/**
 * Portable SQL fragments for date grouping (MySQL / PostgreSQL / SQLite).
 */
final class SqlDateExpression
{
    private static function driver(): string
    {
        return DB::connection()->getDriverName();
    }

    /**
     * Calendar date from a timestamp column (SELECT / GROUP BY).
     */
    public static function calendarDate(string $column): string
    {
        return match (self::driver()) {
            'sqlite' => "date({$column})",
            default => "CAST({$column} AS DATE)",
        };
    }

    /**
     * Truncate timestamp to minute precision (YYYY-MM-DD HH:MM) for grouping / equality.
     */
    public static function minuteBucket(string $column): string
    {
        return match (self::driver()) {
            'pgsql' => "to_char({$column}, 'YYYY-MM-DD HH24:MI')",
            'sqlite' => "strftime('%Y-%m-%d %H:%M', {$column})",
            default => "DATE_FORMAT({$column}, '%Y-%m-%d %H:%i')",
        };
    }
}
