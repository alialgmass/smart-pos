<?php

namespace Modules\Inventory\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\PurchaseOrder;

class RecordPurchaseAction
{
    /**
     * @param  array{supplier_name: string, notes?: string|null, items: array<int, array{product_id: int, variant_id?: int|null, qty: float, unit_cost: float}>}  $data
     */
    public function execute(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data): PurchaseOrder {
            $tenantId = Auth::user()->tenant_id;

            $totalCost = 0;
            foreach ($data['items'] as $item) {
                $totalCost += $item['qty'] * $item['unit_cost'];
            }

            $purchaseOrder = PurchaseOrder::create([
                'tenant_id' => $tenantId,
                'supplier_name' => $data['supplier_name'],
                'total_cost' => $totalCost,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'qty' => $item['qty'],
                    'unit_cost' => $item['unit_cost'],
                ]);
            }

            return $purchaseOrder;
        });
    }
}
