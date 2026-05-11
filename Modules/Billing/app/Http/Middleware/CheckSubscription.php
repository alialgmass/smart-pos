<?php

namespace Modules\Billing\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Billing\Models\Subscription;
use Modules\Shared\Enums\SubscriptionStatus;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $tenant = $user->tenant;

        if ($tenant->trial_ends_at !== null && now()->lessThan($tenant->trial_ends_at)) {
            return $next($request);
        }

        $subscription = Subscription::query()
            ->where('tenant_id', $user->tenant_id)
            ->whereIn('status', [
                SubscriptionStatus::Active,
                SubscriptionStatus::Trialing,
                SubscriptionStatus::Grace,
            ])
            ->where(function ($query): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->first();

        if ($subscription !== null) {
            return $next($request);
        }

        return redirect()->route('billing.pricing');
    }
}
