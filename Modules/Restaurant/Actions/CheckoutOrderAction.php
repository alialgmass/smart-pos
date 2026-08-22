<?php

namespace Modules\Restaurant\Actions;

use Modules\Restaurant\Enums\OrderStatus;
use Modules\Restaurant\Enums\TableStatus;
use Modules\Restaurant\Models\Order;

class CheckoutOrderAction
{
    public function execute(int $orderId, int $tenantId): Order
    {
        $order = Order::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->with('table')
            ->findOrFail($orderId);

        $order->update(['status' => OrderStatus::Paid]);

        $order->table->update(['status' => TableStatus::Available]);

        return $order->fresh(['items', 'table']);
    }
}
