<?php

namespace Modules\Shared\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Modules\Shared\Support\CurrentTenant;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $tenant = app(CurrentTenant::class);

            if ($tenant->isResolved()) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', $tenant->id);
            }
        });

        static::creating(function ($model): void {
            if (empty($model->tenant_id)) {
                $tenant = app(CurrentTenant::class);

                if ($tenant->isResolved()) {
                    $model->tenant_id = $tenant->id;
                }
            }
        });
    }
}
