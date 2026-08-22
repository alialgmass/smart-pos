<?php

namespace Modules\Sales\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Actions\UpdateStockAction;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Sales\Models\SaleReturn;
use Modules\Sales\Models\SaleReturnItem;

class ProcessReturnAction
{
    public function __construct(
        private readonly UpdateStockAction $updateStock,
    ) {}

    /**
     * @param  array<int, array{item_id: int, qty: float}>  $items
     */
    public function execute(Sale $sale, array $items, int $refundMethod = 1, ?string $reason = null): SaleReturn
    {
        return DB::transaction(function () use ($sale, $items, $refundMethod): SaleReturn {
            $totalRefund = 0;

            $return = SaleReturn::create([
                'tenant_id' => $sale->tenant_id,
                'sale_id' => $sale->id,
                'user_id' => auth()->id(),
                'refund_method' => $refundMethod,
                'total_refund' => 0,
            ]);

            foreach ($items as $itemData) {
                $saleItem = SaleItem::query()
                    ->withoutGlobalScope('tenant')
                    ->where('id', $itemData['item_id'])
                    ->where('sale_id', $sale->id)
                    ->firstOrFail();

                $returnQty = $itemData['qty'];

                SaleReturnItem::create([
                    'tenant_id' => $sale->tenant_id,
                    'sale_return_id' => $return->id,
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'variant_id' => $saleItem->variant_id,
                    'qty' => $returnQty,
                    'refund_amount' => $saleItem->price * $returnQty,
                ]);

                $totalRefund += $saleItem->price * $returnQty;

                if ($saleItem->product_id !== null) {
                    $this->updateStock->execute(
                        productId: $saleItem->product_id,
                        qtyChange: $returnQty,
                        referenceType: 'return',
                        referenceId: $return->id,
                        variantId: $saleItem->variant_id,
                    );
                }
            }

            $return->update(['total_refund' => $totalRefund]);

            return $return->load('items');
        });
    }
}
