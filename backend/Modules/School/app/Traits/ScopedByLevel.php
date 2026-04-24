<?php

namespace Modules\School\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Context;

/**
 * @property int $school_level_id
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait ScopedByLevel
{
    public static function bootScopedByLevel(): void
    {
        static::addGlobalScope('school_level', function (Builder $builder) {
            $levelId = Context::get('school_level_id');
            if ($levelId) {
                $builder->where('school_level_id', $levelId);
            }
        });

        static::creating(function (Model $model) {
            /** @var static $model */
            $levelId = Context::get('school_level_id');
            if (is_numeric($levelId) && ! isset($model->school_level_id)) {
                $model->school_level_id = (int)$levelId;
            }
        });
    }
}
