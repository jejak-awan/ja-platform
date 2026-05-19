<?php

namespace Modules\System\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Schema;

/**
 * @implements Scope<Model>
 */
class PolymorphicTenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Bypass scoping for Super Admin or specific administrative tasks
        if (Context::get('bypass_unit_scope') || Context::get('bypass_tenant_scope')) {
            return;
        }

        $table = $model->getTable();

        // Resolve columns to scope by.
        // First check if the model explicitly declares a $tenantColumns property.
        // Otherwise, fallback to checking standard columns dynamically.
        /** @var array<string> $tenantColumns */
        $tenantColumns = property_exists($model, 'tenantColumns') && is_array($model->tenantColumns)
            ? $model->tenantColumns
            : ['workspace_id', 'school_id'];

        // Check if model explicitly enables shared scoping
        $hasShared = property_exists($model, 'isSharedScoped') && (bool) $model->isSharedScoped;

        foreach ($tenantColumns as $column) {
            // Get context value for this column
            $contextValue = Context::get($column);

            if ($contextValue !== null && $contextValue !== 0 && $contextValue !== '0' && $contextValue !== '') {
                if (Schema::hasColumn($table, $column)) {
                    $builder->where(function ($query) use ($table, $column, $contextValue, $hasShared): void {
                        $query->where("{$table}.{$column}", $contextValue)
                            ->orWhereNull("{$table}.{$column}");

                        if ($hasShared) {
                            $query->orWhere("{$table}.is_shared", true);
                        }
                    });
                }
            } elseif ($contextValue === null) {
                if (Schema::hasColumn($table, $column)) {
                    // Only show global records if no context set
                    $builder->whereNull("{$table}.{$column}");
                }
            }
        }
    }
}
