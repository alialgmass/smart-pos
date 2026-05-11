<?php

namespace Modules\Sales\Repositories;

use Illuminate\Support\Collection;
use Modules\Inventory\Models\Product;

class PosProductSearchRepository
{
    /**
     * Search products by barcode or name for POS.
     *
     * @return Collection<int, array{id: int, name: string, barcode: string|null, price: float, cost: float, stock_qty: float, has_variants: bool}>
     */
    public function search(int $tenantId, string $query, int $limit = 10): Collection
    {
        $productClass = Product::class;

        $products = $productClass::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($query): void {
                $q->where('barcode', $query)
                    ->orWhere('name', 'like', '%'.$query.'%');
            })
            ->orderByRaw('CASE WHEN barcode = ? THEN 0 ELSE 1 END', [$query])
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'barcode', 'price', 'cost', 'stock_qty', 'has_variants']);

        return $products;
    }
}
