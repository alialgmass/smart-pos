<?php

namespace Modules\Billing\Actions;

use Modules\Billing\Models\Subscription;
use Modules\Shared\Enums\SubscriptionStatus;

class RenewSubscriptionAction
{
    public function execute(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status' => SubscriptionStatus::Active,
            'starts_at' => $subscription->ends_at ?? now(),
            'ends_at' => now()->addMonth(),
        ]);

        return $subscription->fresh();
    }
}
