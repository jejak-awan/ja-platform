<?php

namespace Modules\School\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Context;

/**
 * @property string $school_id
 *
 * @mixin Model
 */
trait ScopedBySchool
{
    public static function bootScopedBySchool(): void
    {
        static::addGlobalScope('school', function (Builder $builder): void {
            $schoolId = Context::get('school_id');
            if ($schoolId) {
                // Determine the correct table prefix/column
                // Assumes 'school_id' exists on the model
                $builder->where($builder->getModel()->getTable().'.school_id', $schoolId);
            }
        });

        static::creating(function (Model $model): void {
            /** @var static $model */
            $schoolId = Context::get('school_id');
            if (is_string($schoolId) && (! property_exists($model, 'school_id') || $model->school_id === null)) {
                $model->school_id = $schoolId;
            }
        });
    }
}
