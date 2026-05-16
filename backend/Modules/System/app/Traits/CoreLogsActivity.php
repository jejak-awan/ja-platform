<?php

declare(strict_types=1);

namespace Modules\System\Traits;

use Modules\System\Models\ActivityLog;

/**
 * Trait to automatically log create, update, and delete events on Eloquent models.
 * Add this trait to any Core model that should be tracked in the activity journal.
 * Usage: `use \App\Traits\CoreLogsActivity;` in any Core Model class.
 */
trait CoreLogsActivity
{
    public static function bootCoreLogsActivity(): void
    {
        static::created(function (?\Illuminate\Database\Eloquent\Model $model): void {
            ActivityLog::log(
                'created',
                $model,
                ['attributes' => $model->getAttributes()],
            );
        });

        static::updated(function (?\Illuminate\Database\Eloquent\Model $model): void {
            $changes = $model->getChanges();
            unset($changes['updated_at'], $changes['remember_token']);

            if ($changes === []) {
                return;
            }

            $original = [];
            foreach (array_keys($changes) as $key) {
                $original[$key] = $model->getOriginal($key);
            }

            ActivityLog::log(
                'updated',
                $model,
                ['old' => $original, 'new' => $changes],
            );
        });

        static::deleted(function (?\Illuminate\Database\Eloquent\Model $model): void {
            ActivityLog::log(
                'deleted',
                $model,
                ['attributes' => $model->getAttributes()],
            );
        });
    }
}
