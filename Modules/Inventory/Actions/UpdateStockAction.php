<?php

namespace Modules\Inventory\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\StockMovement;
use Modules\Shared\Enums\StockMovementType;

class UpdateStockAction
{
    public function execute(
        int $productId,
        float $qtyChange,
        string $referenceType,
        ?int $referenceId = null,
        ?int $variantId = null,
        ?int $userId = null,
    ): StockMovement {
        return DB::transaction(function () use ($productId, $qtyChange, $referenceType, $referenceId, $variantId, $userId): StockMovement {
            $product = Product::query()
                ->withoutGlobalScope('tenant')
                ->lockForUpdate()
                ->findOrFail($productId);

            $qtyBefore = $variantId !== null
                ? (float) $product->variants()->lockForUpdate()->findOrFail($variantId)->stock_qty
                : (float) $product->stock_qty;

            $qtyAfter = $qtyBefore + $qtyChange;

            if ($variantId !== null) {
                $product->variants()->where('id', $variantId)->update(['stock_qty' => $qtyAfter]);
            } else {
                $product->update(['stock_qty' => $qtyAfter]);
            }

            return StockMovement::create([
                'tenant_id' => $product->tenant_id,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'type' => $qtyChange >= 0 ? StockMovementType::Purchase : StockMovementType::Sale,
                'qty_change' => $qtyChange,
                'qty_before' => $qtyBefore,
                'qty_after' => $qtyAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'user_id' => $userId ?? Auth::id(),
            ]);
        });
    }
}
