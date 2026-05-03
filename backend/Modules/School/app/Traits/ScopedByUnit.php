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
            // Bypass scoping for Super Admin or specific administrative tasks
            if (Context::get('bypass_unit_scope')) {
                return;
            }

            $levelId = Context::get('school_unit_id');
            $model = $builder->getModel();
            
            // Check if model has is_shared column
            $hasShared = \Schema::hasColumn($model->getTable(), 'is_shared');

            if ($levelId) {
                $builder->where(function($query) use ($levelId, $hasShared) {
                    $query->where('school_unit_id', $levelId)
                          ->orWhereNull('school_unit_id'); // Global records are always visible
                    
                    if ($hasShared) {
                        $query->orWhere('is_shared', true);
                    }
                });
            } else {
                // If no unit context, only show global content
                $builder->whereNull('school_unit_id');
            }
        });

        static::creating(function (Model $model) {
            /** @var static $model */
            $levelId = Context::get('school_unit_id');
            // Only auto-assign for real unit IDs (> 0). ID 0 = Global context → keep null.
            if (is_numeric($levelId) && (int)$levelId > 0 && ! isset($model->school_unit_id)) {
                $model->school_unit_id = (int)$levelId;
            }
        });
    }
}
