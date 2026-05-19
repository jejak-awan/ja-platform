<?php

namespace Modules\System\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Schema;

trait ScopedByWorkspace
{
    public static function bootScopedByWorkspace(): void
    {
        static::addGlobalScope(new \Modules\System\Scopes\PolymorphicTenantScope());

        static::creating(function (Model $model): void {
            $workspaceId = Context::get('workspace_id');
            if ($workspaceId && $workspaceId !== '0' && $workspaceId !== 0 && ! $model->getAttribute('workspace_id')) {
                if (Schema::hasColumn($model->getTable(), 'workspace_id')) {
                    $model->setAttribute('workspace_id', (string) $workspaceId);
                }
            }
        });
    }
}
