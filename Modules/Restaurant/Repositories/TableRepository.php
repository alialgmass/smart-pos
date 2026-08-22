<?php

namespace Modules\Restaurant\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Restaurant\Enums\TableStatus;
use Modules\Restaurant\Models\Table;

class TableRepository
{
    public function getForTenant(int $tenantId): Collection
    {
        return Table::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->with('orders')
            ->orderBy('name')
            ->get();
    }

    public function findForTenant(int $id, int $tenantId): ?Table
    {
        return Table::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->with('orders')
            ->findOrFail($id);
    }

    public function getAvailableForTenant(int $tenantId): Collection
    {
        return Table::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('status', TableStatus::Available)
            ->orderBy('name')
            ->get();
    }
}
