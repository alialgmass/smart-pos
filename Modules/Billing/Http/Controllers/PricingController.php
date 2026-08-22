<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Billing\Models\Plan;

class PricingController extends Controller
{
    public function index(): Response
    {
        $plans = Plan::query()->get()->map(fn (Plan $plan) => [
            'id' => $plan->id,
            'name' => $plan->name,
            'price_monthly' => (float) $plan->price_monthly,
            'max_users' => $plan->max_users,
            'max_products' => $plan->max_products,
            'features' => $plan->features,
        ]);

        return Inertia::render('billing::Pricing', [
            'plans' => $plans,
        ]);
    }
}
