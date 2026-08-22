<?php

namespace Modules\Sales\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\StockMovement;
use Modules\Sales\Enums\SaleStatus;
use Modules\Sales\Events\SaleCompleted;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Shared\Enums\StockMovementType;

class ProcessSaleAction
{
    /**
     * @param  array{items: array<int, array{product_id?: int, variant_id?: int|null, name: string, price: float, cost: float, qty: float, discount?: float, tax_amount?: float}>, payment_method: int, subtotal: float, discount_amount?: float, tax_amount?: float, total: float, paid_amount: float, change_amount?: float, customer_id?: int|null, order_id?: int|null}  $data
     */
    public function execute(array $data, User $user): Sale
    {
        return DB::transaction(function () use ($data, $user): Sale {
            $tenantId = $user->tenant_id;

            $invoiceNumber = $this->generateInvoiceNumber($tenantId);

            $sale = Sale::create([
                'tenant_id' => $tenantId,
                'user_id' => $user->id,
                'customer_id' => $data['customer_id'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'invoice_number' => $invoiceNumber,
                'payment_method' => $data['payment_method'],
                'subtotal' => $data['subtotal'],
                'discount_amount' => $data['discount_amount'] ?? 0,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'total' => $data['total'],
                'paid_amount' => $data['paid_amount'],
                'change_amount' => $data['change_amount'] ?? 0,
                'status' => SaleStatus::Completed,
            ]);

            $productClass = Product::class;

            foreach ($data['items'] as $item) {
                $product = null;
                if (isset($item['product_id'])) {
                    $product = $productClass::query()
                        ->withoutGlobalScope('tenant')
                        ->where('tenant_id', $tenantId)
                        ->findOrFail($item['product_id']);

                    if ($product->stock_qty < $item['qty']) {
                        throw new \RuntimeException(__('Insufficient stock for :name', ['name' => $product->name]));
                    }
                }

                SaleItem::create([
                    'tenant_id' => $tenantId,
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'] ?? null,
                    'variant_id' => $item['variant_id'] ?? null,
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'cost' => $item['cost'],
                    'qty' => $item['qty'],
                    'discount' => $item['discount'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'total' => $item['price'] * $item['qty'] - ($item['discount'] ?? 0) + ($item['tax_amount'] ?? 0),
                ]);

                if ($product !== null) {
                    $product->decrement('stock_qty', $item['qty']);

                    $movementClass = StockMovement::class;
                    $movementClass::create([
                        'tenant_id' => $tenantId,
                        'product_id' => $item['product_id'],
                        'variant_id' => $item['variant_id'] ?? null,
                        'type' => StockMovementType::Sale,
                        'qty_change' => -$item['qty'],
                        'qty_before' => $product->stock_qty + $item['qty'],
                        'qty_after' => $product->stock_qty,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'user_id' => $user->id,
                    ]);
                }
            }

            SaleCompleted::dispatch($sale);

            return $sale->load('items');
        });
    }

    private function generateInvoiceNumber(int $tenantId): string
    {
        return 'INV-'.$tenantId.'-'.now()->format('YmdHis').str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
    }
}
