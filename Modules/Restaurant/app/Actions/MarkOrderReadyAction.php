<?php

namespace Modules\Restaurant\Actions;

use Modules\Restaurant\Enums\OrderStatus;
use Modules\Restaurant\Models\Order;

class MarkOrderReadyAction
{
    public function execute(int $orderId, int $tenantId): Order
    {
        $order = Order::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->findOrFail($orderId);

        $order->update(['status' => OrderStatus::Ready]);

        return $order->fresh(['items', 'table']);
    }
}
