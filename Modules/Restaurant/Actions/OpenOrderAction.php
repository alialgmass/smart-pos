<?php

namespace Modules\Restaurant\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Restaurant\Enums\OrderStatus;
use Modules\Restaurant\Enums\TableStatus;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\Table;

class OpenOrderAction
{
    /**
     * @param  array<int, array{product_id?: int, variant_id?: int|null, name: string, price: float, qty: float, notes?: string|null}>  $items
     */
    public function execute(int $tableId, User $user, array $items = [], ?string $notes = null): Order
    {
        return DB::transaction(function () use ($tableId, $user, $items, $notes): Order {
            $tenantId = $user->tenant_id;

            $table = Table::query()
                ->withoutGlobalScope('tenant')
                ->where('tenant_id', $tenantId)
                ->findOrFail($tableId);

            $table->update(['status' => TableStatus::Occupied]);

            $orderNumber = $this->generateOrderNumber($tenantId);

            $order = Order::create([
                'tenant_id' => $tenantId,
                'table_id' => $tableId,
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'status' => OrderStatus::Open,
                'notes' => $notes,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'tenant_id' => $tenantId,
                    'product_id' => $item['product_id'] ?? null,
                    'variant_id' => $item['variant_id'] ?? null,
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $order->load('items', 'table');
        });
    }

    private function generateOrderNumber(int $tenantId): string
    {
        return 'ORD-'.$tenantId.'-'.now()->format('YmdHis').str_pad((string) random_int(0, 99), 2, '0', STR_PAD_LEFT);
    }
}
