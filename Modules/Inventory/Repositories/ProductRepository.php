<?php

namespace Modules\Inventory\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Inventory\Models\Product;

class ProductRepository
{
    /**
     * @return Collection<int, Product>
     */
    public function searchByName(string $query, int $tenantId, int $limit = 10): Collection
    {
        return Product::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('name', 'like', "%{$query}%")
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function searchByBarcode(string $barcode, int $tenantId): ?Product
    {
        return Product::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('barcode', $barcode)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateForTenant(int $tenantId, array $filters = []): LengthAwarePaginator
    {
        return Product::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->when(
                isset($filters['search']),
                fn (Builder $query) => $query->where(function (Builder $q) use ($filters): void {
                    $q->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('barcode', 'like', "%{$filters['search']}%");
                }),
            )
            ->when(
                isset($filters['category_id']),
                fn (Builder $query) => $query->where('category_id', $filters['category_id']),
            )
            ->when(
                isset($filters['status']),
                fn (Builder $query) => $query->where('status', $filters['status']),
            )
            ->with('category:id,name')
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 20);
    }
}
