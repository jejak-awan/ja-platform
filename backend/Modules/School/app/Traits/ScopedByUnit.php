<?php

namespace Modules\School\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Context;

/**
 * @property int $school_unit_id
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait ScopedByUnit
{
    public static function bootScopedByUnit(): void
    {
        static::addGlobalScope('school_unit', function (Builder $builder) {
            $levelId = Context::get('school_unit_id');
            if ($levelId) {
                $builder->where('school_unit_id', $levelId);
            }
        });

        static::creating(function (Model $model) {
            /** @var static $model */
            $levelId = Context::get('school_unit_id');
            if (is_numeric($levelId) && ! isset($model->school_unit_id)) {
                $model->school_unit_id = (int)$levelId;
            }
        });
    }
}
