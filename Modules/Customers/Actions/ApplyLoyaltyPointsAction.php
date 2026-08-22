<?php

namespace Modules\Customers\Actions;

use Modules\Customers\Models\Customer;
use Modules\Customers\Models\LoyaltyTransaction;

class ApplyLoyaltyPointsAction
{
    public function execute(int $customerId, int $points, string $type): void
    {
        $customer = Customer::query()
            ->withoutGlobalScope('tenant')
            ->findOrFail($customerId);

        $typeValue = $type === 'earn' ? 1 : 2;

        if ($type === 'earn') {
            $customer->increment('loyalty_points', $points);
        } else {
            $customer->decrement('loyalty_points', $points);
        }

        LoyaltyTransaction::create([
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customerId,
            'points' => $type === 'earn' ? $points : -$points,
            'type' => $typeValue,
        ]);
    }
}
