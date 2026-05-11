<?php

namespace Modules\Shared\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Support\CurrentTenant;

abstract class BaseTenantRepository
{
    public function __construct(
        protected readonly CurrentTenant $currentTenant,
    ) {}

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    protected function applyTenantScope(Builder $query): Builder
    {
        if ($this->currentTenant->isResolved()) {
            $query->where($query->getModel()->getTable().'.tenant_id', $this->currentTenant->id);
        }

        return $query;
    }
}
