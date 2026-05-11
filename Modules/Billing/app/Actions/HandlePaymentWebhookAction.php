<?php

namespace Modules\Billing\Actions;

use Modules\Billing\Models\Subscription;
use Modules\Shared\Enums\GatewayPaymentStatus;
use Modules\Shared\Enums\SubscriptionStatus;

class HandlePaymentWebhookAction
{
    public function execute(array $payload, Subscription $subscription): Subscription
    {
        $status = $this->resolveStatus($payload);

        if ($status === GatewayPaymentStatus::Succeeded) {
            $subscription->update([
                'status' => SubscriptionStatus::Active,
                'ends_at' => now()->addMonth(),
            ]);
        } elseif ($status === GatewayPaymentStatus::Failed) {
            $subscription->update([
                'status' => SubscriptionStatus::PastDue,
            ]);
        } elseif ($status === GatewayPaymentStatus::Cancelled) {
            $subscription->update([
                'status' => SubscriptionStatus::Cancelled,
            ]);
        }

        return $subscription->fresh();
    }

    private function resolveStatus(array $payload): GatewayPaymentStatus
    {
        $status = $payload['status'] ?? 'pending';

        return match ($status) {
            'succeeded', 'completed' => GatewayPaymentStatus::Succeeded,
            'failed' => GatewayPaymentStatus::Failed,
            'cancelled' => GatewayPaymentStatus::Cancelled,
            default => GatewayPaymentStatus::Pending,
        };
    }
}
