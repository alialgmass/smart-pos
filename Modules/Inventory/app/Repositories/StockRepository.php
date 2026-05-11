<?php

namespace Modules\Inventory\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\StockMovement;

class StockRepository
{
    public function getCurrentStock(int $productId, ?int $variantId = null): float
    {
        if ($variantId !== null) {
            $variant = Product::findOrFail($productId)->variants()->findOrFail($variantId);

            return (float) $variant->stock_qty;
        }

        return (float) Product::findOrFail($productId)->stock_qty;
    }

    /**
     * @return Collection<int, StockMovement>
     */
    public function getMovements(int $productId): Collection
    {
        return StockMovement::query()
            ->where('product_id', $productId)
            ->with('product:id,name')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordMovement(array $data): StockMovement
    {
        return StockMovement::create([
            'tenant_id' => $data['tenant_id'],
            'product_id' => $data['product_id'],
            'variant_id' => $data['variant_id'] ?? null,
            'type' => $data['type'],
            'qty_change' => $data['qty_change'],
            'qty_before' => $data['qty_before'],
            'qty_after' => $data['qty_after'],
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'user_id' => $data['user_id'] ?? Auth::id(),
        ]);
    }
}
