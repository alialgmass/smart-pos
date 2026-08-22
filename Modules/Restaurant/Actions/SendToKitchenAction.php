<?php

namespace Modules\Restaurant\Actions;

use Modules\Restaurant\Enums\OrderStatus;
use Modules\Restaurant\Models\Order;

class SendToKitchenAction
{
    public function execute(int $orderId, int $tenantId): Order
    {
        $order = Order::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->with('items')
            ->findOrFail($orderId);

        $order->update(['status' => OrderStatus::Sent]);

        $order->items()->whereNull('sent_to_kitchen_at')->update([
            'sent_to_kitchen_at' => now(),
        ]);

        return $order->fresh(['items', 'table']);
    }
}
