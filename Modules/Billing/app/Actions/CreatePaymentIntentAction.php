<?php

namespace Modules\Billing\Actions;

use Modules\Billing\Models\Plan;

class CreatePaymentIntentAction
{
    public function execute(Plan $plan, int $tenantId): array
    {
        return [
            'client_secret' => 'pi_mock_'.$tenantId.'_'.now()->timestamp.'_secret_'.str()->random(16),
            'plan_id' => $plan->id,
            'amount' => $plan->price_monthly,
            'currency' => 'usd',
        ];
    }
}
