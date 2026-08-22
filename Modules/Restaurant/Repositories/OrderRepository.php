<?php

namespace Modules\Restaurant\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Restaurant\Models\Order;

class OrderRepository
{
    public function paginateForTenant(int $tenantId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return Order::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->with('table', 'user', 'items')
            ->when(
                isset($filters['status']),
                fn (Builder $query) => $query->where('status', $filters['status']),
            )
            ->when(
                isset($filters['table_id']),
                fn (Builder $query) => $query->where('table_id', $filters['table_id']),
            )
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function findWithItems(int $id, int $tenantId): ?Order
    {
        return Order::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->with('table', 'user', 'items')
            ->findOrFail($id);
    }

    public function getKitchenOrders(int $tenantId): Collection
    {
        return Order::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [2, 3])
            ->with('table', 'items')
            ->orderBy('created_at')
            ->get();
    }
}
