<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Billing\Actions\CreatePaymentIntentAction;
use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Subscription;
use Modules\Shared\Enums\SubscriptionStatus;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly CreatePaymentIntentAction $createPaymentIntent,
    ) {}

    public function index(): Response
    {
        $tenantId = auth()->user()->tenant_id;
        $tenant = auth()->user()->tenant;

        $subscription = Subscription::query()
            ->where('tenant_id', $tenantId)
            ->with('plan')
            ->latest()
            ->first();

        return Inertia::render('billing::Billing', [
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'plan_id' => $subscription->plan_id,
                'plan_name' => $subscription->plan?->name,
                'status' => $subscription->status->value,
                'starts_at' => $subscription->starts_at,
                'ends_at' => $subscription->ends_at,
                'gateway' => $subscription->gateway?->value,
            ] : null,
            'trial_ends_at' => $tenant->trial_ends_at,
            'plans' => Plan::query()->get()->map(fn (Plan $plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'price_monthly' => (float) $plan->price_monthly,
                'max_users' => $plan->max_users,
                'max_products' => $plan->max_products,
                'features' => $plan->features,
            ]),
        ]);
    }

    public function checkout(Request $request, Plan $plan): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $intent = $this->createPaymentIntent->execute($plan, $tenantId);

        Subscription::create([
            'tenant_id' => $tenantId,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::PastDue,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'gateway' => null,
            'gateway_subscription_id' => $intent['client_secret'],
        ]);

        return redirect()->route('billing.index')->with('success', __('Subscription initiated.'));
    }
}
