<?php

namespace Modules\Customers\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Customers\Models\Customer;

class CustomerRepository
{
    /**
     * @return Collection<int, Customer>
     */
    public function search(int $tenantId, string $query, int $limit = 10): Collection
    {
        return Customer::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where(function (Builder $q) use ($query): void {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateForTenant(int $tenantId, array $filters = []): LengthAwarePaginator
    {
        return Customer::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->when(
                isset($filters['search']),
                fn (Builder $query) => $query->where(function (Builder $q) use ($filters): void {
                    $q->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('phone', 'like', "%{$filters['search']}%");
                }),
            )
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function findWithDebts(int $id): ?Customer
    {
        return Customer::query()
            ->with(['debts.payments', 'loyaltyTransactions'])
            ->find($id);
    }
}
