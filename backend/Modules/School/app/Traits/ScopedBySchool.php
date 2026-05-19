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
        static::addGlobalScope(new \Modules\System\Scopes\PolymorphicTenantScope());

        static::creating(function (Model $model): void {
            /** @var static $model */
            $schoolId = Context::get('school_id');
            if (is_string($schoolId) && (! property_exists($model, 'school_id') || $model->school_id === null)) {
                if (\Illuminate\Support\Facades\Schema::hasColumn($model->getTable(), 'school_id')) {
                    $model->setAttribute('school_id', $schoolId);
                }
            }
        });
    }
}
