<?php

namespace Modules\Sales\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Sales\Models\Sale;

class SaleRepository
{
    public function paginateForTenant(int $tenantId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Sale::query()
            ->withoutGlobalScope('tenant')
            ->where('sales.tenant_id', $tenantId)
            ->with('items', 'user')
            ->orderByDesc('created_at');

        if (! empty($filters['status'])) {
            $query->where('sales.status', $filters['status']);
        }

        if (! empty($filters['payment_method'])) {
            $query->where('sales.payment_method', $filters['payment_method']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('sales.created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('sales.created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('sales.invoice_number', 'like', '%'.$filters['search'].'%')
                    ->orWhere('sales.id', $filters['search']);
            });
        }

        return $query->paginate($perPage);
    }

    public function findWithItems(int $id): ?Sale
    {
        return Sale::query()
            ->with('items')
            ->findOrFail($id);
    }
}
