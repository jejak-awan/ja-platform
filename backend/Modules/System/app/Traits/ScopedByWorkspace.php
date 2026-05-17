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
        static::addGlobalScope('workspace', function (Builder $builder): void {
            // Bypass scoping for Super Admin or specific administrative tasks
            if (Context::get('bypass_unit_scope')) {
                return;
            }
            
            $workspaceId = Context::get('workspace_id');
            $model = $builder->getModel();
            
            // Check if model explicitly enables shared scoping via property
            $hasShared = property_exists($model, 'isSharedScoped') && $model->isSharedScoped;

            if ($workspaceId !== null && $workspaceId !== 0 && $workspaceId !== '0' && $workspaceId !== '') {
                $table = $model->getTable();
                if (Schema::hasColumn($table, 'workspace_id')) {
                    $builder->where(function ($query) use ($table, $workspaceId, $hasShared): void {
                        $query->where("{$table}.workspace_id", $workspaceId)
                              ->orWhereNull("{$table}.workspace_id"); // Global records are always visible
                        
                        if ($hasShared) {
                            $query->orWhere("{$table}.is_shared", true);
                        }
                    });
                }
            } elseif ($workspaceId === null) {
                $table = $model->getTable();
                if (Schema::hasColumn($table, 'workspace_id')) {
                    // If no workspace context is set (and not bypassed), only show global records
                    $builder->whereNull($table . '.workspace_id');
                }
            }
        });

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
